<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\AdminLog;
use App\Actions\Admin\GetAdminStatsAction;

/**
 * Controlador para el panel de administración central.
 */
class AdminDashboardController extends Controller
{
    /**
     * Obtiene estadísticas rápidas del sistema.
     */
    public function getStats(GetAdminStatsAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    /**
     * Obtiene el historial de acciones administrativas con paginación.
     */
    public function getLogs(Request $request): JsonResponse
    {
        $query = AdminLog::with('user:id,nickname,email')->orderByDesc('created_at');

        // Filtro por tipo de acción si se solicita
        if ($request->has('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        return response()->json($query->paginate(15));
    }
}
