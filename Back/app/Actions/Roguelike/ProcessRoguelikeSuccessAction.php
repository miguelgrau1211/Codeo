<?php

namespace App\Actions\Roguelike;

use App\Models\RunsRoguelike;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Actions\Achievements\CheckAchievementsAction;
use App\Actions\UpdateUserStreakAction;
use App\Actions\ProcessLevelUpAction;

/**
 * Acción para procesar la superación exitosa de un nivel en Roguelike.
 * Gestiona recompensas, experiencia, racha, subida de nivel y persistencia.
 */
class ProcessRoguelikeSuccessAction
{
    /**
     * Ejecuta el procesamiento del éxito.
     * 
     * @param Usuario $user
     * @param array $session Referencia a la sesión actual en cache.
     * @return array Datos actualizados para el frontend.
     */
    public function execute(Usuario $user, array $session): array
    {
        // 1. Calculamos recompensas
        $multiplier = $session['coin_multiplier'] ?? 1;
        $coinsGained = 50 * $multiplier;
        $xpGained = 50;

        // 2. Actualizamos el estado de la sesión local
        $session['levels_completed'] += 1;
        $session['coins_earned'] += $coinsGained;
        $session['xp_earned'] += $xpGained;

        // Limpiamos el nivel actual para que el siguiente sea nuevo
        if (isset($session['current_level_id'])) {
            $session['used_level_ids'][] = $session['current_level_id'];
            $session['current_level_id'] = null;
        }

        // 3. Persistencia en Cache
        Cache::put("roguelike_session_" . $user->id, $session, 7200);

        // 4. Persistencia en Base de Datos (Run y Usuario)
        try {
            DB::transaction(function () use ($user, $session, $coinsGained, $xpGained) {
                // Actualizar la Run
                $run = RunsRoguelike::find($session['run_id']);
                if ($run) {
                    $run->update([
                        'vidas_restantes' => $session['lives'],
                        'niveles_superados' => $session['levels_completed'],
                        'monedas_obtenidas' => $session['coins_earned'],
                        'data_partida' => array_merge($run->data_partida ?? [], [
                            'xp_earned' => $session['xp_earned'],
                            'ended_at' => now()->toISOString(),
                        ]),
                    ]);
                }

                // Otorgar recompensas al perfil global del usuario
                DB::table('usuarios')
                    ->where('id', $user->id)
                    ->lockForUpdate()
                    ->increment('exp_total', $xpGained);

                DB::table('usuarios')
                    ->where('id', $user->id)
                    ->increment('monedas', $coinsGained);
            });
        } catch (\Throwable $e) {
            Log::error('Error en persistencia Roguelike Success: ' . $e->getMessage());
        }

        // 5. Lógica de Gamificación (Logros, Racha, Nivel Global)
        // Invalidar la caché del dashboard para mostrar datos actualizados (XP, Monedas, Nivel)
        $user->refresh();
        Cache::forget("user_summary_{$user->id}");

        $nuevosLogros = (new CheckAchievementsAction())->execute();
        $rachaData = (new UpdateUserStreakAction())->execute($user);
        $levelUpData = (new ProcessLevelUpAction())->execute($user);

        return [
            'lives' => $session['lives'],
            'levels_completed' => $session['levels_completed'],
            'coins_earned' => $session['coins_earned'],
            'xp_earned' => $session['xp_earned'],
            'nuevos_logros' => $nuevosLogros,
            'racha' => $rachaData,
            'level_up' => $levelUpData
        ];
    }
}
