<?php

namespace App\Actions;

use App\Models\Usuario;
use Illuminate\Support\Facades\Log;

class ProcessLevelUpAction
{
    /**
     * Procesa la subida de nivel del usuario basado en su experiencia total.
     * La regla es: se sube de nivel cada 500 de XP.
     * Nivel 1: 0-499 XP
     * Nivel 2: 500-999 XP
     * Nivel N: (N-1)*500 XP
     */
    public function execute(Usuario $usuario): array
    {
        $oldLevel = $usuario->nivel_global;
        
        // Calcular el nuevo nivel (empezando en nivel 1)
        $newLevel = floor($usuario->exp_total / 500) + 1;

        $leveledUp = false;

        if ($newLevel > $oldLevel) {
            $usuario->nivel_global = $newLevel;
            $usuario->save();
            $leveledUp = true;

            // Invalidar la caché del dashboard al subir de nivel
            \Illuminate\Support\Facades\Cache::forget("user_summary_{$usuario->id}");

            // Otorgar recompensas del pase de batalla si corresponde
            $battlePassRewards = (new \App\Actions\GrantBattlePassRewardsAction())->execute($usuario);

            Log::info("Usuario subió de nivel", [
                'usuario_id' => $usuario->id,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'exp_total' => $usuario->exp_total,
                'battle_pass_rewards' => count($battlePassRewards)
            ]);
        }

        return [
            'leveled_up' => $leveledUp,
            'old_level' => (int) $oldLevel,
            'current_level' => (int) $usuario->nivel_global,
            'exp_total' => (int) $usuario->exp_total,
            'next_level_exp' => (int) ($usuario->nivel_global * 500)
        ];
    }
}
