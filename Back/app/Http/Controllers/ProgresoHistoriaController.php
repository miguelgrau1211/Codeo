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

    /**
     * Obtiene el progreso detallado del usuario en el Modo Historia.
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgresoModoHistoriaUsuario($idUsuario)
    {
        // verificamos que el usuario existe
        $usuarioExists = \App\Models\Usuario::where('id', $idUsuario)->exists();
        
        if (!$usuarioExists) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //obtenemos el progreso con los datos del nivel
        $progreso = \App\Models\ProgresoHistoria::where('usuario_id', $idUsuario)->join('niveles_historia', 'usuario_progreso_historia.nivel_id', '=', 'niveles_historia.id')
            ->select(
                'usuario_progreso_historia.*', 
                'niveles_historia.titulo', 
                'niveles_historia.orden'
            )->orderBy('niveles_historia.orden', 'asc')->get();

        //estadísticas para el Dashboard
        $totalNiveles = \App\Models\NivelHistoria::count();
        $nivelesCompletados = $progreso->where('completado', true)->count();
        
        //porcentaje
        $porcentajeCerrado = $totalNiveles > 0 ? round(($nivelesCompletados / $totalNiveles) * 100) : 0;

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'stats' => [
                'total_niveles' => $totalNiveles,
                'completados' => $nivelesCompletados,
                'porcentaje_progreso' => $porcentajeCerrado . '%'
            ],
            'progreso_detallado' => $progreso
        ], 200);
    }
}