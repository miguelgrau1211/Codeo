<?php

namespace App\Http\Controllers;

use App\Models\ProgresoHistoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NivelesHistoria;

class ProgresoHistoriaController extends Controller
{
    // progreso de un usuario
    // progreso de un usuario
    public function index()
    {
        $progreso = ProgresoHistoria::where('usuario_id', Auth::id())
            ->with('nivel')
            ->get();

        return response()->json($progreso, 200);
    }

    //Guarda o actualiza el progreso de un nivel
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nivel_id'   => 'required|exists:niveles_historia,id',
            'completado' => 'required|boolean',
            'codigo_solucion_usuario' => 'nullable|string'
        ]);

        //Si ya existe lo actualiza si no lo crea
        $progreso = ProgresoHistoria::updateOrCreate(
            [
                'usuario_id' => Auth::id(),
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
    /**
     * Obtiene el progreso detallado del usuario en el Modo Historia.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgresoModoHistoriaUsuario()
    {
        $idUsuario = Auth::id();

        //obtenemos el progreso con los datos del nivel
        // Usamos DB::raw para el COALESCE, asegurando que si el usuario no tiene codigo, se devuelve el inicial.
        $progreso = ProgresoHistoria::where('usuario_id', $idUsuario)
            ->join('niveles_historia', 'usuario_progreso_historia.nivel_id', '=', 'niveles_historia.id')
            ->select(
                'usuario_progreso_historia.*',
                // Sobrescribimos codigo_solucion_usuario con la lógica de fallback
                \Illuminate\Support\Facades\DB::raw('COALESCE(usuario_progreso_historia.codigo_solucion_usuario, niveles_historia.codigo_inicial) as codigo_solucion_usuario'),
                'niveles_historia.titulo', 
                'niveles_historia.orden',
                'niveles_historia.codigo_inicial', // Seleccionamos tambien el inicial por si acaso
                'niveles_historia.descripcion',
                'niveles_historia.contenido_teorico'
            )
            ->orderBy('niveles_historia.orden', 'asc')
            ->get();

        //estadísticas para el Dashboard
        $totalNiveles = NivelesHistoria::count();
        $nivelesCompletados = $progreso->where('completado', true)->count();
        
        //porcentaje
        $porcentajeCerrado = $totalNiveles > 0 ? round(($nivelesCompletados / $totalNiveles) * 100) : 0;
        
        // Titulo ultimo nivel
        $ultimoTitulo = $progreso->isNotEmpty() ? $progreso->last()->titulo : 'Inicio';

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'stats' => [
                'total_niveles' => $totalNiveles,
                'completados' => $nivelesCompletados,
                'porcentaje_progreso' => $porcentajeCerrado . '%',
                'titulo_ultimo_nivel' => $ultimoTitulo
            ],
            'progreso_detallado' => $progreso
        ], 200);
    }


    public function getPorcentajeUsuarioModoHistoria()
    {
        $idUsuario = Auth::id();

        //obtenemos el progreso con los datos del nivel
        $progreso = ProgresoHistoria::where('usuario_id', $idUsuario)->join('niveles_historia', 'usuario_progreso_historia.nivel_id', '=', 'niveles_historia.id')
            ->select(
                'usuario_progreso_historia.*', 
                'niveles_historia.titulo', 
                'niveles_historia.orden'
            )->orderBy('niveles_historia.orden', 'asc')->get();

        //estadísticas para el Dashboard
        $totalNiveles = NivelesHistoria::count();
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