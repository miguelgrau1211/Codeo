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
}