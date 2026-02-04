<?php

namespace App\Http\Controllers;

use App\Models\NivelRoguelike;
use Illuminate\Http\Request;

class NivelesRoguelikeController extends Controller
{
    // todos los niveles roguelike
    public function index()
    {
        $niveles = NivelRoguelike::all();
        return response()->json($niveles, 200);
    }


    // Crea un nuevo desafío roguelike
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dificultad'         => 'required|in:fácil,medio,difícil,extremo',
            'titulo'             => 'required|string|max:255',
            'descripcion'        => 'required|string',
            'test_cases'   => 'required|array',
            'test_cases.*.input' => 'required|string',
            'test_cases.*.output' => 'required|string',
            'recompensa_monedas' => 'required|integer|min:0',
        ]);

        $nivel = NivelRoguelike::create($validatedData);

        return response()->json([
            'message' => 'Desafío Roguelike creado con éxito',
            'data' => $nivel
        ], 201);
    }


    // Obtener un nivel aleatorio según dificultad.
    public function obtenerAleatorio($dificultad)
    {
        $nivel = NivelRoguelike::where('dificultad', $dificultad)
            ->inRandomOrder()
            ->first();

        if (!$nivel) {
            return response()->json(['message' => 'No hay niveles para esa dificultad'], 404);
        }

        return response()->json($nivel, 200);
    }

    //Eliminar un nivel
    public function destroy($id)
    {
        NivelRoguelike::destroy($id);
        return response()->json(['message' => 'Nivel eliminado'], 200);
    }

    public function getNivelModoInfinito($dificultad)
    {
        $nivel = NivelRoguelike::where('dificultad', $dificultad)
            ->inRandomOrder()
            ->first();

        if (!$nivel) {
            return response()->json(['message' => 'No hay niveles para esa dificultad'], 404);
        }

        return response()->json($nivel, 200);
    }
}