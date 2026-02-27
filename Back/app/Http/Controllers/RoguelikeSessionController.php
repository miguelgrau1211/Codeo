<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Actions\Roguelike\StartRoguelikeSessionAction;
use App\Actions\Roguelike\ProcessRoguelikeSuccessAction;
use App\Actions\Roguelike\ProcessRoguelikeFailureAction;
use App\Actions\Roguelike\CheckRoguelikeTimerAction;
use App\Actions\Roguelike\ApplyRoguelikeUpgradeAction;
use App\Services\TranslationService;

/**
 * Controlador de Sesiones Roguelike.
 * Refactorizado para ser un "Skinny Controller" delegando la lógica a Actions.
 */
class RoguelikeSessionController extends Controller
{
    /**
     * Inicia una nueva partida de roguelike.
     */
    public function startSession(StartRoguelikeSessionAction $action): JsonResponse
    {
        $result = $action->execute(Auth::id());

        return response()->json([
            'message' => 'Sesión iniciada',
            'lives' => $result['session']['lives'],
            'time_remaining' => $result['session']['time_remaining'],
            'coins_earned' => $result['session']['coins_earned'],
            'mejoras_activas' => $result['session']['mejoras_activas'],
            'nuevos_logros' => $result['nuevos_logros']
        ]);
    }

    /**
     * Inicia el temporizador para un nivel específico.
     */
    public function startLevel(): JsonResponse
    {
        $userId = Auth::id();
        $session = $this->getSession($userId);

        if (!$session)
            return $this->noSessionResponse();
        if ($session['lives'] <= 0)
            return response()->json(['message' => 'Sin vidas restantes', 'game_over' => true], 403);

        // Marcamos el inicio del nivel con el tiempo del servidor (UTC)
        $session['level_started_at'] = now('UTC')->toDateTimeString();
        $session['time_remaining'] = 300; // Reset a 5 mins

        Cache::put("roguelike_session_$userId", $session, 7200);

        return response()->json([
            'message' => 'Nivel iniciado',
            'lives' => $session['lives'],
            'time_remaining' => $session['time_remaining']
        ]);
    }

    /**
     * Valida el tiempo restante (prevención de trampas).
     */
    public function checkTime(CheckRoguelikeTimerAction $timerAction, ProcessRoguelikeFailureAction $failureAction): JsonResponse
    {
        $user = Auth::user();
        $session = $this->getSession($user->id);

        if (!$session)
            return $this->noSessionResponse();

        $result = $timerAction->execute($user, $session, $failureAction);
        return response()->json($result);
    }

    /**
     * Registra un fallo al resolver un reto.
     */
    public function registerFailure(ProcessRoguelikeFailureAction $action): JsonResponse
    {
        $user = Auth::user();
        $session = $this->getSession($user->id);

        if (!$session)
            return $this->noSessionResponse();

        $result = $action->execute($user, $session);
        return response()->json($result);
    }

    /**
     * Registra el éxito al resolver un reto.
     */
    public function registerSuccess(Request $request, ProcessRoguelikeSuccessAction $action): JsonResponse
    {
        $user = Auth::user();
        $session = $this->getSession($user->id);

        if (!$session)
            return $this->noSessionResponse();

        $result = $action->execute($user, $session);

        // Traducimos los logros si existen para la respuesta
        $locale = TranslationService::resolveLocale($request);
        $result['nuevos_logros'] = app(TranslationService::class)->translateLogrosCollection($result['nuevos_logros'], $locale);

        return response()->json($result);
    }

    /**
     * Compra de mejoras en la tienda de la sesión.
     */
    public function buyMejora(Request $request, ApplyRoguelikeUpgradeAction $action): JsonResponse
    {
        $request->validate(['mejora_id' => 'required|integer|exists:mejoras,id']);

        $user = Auth::user();
        $session = $this->getSession($user->id);
        if (!$session)
            return $this->noSessionResponse();

        try {
            $result = $action->execute($user, $session, $request->mejora_id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Obtiene el estado persistente de la sesión.
     */
    public function getSessionStatus(): JsonResponse
    {
        $session = $this->getSession(Auth::id());

        if (!$session)
            return response()->json(['active' => false]);

        return response()->json(array_merge($session, ['active' => true]));
    }

    /**
     * Obtiene la clave de caché estándar para la sesión de un usuario.
     */
    public static function getCacheKey(int $userId): string
    {
        return "roguelike_session_$userId";
    }

    // --- Helpers Privados ---

    private function getSession($userId)
    {
        return Cache::get(self::getCacheKey($userId));
    }

    private function noSessionResponse(): JsonResponse
    {
        return response()->json(['message' => 'No hay sesión activa.'], 404);
    }
}
