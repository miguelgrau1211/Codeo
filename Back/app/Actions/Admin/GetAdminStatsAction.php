<?php

namespace App\Actions\Admin;

use App\Models\Usuario;
use App\Models\RunsRoguelike;
use Carbon\Carbon;

/**
 * Acción para recopilar estadísticas globales del sistema para el panel de administración.
 */
class GetAdminStatsAction
{
    public function execute(): array
    {
        $totalUsers = Usuario::count();
        $activeUsers = Usuario::where('updated_at', '>=', Carbon::now()->subDay())->count();
        $totalRuns = RunsRoguelike::count();

        // Calculamos tasa de éxito (partidas ganadas o completadas)
        $successfulRuns = RunsRoguelike::whereIn('estado', ['completed', 'win', 'finalizada'])->count();
        $successRate = $totalRuns > 0 ? round(($successfulRuns / $totalRuns) * 100, 1) : 0;

        return [
            'total_users' => $totalUsers,
            'active_users_24h' => $activeUsers,
            'total_runs' => $totalRuns,
            'success_rate' => $successRate,
            'timestamp' => now()->toISOString()
        ];
    }
}
