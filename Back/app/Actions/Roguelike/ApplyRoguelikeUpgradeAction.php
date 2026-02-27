<?php

namespace App\Actions\Roguelike;

use App\Models\Mejoras;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Acción para comprar y aplicar una mejora en la sesión de Roguelike.
 */
class ApplyRoguelikeUpgradeAction
{
    private const UPGRADE_COST = 100;

    /**
     * @return array Resultado de la compra y estado actualizado.
     */
    public function execute(Usuario $user, array $session, int $upgradeId): array
    {
        $mejora = Mejoras::findOrFail($upgradeId);

        // 1. Validar fondos (monedas de la sesión actual)
        if ($session['coins_earned'] < self::UPGRADE_COST) {
            throw new \Exception('No tienes suficientes monedas de sesión. Coste: ' . self::UPGRADE_COST);
        }

        // 2. Deducir coste
        $session['coins_earned'] -= self::UPGRADE_COST;

        // 3. Aplicar efecto
        $mensaje = '';
        switch ($mejora->tipo) {
            case 'vidas_extra':
                $session['lives'] += 1;
                $mensaje = '+1 vida extra.';
                break;
            case 'tiempo_extra':
                $session['time_remaining'] = ($session['time_remaining'] ?? 300) + 60;
                $mensaje = '+60 segundos extra.';
                break;
            case 'multiplicador':
                $session['coin_multiplier'] = ($session['coin_multiplier'] ?? 1) * 2;
                $mensaje = 'Multiplicador x2 activado.';
                break;
            case 'pista':
                $mensaje = 'Pista revelada para el nivel actual.';
                break;
        }

        // 4. Registrar mejora activa
        $session['mejoras_activas'][] = [
            'id' => $mejora->id,
            'nombre' => $mejora->nombre,
            'tipo' => $mejora->tipo,
            'icon' => $this->getIcon($mejora->tipo)
        ];

        // 5. Persistir
        Cache::put("roguelike_session_" . $user->id, $session, 7200);

        return [
            'message' => '¡Mejora activada! ' . $mensaje,
            'session' => $session
        ];
    }

    private function getIcon(string $tipo): string
    {
        return match ($tipo) {
            'vidas_extra' => '❤️',
            'tiempo_extra' => '⏱️',
            'multiplicador' => '💰',
            'pista' => '💡',
            default => '⚡',
        };
    }
}
