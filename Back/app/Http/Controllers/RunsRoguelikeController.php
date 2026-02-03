<?php

namespace App\Http\Controllers;

use App\Models\RunsRoguelike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RunsRoguelikeController extends Controller
{
    public function index(){
        $runs = RunsRoguelike::where('usuario_id', Auth::id())->get();
        return response()->json($runs, 200);
    }

    public function show($id){
        $run = RunsRoguelike::findOrFail($id);
        return response()->json($run, 200);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'vidas_restantes' => 'required|integer',
            'niveles_superados' => 'required|integer',
            'monedas_obtenidas' => 'required|integer',
            'estado' => 'required|string',
            'data_partida' => 'required|array',
        ]);

        $validatedData['usuario_id'] = Auth::id();

        $run = RunsRoguelike::create($validatedData);

        return response()->json([
            'message' => 'Run creado exitosamente',
            'data' => $run
        ], 201);
    }

    public function update(Request $request, $id){
        $run = RunsRoguelike::where('usuario_id', Auth::id())->findOrFail($id);

        $validatedData = $request->validate([
            'vidas_restantes' => 'required|integer',
            'niveles_superados' => 'required|integer',
            'monedas_obtenidas' => 'required|integer',
            'estado' => 'required|string',
            'data_partida' => 'required|array',
        ]);

        $run->update($validatedData);

        return response()->json([
            'message' => 'Run actualizado exitosamente',
            'data' => $run
        ], 200);
    }

    public function destroy($id){
        $run = RunsRoguelike::findOrFail($id);
        $run->delete();

        return response()->json([
            'message' => 'Run eliminado exitosamente'
        ], 200);
    }
    
    /**
     * Obtiene el récord histórico del usuario en Roguelike
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNivelMejorRunUsuario()
    {
        $idUsuario = Auth::id();
        $usuario = Auth::user();

        //mejor partida
        $mejorRun = RunsRoguelike::where('usuario_id', $idUsuario)
            ->orderByDesc('niveles_superados')->orderByDesc('monedas_obtenidas') // Desempate por monedas
            ->first();

        if (!$mejorRun) {
            return response()->json([
                'nickname' => $usuario->nickname,
                'tiene_record' => false,
                'mejor_nivel' => 0
            ], 200);
        }

        return response()->json([
            'nickname' => $usuario->nickname,
            'tiene_record' => true,
            'mejor_nivel' => $mejorRun->niveles_superados,
            'monedas' => $mejorRun->monedas_obtenidas,
            'fecha' => $mejorRun->created_at->diffForHumans()
        ], 200);
    }
}
