<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Actions\Reports\CreateReportAction;
use App\Actions\Reports\UpdateReportStatusAction;

/**
 * Controlador para la gestión de Reportes (Soporte, Bugs, Sugerencias).
 */
class ReporteController extends Controller
{
    /**
     * Lista todos los reportes (Generalmente para Admin).
     */
    public function index(): JsonResponse
    {
        return response()->json(Reporte::with('usuario')->get());
    }

    /**
     * Crea un nuevo reporte desde el frontend.
     */
    public function store(Request $request, CreateReportAction $action): JsonResponse
    {
        $validatedData = $request->validate([
            'email_contacto' => 'nullable|email|max:255',
            'tipo' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'prioridad' => 'nullable|string|in:baja,media,alta,critica',
        ]);

        $reporte = $action->execute(Auth::user(), $validatedData);

        return response()->json([
            'message' => 'Reporte creado correctamente. ¡Gracias por ayudarnos!',
            'data' => $reporte
        ], 201);
    }

    /**
     * Muestra un reporte específico.
     */
    public function show($id): JsonResponse
    {
        return response()->json(Reporte::with('usuario')->findOrFail($id));
    }

    /**
     * Actualiza el estado de un reporte y aplica recompensas/sanciones (Solo Admin).
     */
    public function update(Request $request, $id, UpdateReportStatusAction $action): JsonResponse
    {
        $validatedData = $request->validate([
            'estado' => 'nullable|string|in:pendiente,en revision,solucionado,rechazado,spam',
            'prioridad' => 'nullable|string|in:baja,media,alta,critica',
        ]);

        $result = $action->execute($id, $validatedData);

        return response()->json([
            'message' => $result['mensaje'],
            'data' => $result['reporte']
        ]);
    }

    /**
     * Elimina un reporte de la base de datos.
     */
    public function destroy($id): JsonResponse
    {
        Reporte::findOrFail($id)->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }
}