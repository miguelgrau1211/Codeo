<?php

namespace App\Http\Controllers;

use App\Models\UsuarioLogro;
use Illuminate\Http\Request;

class UsuarioLogroController extends Controller
{
    // logros conseguidos
    public function index(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id'
        ]);

        $logros = UsuarioLogro::where('usuario_id', $request->usuario_id)->with('logro')->get();

        return response()->json($logros, 200);
    }

    // Asigna un logro 
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'logro_id'   => 'required|exists:logros,id',
        ]);

        //crear el registro
        try {
            $nuevoLogro = UsuarioLogro::create([
                'usuario_id' => $validatedData['usuario_id'],
                'logro_id'   => $validatedData['logro_id'],
                'fecha_desbloqueo' => now()
            ]);

            return response()->json([
                'message' => '¡Logro desbloqueado con éxito!',
                'data' => $nuevoLogro
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'El usuario ya posee este logro o ha ocurrido un error.',
                'error' => $e->getMessage()
            ], 409);
        }
    }

    // Elimina un logro
    public function destroy(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'logro_id'   => 'required|exists:logros,id',
        ]);

        UsuarioLogro::where('usuario_id', $request->usuario_id)
            ->where('logro_id', $request->logro_id)
            ->delete();

        return response()->json(['message' => 'Logro revocado correctamente'], 200);
    }
    /**
     * Obtiene todos los logros de un usuario con los detalles del logro.
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogrosUsuario($idUsuario)
    {
        //verificamos si el usuario existe
        $usuarioExists = \App\Models\Usuario::where('id', $idUsuario)->exists();
        
        if (!$usuarioExists) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // obtenemos los logros del usuario
        $logrosConseguidos = \App\Models\UsuarioLogro::where('usuario_id', $idUsuario)
            ->with('logro') 
            ->get();

        // mapeamos los datos para que el objeto 'logro' esté al mismo nivel que la fecha
        $resultado = $logrosConseguidos->map(function ($item) {
            return [
                'logro_id'         => $item->logro_id,
                'nombre'           => $item->logro->nombre,
                'descripcion'      => $item->logro->descripcion,
                'icono_url'        => $item->logro->icono_url,
                'fecha_desbloqueo' => $item->fecha_desbloqueo,
                'requisito_tipo'   => $item->logro->requisito_tipo,
            ];
        });

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'total_logros' => $resultado->count(),
            'logros' => $resultado
        ], 200);
    }
}