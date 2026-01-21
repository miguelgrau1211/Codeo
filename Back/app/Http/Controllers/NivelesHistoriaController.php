<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use Illuminate\Http\Request;

class NivelesHistoriaController
{
    public function index(){
        $niveles = NivelesHistoria::all();
        return response()->json($niveles, 200);
    }

    public function show($id){
        $nivel = NivelesHistoria::findOrFail($id);
        return response()->json($nivel, 200);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'orden' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'contenido_teorico' => 'required|string',
            'codigo_inicial' => 'required|string',
            'solucion_esperada' => 'required|string',
            'recompensa_exp' => 'required|integer',
            'recompensa_monedas' => 'required|integer',
        ]);

        $nivel = NivelesHistoria::create($validatedData);

        return response()->json([
            'message' => 'Nivel creado exitosamente',
            'data' => $nivel
        ], 201);
    }

    public function update(Request $request, $id){
        $nivel = NivelesHistoria::findOrFail($id);

        $validatedData = $request->validate([
            'orden' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'contenido_teorico' => 'required|string',
            'codigo_inicial' => 'required|string',
            'solucion_esperada' => 'required|string',
            'recompensa_exp' => 'required|integer',
            'recompensa_monedas' => 'required|integer',
        ]);

        $nivel->update($validatedData);

        return response()->json([
            'message' => 'Nivel actualizado exitosamente',
            'data' => $nivel
        ], 200);
    }

    public function destroy($id){
        $nivel = NivelesHistoria::findOrFail($id);
        $nivel->delete();

        return response()->json([
            'message' => 'Nivel eliminado exitosamente'
        ], 200);
    }

}
