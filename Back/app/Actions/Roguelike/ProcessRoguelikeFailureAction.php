<?php

namespace App\Actions\Roguelike;

use App\Models\RunsRoguelike;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Actions\Achievements\CheckAchievementsAction;
use App\Actions\ProcessLevelUpAction;
use App\Actions\GrantBattlePassRewardsAction;

/**
 * Acción para procesar un fallo en el Roguelike (código erróneo o tiempo agotado).
 */
class ProcessRoguelikeFailureAction
{
    /**
     * Ejecuta el procesamiento del fallo.
     */
    public function execute(Usuario $user, array $session): array
    {
        $gameOver = false;

        // 1. Restamos vida
        if ($session['lives'] <= 1) {
            $session['lives'] = 0;
            $gameOver = true;
        } else {
            $session['lives']--;
        }

        // 2. Persistencia en Cache
        Cache::put("roguelike_session_" . $user->id, $session, 7200);

        // 3. Si es Game Over, cerramos la Run en BD
        $nuevosLogros = [];
        if ($gameOver) {
            $this->closeRun($session);
            $nuevosLogros = (new CheckAchievementsAction())->execute();
        }

        return [
            'lives' => $session['lives'],
            'game_over' => $gameOver,
            'nuevos_logros' => $nuevosLogros,
            'stats' => $gameOver ? [
                'niveles_superados' => $session['levels_completed'],
                'monedas_obtenidas' => $session['coins_earned'],
                'xp_ganada' => $session['xp_earned'],
                'vidas_restantes' => 0
            ] : null
        ];
    }

    /**
     * Marca la run como fallida en la base de datos.
     */
    private function closeRun(array $session): void
    {
        try {
            $run = RunsRoguelike::find($session['run_id']);
            if ($run) {
                $run->update([
                    'vidas_restantes' => 0,
                    'estado' => 'fallido',
                    'data_partida' => array_merge($run->data_partida ?? [], [
                        'xp_earned' => $session['xp_earned'],
                        'ended_at' => now()->toISOString(),
                    ]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error cerrando run roguelike: ' . $e->getMessage());
        }
    }
}
