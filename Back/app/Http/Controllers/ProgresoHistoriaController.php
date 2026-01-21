<?php

namespace App\Http\Controllers;

use App\Models\ProgresoHistoria;
use Illuminate\Http\Request;

class ProgresoHistoriaController extends Controller
{
    // progreso de un usuario
    public function index(Request $request)
    {
        $request->validate(['usuario_id' => 'required|exists:usuarios,id']);

        $progreso = ProgresoHistoria::where('usuario_id', $request->usuario_id)
            ->with('nivel')
            ->get();

        return response()->json($progreso, 200);
    }

    //Guarda o actualiza el progreso de un nivel
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'nivel_id'   => 'required|exists:niveles_historia,id',
            'completado' => 'required|boolean',
            'codigo_solucion_usuario' => 'nullable|string'
        ]);

        //Si ya existe lo actualiza si no lo crea
        $progreso = ProgresoHistoria::updateOrCreate(
            [
                'usuario_id' => $validatedData['usuario_id'],
                'nivel_id'   => $validatedData['nivel_id']
            ],
            [
                'completado' => $validatedData['completado'],
                'codigo_solucion_usuario' => $validatedData['codigo_solucion_usuario']
            ]
        );

        return response()->json([
            'message' => 'Progreso guardado correctamente',
            'data' => $progreso
        ], 200);
    }
}