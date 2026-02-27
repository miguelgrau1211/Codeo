<?php

namespace App\Http\Controllers;

use App\Models\UsuarioDesactivado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Actions\User\ReactivateUserAction;

/**
 * Controlador para la gestión de usuarios desactivados (archivados).
 */
class UsuarioDesactivadoController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios desactivados.
     */
    public function index(): JsonResponse
    {
        $desactivados = UsuarioDesactivado::orderByDesc('fecha_desactivacion')->get();
        return response()->json($desactivados);
    }

    /**
     * Reactiva un usuario y lo devuelve a la tabla principal.
     */
    public function reactivar(Request $request, $idOriginal, ReactivateUserAction $action): JsonResponse
    {
        try {
            $action->execute((int) $idOriginal, $request->input('password'));

            return response()->json([
                'message' => 'Usuario reactivado con éxito. Se ha asignado la contraseña proporcionada o una por defecto.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}