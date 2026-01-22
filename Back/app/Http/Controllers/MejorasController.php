<?php

namespace App\Http\Controllers;

use App\Models\Mejoras;
use Illuminate\Http\Request;

class MejorasController
{
    public function index(){
        $mejoras = Mejoras::all();
        return response()->json($mejoras, 200);
    }

    public function show($id){
        $mejora = Mejoras::findOrFail($id);
        return response()->json($mejora, 200);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'tipo' => 'required|string',
            'precio_monedas' => 'required|integer',
        ]);

        $mejora = Mejoras::create($validatedData);

        return response()->json([
            'message' => 'Mejora creada exitosamente',
            'data' => $mejora
        ], 201);
    }

    public function update(Request $request, $id){
        $mejora = Mejoras::findOrFail($id);

        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'tipo' => 'required|string',
            'precio_monedas' => 'required|integer',
        ]);

        $mejora->update($validatedData);

        return response()->json([
            'message' => 'Mejora actualizada exitosamente',
            'data' => $mejora
        ], 200);
    }

    public function destroy($id){
        $mejora = Mejoras::findOrFail($id);
        $mejora->delete();

        return response()->json([
            'message' => 'Mejora eliminada exitosamente'
        ], 200);
    }

    public function getTresMejorasRandom(){
        $mejoras = Mejoras::inRandomOrder()->take(3)->get();
        return response()->json($mejoras, 200);
    }
}
