<?php

namespace App\Actions\Roguelike;

use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Acción para validar el tiempo restante de un nivel de Roguelike.
 * Protege contra trampas del lado del cliente validando timestamps de servidor.
 */
class CheckRoguelikeTimerAction
{
    private const TIME_PER_LEVEL = 300;

    /**
     * Valida el tiempo y resta vida si ha expirado.
     * 
     * @param Usuario $user
     * @param array $session
     * @param ProcessRoguelikeFailureAction $failureAction
     * @return array
     */
    public function execute(Usuario $user, array $session, ProcessRoguelikeFailureAction $failureAction): array
    {
        if (!isset($session['level_started_at']) || !$session['level_started_at']) {
            return ['time_expired' => false, 'time_remaining' => self::TIME_PER_LEVEL];
        }

        // 1. Calculamos tiempo transcurrido real
        $now = now('UTC');
        $startedAt = Carbon::parse($session['level_started_at'], 'UTC');
        $elapsed = $startedAt->diffInSeconds($now, false);

        $allocatedTime = $session['time_remaining'] ?? self::TIME_PER_LEVEL;
        $timeLeft = $allocatedTime - $elapsed;

        // 2. Si el tiempo se ha agotado
        if ($timeLeft <= 0) {
            // Procesamos como un fallo (pierde vida)
            $failureResult = $failureAction->execute($user, $session);

            // Si el juego continúa, le damos un minuto de gracia para que no pierda instantáneamente otra vida
            if (!$failureResult['game_over']) {
                $session = Cache::get("roguelike_session_" . $user->id);
                $session['time_remaining'] = 60;
                $session['level_started_at'] = now('UTC')->toDateTimeString();
                Cache::put("roguelike_session_" . $user->id, $session, 7200);
            }

            return array_merge($failureResult, [
                'time_expired' => true,
                'time_remaining' => 0
            ]);
        }

        return [
            'time_expired' => false,
            'time_remaining' => max(0, $timeLeft),
            'lives' => $session['lives']
        ];
    }
}
