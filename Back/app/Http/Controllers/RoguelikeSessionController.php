<?php

namespace App\Http\Controllers;

use App\Models\Mejoras;
use App\Models\RunsRoguelike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RoguelikeSessionController extends Controller
{
    /** Tiempo por nivel en segundos (5 minutos). */
    private const TIME_PER_LEVEL = 300;

    /** Vidas iniciales. */
    private const INITIAL_LIVES = 3;

    /** TTL del cache en segundos (2 horas). */
    private const CACHE_TTL = 7200;

    /**
     * Inicia una nueva sesión/run de roguelike.
     * Almacena el estado en Cache vinculado al usuario.
     */
    public function startSession(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = $this->getCacheKey($userId);

        $session = [
            'user_id'          => $userId,
            'lives'            => self::INITIAL_LIVES,
            'levels_completed' => 0,
            'coins_earned'     => 0,
            'xp_earned'        => 0,
            'level_started_at' => null,
            'time_remaining'   => self::TIME_PER_LEVEL,
            'started_at'       => now()->toISOString(),
            'mejoras_activas'  => [],
            'coin_multiplier'  => 1,
        ];

        Cache::put($cacheKey, $session, self::CACHE_TTL);

        Log::info('Roguelike session started', ['user_id' => $userId]);

        return response()->json([
            'message'          => 'Sesión iniciada',
            'lives'            => $session['lives'],
            'time_remaining'   => $session['time_remaining'],
            'coins_earned'     => $session['coins_earned'],
            'mejoras_activas'  => $session['mejoras_activas'],
        ], 200);
    }

    /**
     * Inicia el temporizador para un nuevo nivel.
     * Registra el timestamp exacto del inicio.
     */
    public function startLevel(Request $request)
    {
        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session) {
            return $this->noSessionResponse();
        }

        if ($session['lives'] <= 0) {
            return response()->json([
                'message'   => 'Game Over. No te quedan vidas.',
                'game_over' => true,
                'stats'     => $this->getSessionStats($session),
            ], 200);
        }

        // Reiniciar temporizador para el nuevo nivel
        $session['level_started_at'] = now()->toISOString();
        $session['time_remaining'] = self::TIME_PER_LEVEL;

        $this->saveSession($userId, $session);

        return response()->json([
            'message'          => 'Nivel iniciado',
            'lives'            => $session['lives'],
            'time_remaining'   => $session['time_remaining'],
            'levels_completed' => $session['levels_completed'],
        ], 200);
    }

    /**
     * Valida el tiempo restante del nivel actual.
     * Calcula en base al timestamp real del servidor (anti-tampering).
     */
    public function checkTime(Request $request)
    {
        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session || !$session['level_started_at']) {
            return response()->json(['message' => 'No hay nivel activo'], 404);
        }

        $elapsed = now()->diffInSeconds($session['level_started_at']);
        $allocatedTime = $session['time_remaining'] ?? self::TIME_PER_LEVEL;
        $timeRemaining = max(0, $allocatedTime - $elapsed);

        if ($timeRemaining <= 0) {
            // Evitar doble resta: solo restar si aún tiene tiempo en cache
            if ($session['time_remaining'] > 0) {
                $session['lives'] = max(0, $session['lives'] - 1);

                // Reiniciar temporizador a 90 segundos (1.5 min)
                $session['time_remaining'] = 90;
                $session['level_started_at'] = now()->toISOString();
                $this->saveSession($userId, $session);
            }

            $gameOver = $session['lives'] <= 0;

            if ($gameOver) {
                $session['time_remaining'] = 0;
                $session['level_started_at'] = null;
                $this->saveSession($userId, $session);
                $this->saveRun($session);
            }

            return response()->json([
                'time_expired'   => true,
                'lives'          => $session['lives'],
                'time_remaining' => $session['time_remaining'],
                'game_over'      => $gameOver,
                'stats'          => $gameOver ? $this->getSessionStats($session) : null,
                'message'        => $gameOver
                    ? '¡Game Over! Se acabaron tus vidas.'
                    : '¡Se acabó el tiempo! Pierdes una vida. Tienes 1:30 extra.',
            ], 200);
        }

        return response()->json([
            'time_expired'   => false,
            'time_remaining' => $timeRemaining,
            'lives'          => $session['lives'],
        ], 200);
    }

    /**
     * Registra un fallo (código no pasa tests).
     * Resta una vida y verifica game over.
     */
    public function registerFailure(Request $request)
    {
        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session) {
            return $this->noSessionResponse();
        }

        // Evitar abusos: si ya no tiene vidas, no resta más
        if ($session['lives'] <= 0) {
            return response()->json([
                'lives'     => 0,
                'game_over' => true,
                'stats'     => $this->getSessionStats($session),
                'message'   => '¡Game Over!',
            ], 200);
        }

        $session['lives'] = max(0, $session['lives'] - 1);
        $this->saveSession($userId, $session);

        $gameOver = $session['lives'] <= 0;

        if ($gameOver) {
            $this->saveRun($session);
        }

        Log::info('Roguelike failure registered', [
            'user_id'   => $userId,
            'lives'     => $session['lives'],
            'game_over' => $gameOver,
        ]);

        return response()->json([
            'lives'     => $session['lives'],
            'game_over' => $gameOver,
            'stats'     => $gameOver ? $this->getSessionStats($session) : null,
            'message'   => $gameOver
                ? '¡Game Over!'
                : '¡Código incorrecto! Pierdes una vida.',
        ], 200);
    }

    /**
     * Registra un éxito (nivel completado).
     */
    public function registerSuccess(Request $request)
    {
        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session) {
            return $this->noSessionResponse();
        }

        $multiplier = $session['coin_multiplier'] ?? 1;
        $coinsGained = 10 * $multiplier;

        $session['levels_completed'] += 1;
        $session['coins_earned'] += $coinsGained;
        $session['xp_earned'] += 25;

        $this->saveSession($userId, $session);

        return response()->json([
            'lives'            => $session['lives'],
            'levels_completed' => $session['levels_completed'],
            'coins_earned'     => $session['coins_earned'],
            'xp_earned'        => $session['xp_earned'],
        ], 200);
    }

    /**
     * Compra una mejora con monedas de la sesión.
     * Carga 3 mejoras aleatorias, el usuario elige una por ID.
     */
    public function buyMejora(Request $request)
    {
        $request->validate([
            'mejora_id' => 'required|integer|exists:mejoras,id',
        ]);

        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session) {
            return $this->noSessionResponse();
        }

        $mejora = Mejoras::find($request->mejora_id);

        if (!$mejora) {
            return response()->json(['message' => 'Mejora no encontrada.'], 404);
        }

        // Coste fijo de la caja: 100 monedas de sesión
        $costeCaja = 100;

        if ($session['coins_earned'] < $costeCaja) {
            return response()->json([
                'message'      => 'No tienes suficientes monedas.',
                'coins_earned' => $session['coins_earned'],
                'coste'        => $costeCaja,
            ], 400);
        }

        // Deducir coste
        $session['coins_earned'] -= $costeCaja;

        // Aplicar efecto según tipo
        $efectoAplicado = '';

        switch ($mejora->tipo) {
            case 'vidas_extra':
                $session['lives'] += 1;
                $efectoAplicado = '+1 vida. Ahora tienes ' . $session['lives'] . ' vidas.';
                break;

            case 'tiempo_extra':
                $session['time_remaining'] = ($session['time_remaining'] ?? self::TIME_PER_LEVEL) + 60;
                $efectoAplicado = '+60 segundos de tiempo extra.';
                break;

            case 'multiplicador':
                $session['coin_multiplier'] = ($session['coin_multiplier'] ?? 1) * 2;
                $efectoAplicado = 'x2 multiplicador de monedas activo.';
                break;

            case 'pista':
                // Se envía al frontend para revelar info extra
                $efectoAplicado = 'Pista desbloqueada para el nivel actual.';
                break;
        }

        // Registrar mejora activa
        $session['mejoras_activas'] = $session['mejoras_activas'] ?? [];
        $session['mejoras_activas'][] = [
            'id'     => $mejora->id,
            'nombre' => $mejora->nombre,
            'tipo'   => $mejora->tipo,
            'icon'   => $this->getMejoraIcon($mejora->tipo),
        ];

        $this->saveSession($userId, $session);

        Log::info('Mejora comprada', [
            'user_id'  => $userId,
            'mejora'   => $mejora->nombre,
            'tipo'     => $mejora->tipo,
        ]);

        return response()->json([
            'message'         => '¡Mejora activada! ' . $efectoAplicado,
            'efecto'          => $efectoAplicado,
            'mejora'          => [
                'id'     => $mejora->id,
                'nombre' => $mejora->nombre,
                'tipo'   => $mejora->tipo,
                'icon'   => $this->getMejoraIcon($mejora->tipo),
            ],
            'lives'           => $session['lives'],
            'time_remaining'  => $session['time_remaining'],
            'coins_earned'    => $session['coins_earned'],
            'coin_multiplier' => $session['coin_multiplier'] ?? 1,
            'mejoras_activas' => $session['mejoras_activas'],
        ], 200);
    }

    /**
     * Devuelve un icono emoji según el tipo de mejora.
     */
    private function getMejoraIcon(string $tipo): string
    {
        return match ($tipo) {
            'vidas_extra'    => '❤️',
            'tiempo_extra'   => '⏱️',
            'multiplicador'  => '💰',
            'pista'          => '💡',
            default          => '⚡',
        };
    }

    /**
     * Obtiene el estado completo de la sesión actual.
     */
    public function getSessionStatus(Request $request)
    {
        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session) {
            return response()->json(['active' => false], 200);
        }

        // Calcular tiempo restante en tiempo real
        $timeRemaining = self::TIME_PER_LEVEL;
        if ($session['level_started_at']) {
            $elapsed = now()->diffInSeconds($session['level_started_at']);
            $timeRemaining = max(0, self::TIME_PER_LEVEL - $elapsed);
        }

        return response()->json([
            'active'           => true,
            'lives'            => $session['lives'],
            'time_remaining'   => $timeRemaining,
            'levels_completed' => $session['levels_completed'],
            'coins_earned'     => $session['coins_earned'],
            'xp_earned'        => $session['xp_earned'],
        ], 200);
    }

    // ==========================================
    // HELPERS (Private)
    // ==========================================

    /**
     * Genera la cache key vinculada al usuario.
     */
    private function getCacheKey(int $userId): string
    {
        return "roguelike_session_{$userId}";
    }

    /**
     * Obtiene la sesión del cache.
     */
    private function getSession(int $userId): ?array
    {
        return Cache::get($this->getCacheKey($userId));
    }

    /**
     * Guarda la sesión en cache.
     */
    private function saveSession(int $userId, array $session): void
    {
        Cache::put($this->getCacheKey($userId), $session, self::CACHE_TTL);
    }

    /**
     * Respuesta estándar cuando no hay sesión activa.
     */
    private function noSessionResponse()
    {
        return response()->json([
            'message' => 'No hay sesión activa. Inicia una nueva partida.',
        ], 404);
    }

    /**
     * Extrae las estadísticas de la sesión para el Game Over.
     */
    private function getSessionStats(array $session): array
    {
        return [
            'niveles_superados'  => $session['levels_completed'],
            'monedas_obtenidas'  => $session['coins_earned'],
            'xp_ganada'          => $session['xp_earned'],
            'vidas_restantes'    => $session['lives'],
        ];
    }

    /**
     * Persiste la run finalizada en la base de datos.
     */
    private function saveRun(array $session): void
    {
        try {
            RunsRoguelike::create([
                'usuario_id'        => $session['user_id'],
                'vidas_restantes'   => $session['lives'],
                'niveles_superados' => $session['levels_completed'],
                'monedas_obtenidas' => $session['coins_earned'],
                'estado'            => 'finalizada',
                'data_partida'      => [
                    'xp_earned'  => $session['xp_earned'],
                    'started_at' => $session['started_at'],
                    'ended_at'   => now()->toISOString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error guardando run roguelike: ' . $e->getMessage());
        }
    }
}
