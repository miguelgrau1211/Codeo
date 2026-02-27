<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Actions\Achievements\GetAchievementsStatusAction;
use App\Actions\Achievements\AssignAchievementManualAction;
use App\Models\UsuarioLogro;
use App\Services\TranslationService;

/**
 * Controlador para el progreso de logros de los usuarios.
 */
class UsuarioLogroController extends Controller
{
    /**
     * Lista completa de logros con su estado de desbloqueo para el usuario actual.
     */
    public function getLogrosDesbloqueados(Request $request, GetAchievementsStatusAction $action): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        $result = $action->execute(Auth::id(), $locale);

        return response()->json([
            'usuario_id' => Auth::id(),
            'progreso_logros' => $result['progreso_texto'],
            'lista_completa' => $result['lista']
        ]);
    }

    /**
     * Obtiene solo los logros ya conseguidos por el usuario.
     */
    public function getLogrosUsuario(Request $request, GetAchievementsStatusAction $action): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        $result = $action->execute(Auth::id(), $locale);

        // Filtramos solo los desbloqueados para esta respuesta específica
        $desbloqueados = collect($result['lista'])->where('desbloqueado', true)->values();

        return response()->json([
            'usuario_id' => Auth::id(),
            'total_logros' => $desbloqueados->count(),
            'logros' => $desbloqueados
        ]);
    }

    /**
     * Asignación manual de un logro (Admin o eventos especiales).
     */
    public function store(Request $request, AssignAchievementManualAction $action): JsonResponse
    {
        $request->validate(['logro_id' => 'required|exists:logros,id']);

        try {
            $nuevoLogro = $action->execute(Auth::id(), $request->logro_id);
            return response()->json(['message' => '¡Logro desbloqueado!', 'data' => $nuevoLogro], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    /**
     * Revocación de un logro.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['logro_id' => 'required|exists:logros,id']);

        UsuarioLogro::where('usuario_id', Auth::id())
            ->where('logro_id', $request->logro_id)
            ->delete();

        return response()->json(['message' => 'Logro revocado correctamente']);
    }

    /**
     * Porcentaje de completitud para el Dashboard.
     */
    public function getPorcentajeLogros(GetAchievementsStatusAction $action): JsonResponse
    {
        $result = $action->execute(Auth::id(), 'es'); // El idioma da igual para números

        return response()->json([
            'usuario_id' => Auth::id(),
            'logros_obtenidos' => $result['total_obtenidos'],
            'total_disponibles' => $result['total_disponibles'],
            'porcentaje' => $result['total_disponibles'] > 0
                ? round(($result['total_obtenidos'] / $result['total_disponibles']) * 100, 2)
                : 0
        ]);
    }
}
