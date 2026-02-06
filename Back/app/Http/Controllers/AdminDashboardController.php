<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\RunsRoguelike;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Get Dashboard Stats
     */
    public function getStats()
    {
        // total
        $totalUsers = Usuario::count();

        // activos en 24h
        $activeUsers = Usuario::where('updated_at', '>=', Carbon::now()->subDay())->count();

        // total runs
        $totalRuns = RunsRoguelike::count();

        // tasa de exito
        $successfulRuns = RunsRoguelike::where('estado', 'completed')->orWhere('estado', 'win')->count();

        $successRate = $totalRuns > 0 ? round(($successfulRuns / $totalRuns) * 100, 1) : 0;

        return response()->json([
            'total_users' => $totalUsers,
            'active_users_24h' => $activeUsers,
            'total_runs' => $totalRuns,
            'success_rate' => $successRate
        ]);
    }

    /**
     * Get System Logs
     */
    public function getLogs(Request $request)
    {
        $query = AdminLog::with('user:id,nombre,email')->orderBy('created_at', 'desc');

        // filtro simple
        if ($request->has('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        $logs = $query->paginate(15);

        return response()->json($logs);
    }
}
