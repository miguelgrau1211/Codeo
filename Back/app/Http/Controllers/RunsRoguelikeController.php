<?php

namespace App\Http\Controllers;

use App\Models\RunsRoguelike;
use Illuminate\Http\Request;

class RunsRoguelikeController
{
    public function index(){
        $runs = RunsRoguelike::all();
        return response()->json($runs, 200);
    }

    public function show($id){
        $run = RunsRoguelike::findOrFail($id);
        return response()->json($run, 200);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'vidas_restantes' => 'required|integer',
            'niveles_superados' => 'required|integer',
            'monedas_obtenidas' => 'required|integer',
            'estado' => 'required|string',
            'data_partida' => 'required|array',
        ]);

        $run = RunsRoguelike::create($validatedData);

        return response()->json([
            'message' => 'Run creado exitosamente',
            'data' => $run
        ], 201);
    }

    public function update(Request $request, $id){
        $run = RunsRoguelike::findOrFail($id);

        $validatedData = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
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
}
