<?php

namespace App\Actions\Roguelike;

use App\Models\RunsRoguelike;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Actions\Achievements\CheckAchievementsAction;
use App\Http\Controllers\RoguelikeSessionController;

/**
 * Acción para iniciar una nueva partida de Roguelike.
 * Encargada de crear el registro en BD e inicializar el estado en Cache.
 */
class StartRoguelikeSessionAction
{
    private const INITIAL_LIVES = 3;
    private const TIME_PER_LEVEL = 300;
    private const CACHE_TTL = 7200;

    /**
     * Ejecuta el inicio de la sesión.
     * 
     * @param int $userId
     * @return array Datos de la sesión inicializada.
     */
    public function execute(int $userId): array
    {
        // 1. Creamos la "Run" en la base de datos de forma inmediata.
        $run = RunsRoguelike::create([
            'usuario_id' => $userId,
            'vidas_restantes' => self::INITIAL_LIVES,
            'niveles_superados' => 0,
            'monedas_obtenidas' => 0,
            'estado' => 'activo',
            'data_partida' => [
                'xp_earned' => 0,
                'started_at' => now()->toISOString(),
            ],
        ]);

        // 2. Definimos el objeto de sesión que vivirá en Cache para máxima velocidad.
        $session = [
            'user_id' => $userId,
            'run_id' => $run->id,
            'lives' => self::INITIAL_LIVES,
            'levels_completed' => 0,
            'coins_earned' => 0,
            'xp_earned' => 0,
            'level_started_at' => null,
            'time_remaining' => self::TIME_PER_LEVEL,
            'started_at' => now()->toISOString(),
            'mejoras_activas' => [],
            'coin_multiplier' => 1,
            'current_level_id' => null,
            'used_level_ids' => [],
        ];

        // 3. Guardamos en cache con una clave única por usuario.
        Cache::put(RoguelikeSessionController::getCacheKey($userId), $session, self::CACHE_TTL);

        Log::info('Nueva sesión Roguelike iniciada', ['user_id' => $userId, 'run_id' => $run->id]);

        // 4. Verificamos si el simple hecho de empezar una partida otorga algún logro.
        $nuevosLogros = (new CheckAchievementsAction())->execute();

        return [
            'session' => $session,
            'nuevos_logros' => $nuevosLogros
        ];
    }
}
