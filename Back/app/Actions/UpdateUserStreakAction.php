<?php

namespace App\Actions;

use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateUserStreakAction
{
    /**
     * Actualiza la racha del usuario tras completar un nivel.
     * 
     * @param Usuario $usuario
     * @return array Datos de la racha actualizada
     */
    public function execute(Usuario $usuario): array
    {
        $now = Carbon::now();
        $lastCompletion = $usuario->ultimo_nivel_completado_at;
        
        $streakUpdated = false;
        $streakReset = false;

        if (!$lastCompletion) {
            // Primera vez
            $usuario->streak = 1;
            $usuario->max_streak = max($usuario->max_streak, 1);
            $streakUpdated = true;
        } else {
            $lastCompletionDate = Carbon::parse($lastCompletion)->startOfDay();
            $today = $now->copy()->startOfDay();
            $yesterday = $now->copy()->subDay()->startOfDay();

            if ($lastCompletionDate->equalTo($today)) {
                // Ya completó algo hoy, no incrementamos racha pero actualizamos timestamp
                // (Opcional: podrías decidir no actualizar el timestamp para mantener el "primer logro del día")
            } elseif ($lastCompletionDate->equalTo($yesterday)) {
                // Completó ayer, incrementamos racha
                $usuario->streak += 1;
                $usuario->max_streak = max($usuario->max_streak, $usuario->streak);
                $streakUpdated = true;
            } else {
                // Pasó más de un día, racha reiniciada
                $usuario->streak = 1;
                $streakReset = true;
                $streakUpdated = true;
            }
        }

        $usuario->ultimo_nivel_completado_at = $now;
        $usuario->save();

        Log::info('Racha actualizada para usuario', [
            'usuario_id' => $usuario->id,
            'streak' => $usuario->streak,
            'updated' => $streakUpdated,
            'reset' => $streakReset
        ]);

        return [
            'streak' => $usuario->streak,
            'max_streak' => $usuario->max_streak,
            'updated' => $streakUpdated,
            'reset' => $streakReset
        ];
    }
    
    /**
     * Verifica si la racha debe reiniciarse (al iniciar sesión o cargar datos).
     * Se llama cuando el usuario entra para que vea su racha real.
     */
    public function checkAndResetIfNecessary(Usuario $usuario): void
    {
        if (!$usuario->ultimo_nivel_completado_at) return;

        $lastCompletionDate = Carbon::parse($usuario->ultimo_nivel_completado_at)->startOfDay();
        $yesterday = Carbon::now()->subDay()->startOfDay();

        // Si la última racha fue antes de ayer, se reinicia a 0 (porque hoy aún no ha hecho nada)
        if ($lastCompletionDate->lessThan($yesterday)) {
            $usuario->streak = 0;
            $usuario->save();
        }
    }
}
