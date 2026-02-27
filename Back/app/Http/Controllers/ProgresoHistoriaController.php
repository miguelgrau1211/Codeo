<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Actions\Story\SaveStoryProgressAction;
use App\Actions\Story\GetStoryProgressSummaryAction;
use App\Services\TranslationService;

/**
 * Controlador de Progreso de Historia.
 * Gestiona el avance del usuario a través de los niveles teóricos.
 */
class ProgresoHistoriaController extends Controller
{
    /**
     * Guarda el progreso de un nivel y otorga recompensas.
     */
    public function store(Request $request, SaveStoryProgressAction $action): JsonResponse
    {
        $validatedData = $request->validate([
            'nivel_id' => 'required|exists:niveles_historia,id',
            'completado' => 'required|boolean',
            'codigo_solucion_usuario' => 'nullable|string|max:10000',
        ]);

        $result = $action->execute(Auth::user(), $validatedData);

        // Si hay nuevos logros, los traducimos antes de enviarlos
        if (!empty($result['gamificacion']['nuevos_logros'])) {
            $locale = TranslationService::resolveLocale($request);
            $result['gamificacion']['nuevos_logros'] = app(TranslationService::class)
                ->translateLogrosCollection($result['gamificacion']['nuevos_logros'], $locale);
        }

        return response()->json([
            'message' => 'Progreso guardado correctamente',
            'data' => $result['progreso'],
            'recompensas' => $result['recompensas'],
            'gamificacion' => $result['gamificacion']
        ]);
    }

    /**
     * Obtiene todo el progreso del modo historia para el usuario actual.
     */
    public function getProgresoModoHistoriaUsuario(Request $request, GetStoryProgressSummaryAction $action): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        $summary = $action->execute(Auth::id(), $locale);

        return response()->json([
            'usuario_id' => Auth::id(),
            'stats' => $summary['stats'],
            'progreso_detallado' => $summary['niveles']
        ]);
    }
}