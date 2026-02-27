<?php

namespace App\Actions\Story;

use App\Models\ProgresoHistoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Actions\Achievements\CheckAchievementsAction;
use App\Actions\UpdateUserStreakAction;
use App\Actions\ProcessLevelUpAction;

/**
 * Acción para guardar el progreso de un usuario en un nivel de historia.
 * Gestiona el guardado en BD y el otorgamiento de recompensas por primera vez.
 */
class SaveStoryProgressAction
{
    /**
     * @param Usuario $user
     * @param array $data ['nivel_id', 'completado', 'codigo_solucion_usuario']
     * @return array
     */
    public function execute(Usuario $user, array $data): array
    {
        $nivelId = $data['nivel_id'];
        $completado = $data['completado'];

        // 1. Verificamos si ya estaba completado para evitar duplicar recompensas
        $progresoAnterior = ProgresoHistoria::where('usuario_id', $user->id)
            ->where('nivel_id', $nivelId)
            ->first();

        $yaEstabaCompletado = $progresoAnterior && $progresoAnterior->completado;

        // 2. Guardamos el progreso
        $progreso = ProgresoHistoria::updateOrCreate(
            ['usuario_id' => $user->id, 'nivel_id' => $nivelId],
            ['completado' => $completado, 'codigo_solucion_usuario' => $data['codigo_solucion_usuario'] ?? '']
        );

        $recompensas = [];

        // 3. Si se completa por primera vez, otorgamos XP y Monedas
        if ($completado && !$yaEstabaCompletado) {
            $xpGanada = 100;
            $monedasGanadas = 50;

            DB::transaction(function () use ($user, $xpGanada, $monedasGanadas) {
                DB::table('usuarios')->where('id', $user->id)->lockForUpdate()->increment('exp_total', $xpGanada);
                DB::table('usuarios')->where('id', $user->id)->increment('monedas', $monedasGanadas);
            });

            $recompensas = [
                'xp' => $xpGanada,
                'monedas' => $monedasGanadas,
                'mensaje' => "¡Nivel completado! +{$xpGanada} XP y +{$monedasGanadas} monedas."
            ];

            Log::info('Recompensas Historia otorgadas', ['user' => $user->id, 'nivel' => $nivelId]);
        }

        // 4. Lógica de Gamificación (solo si se ha completado)
        $gamificacion = [];
        if ($completado) {
            // Invalidar la caché del dashboard para mostrar datos actualizados
            \Illuminate\Support\Facades\Cache::forget("user_summary_{$user->id}");

            $gamificacion = [
                'nuevos_logros' => (new CheckAchievementsAction())->execute(),
                'racha' => (new UpdateUserStreakAction())->execute($user),
                'level_up' => (new ProcessLevelUpAction())->execute($user)
            ];
        }

        return [
            'progreso' => $progreso,
            'recompensas' => $recompensas,
            'gamificacion' => $gamificacion
        ];
    }
}
