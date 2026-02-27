<?php

namespace App\Actions;

use App\Models\Usuario;
use App\Models\Tema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrantBattlePassRewardsAction
{
    /**
     * Recompensas definidas por nivel.
     */
    private const REWARDS = [
        5 => ['type' => 'theme', 'value' => 'Cyber Volcanic'],
        12 => ['type' => 'theme', 'value' => 'Aurora Borealis'],
        20 => ['type' => 'coins', 'value' => 500],
        28 => ['type' => 'theme', 'value' => 'Gold Rush'],
        35 => ['type' => 'theme', 'value' => 'Void Master'],
        42 => ['type' => 'coins', 'value' => 5000],
    ];

    /**
     * Otorga las recompensas del pase de batalla pendientes para el nivel actual del usuario.
     * 
     * @param Usuario $usuario
     * @return array Lista de recompensas otorgadas
     */
    public function execute(Usuario $usuario): array
    {
        // Solo otorgar recompensas si el usuario tiene el Pase de Batalla (Premium)
        if (!$usuario->es_premium) {
            return [];
        }

        $grantedRewards = [];
        $currentLevel = $usuario->nivel_global;

        foreach (self::REWARDS as $level => $reward) {
            if ($currentLevel >= $level) {
                // Verificar si ya ha recibido esta recompensa
                $alreadyGranted = DB::table('usuario_battle_pass_rewards')
                    ->where('usuario_id', $usuario->id)
                    ->where('nivel_recompensa', $level)
                    ->exists();

                // Si ya se otorgó según la tabla, pero es un tema y el usuario no lo tiene, permitir re-otorgar
                // (útil si cambiamos la recompensa de un nivel específico)
                $needsTheme = ($reward['type'] === 'theme' && !$usuario->temas()->where('nombre', $reward['value'])->exists());

                if (!$alreadyGranted || $needsTheme) {
                    $this->grantReward($usuario, $level, $reward);
                    $grantedRewards[] = array_merge(['level' => $level], $reward);
                }
            }
        }

        return $grantedRewards;
    }

    /**
     * Lógica interna para otorgar una recompensa específica (monedas o temas).
     */
    private function grantReward(Usuario $usuario, int $level, array $reward): void
    {
        DB::transaction(function () use ($usuario, $level, $reward) {
            // 1. Registrar el otorgamiento (updateOrInsert por si ya existía pero estamos forzando re-otorgar)
            DB::table('usuario_battle_pass_rewards')->updateOrInsert(
                ['usuario_id' => $usuario->id, 'nivel_recompensa' => $level],
                ['granted_at' => now()]
            );

            // 2. Aplicar la recompensa según el tipo
            if ($reward['type'] === 'coins') {
                $usuario->monedas += $reward['value'];
                $usuario->save();
                Log::info("Pase de Batalla: Monedas otorgadas", [
                    'usuario_id' => $usuario->id,
                    'nivel' => $level,
                    'cantidad' => $reward['value']
                ]);
            } elseif ($reward['type'] === 'theme') {
                $tema = Tema::where('nombre', $reward['value'])->first();
                if ($tema) {
                    // Otorgar solo si el usuario no tiene el tema registrado
                    if (!$usuario->temas()->where('tema_id', $tema->id)->exists()) {
                        $usuario->temas()->attach($tema->id, ['comprado_at' => now()]);
                        Log::info("Pase de Batalla: Tema visual otorgado", [
                            'usuario_id' => $usuario->id,
                            'nivel' => $level,
                            'tema' => $reward['value']
                        ]);
                    }
                }
            }
        });
    }
}
