<?php

namespace App\Http\Controllers;

use App\Models\NivelRoguelike;
use App\Models\NivelRoguelikeDesactivado;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NivelesRoguelikeController extends Controller
{
    // todos los niveles roguelike
    public function index()
    {
        // Solo mostraremos lo necesario
        $niveles = NivelRoguelike::select('id', 'dificultad', 'titulo', 'recompensa_monedas')->orderBy('id')->get();
        return response()->json($niveles, 200);
    }

    public function indexAdmin()
    {
        // Paginación para admin
        $niveles = NivelRoguelike::select('id', 'dificultad', 'titulo', 'recompensa_monedas')->orderBy('id')->paginate(10);
        return response()->json($niveles, 200);
    }

    public function show($id)
    {
        $nivel = NivelRoguelike::findOrFail($id);
        return response()->json($nivel, 200);
    }


    // Crea un nuevo desafío roguelike
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dificultad' => 'required|in:fácil,medio,difícil,extremo',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'test_cases' => 'nullable|array',
            'recompensa_monedas' => 'required|integer|min:0',
        ]);

        $nivel = NivelRoguelike::create($validatedData);

        return response()->json([
            'message' => 'Desafío Roguelike creado con éxito',
            'data' => $nivel
        ], 201);
    }

    // Actualiza un desafío roguelike
    public function update(Request $request, $id)
    {
        $nivel = NivelRoguelike::findOrFail($id);

        $validatedData = $request->validate([
            'dificultad' => 'required|in:fácil,medio,difícil,extremo',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'test_cases' => 'nullable|array',
            'recompensa_monedas' => 'required|integer|min:0',
        ]);

        // Removed manual validation for test_cases structure to be more flexible
        // if (isset($request->test_cases)) { ... }

        $nivel->update($validatedData);

        return response()->json([
            'message' => 'Desafío Roguelike actualizado correctamente',
            'data' => $nivel
        ], 200);
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

    public function getNivelModoInfinito(Request $request)
    {
        $nivelesCompletados = $request->query('niveles_completados', 0);
        
        // Determinar probabilidades según niveles completados
        if ($nivelesCompletados < 4) {
            // Inicio: Mayoría fácil
            $probs = ['fácil' => 60, 'medio' => 20, 'difícil' => 0]; // (El resto 20% podría ser error o fallback) -> Ajustemos a 100%
            // 80% Facil, 20% Medio, 0% Dificil
            $p_facil = 80; 
            $p_medio = 20;
            $p_dificil = 0;
        } elseif ($nivelesCompletados < 8) {
            // Medio juego
            $p_facil = 40;
            $p_medio = 50;
            $p_dificil = 10;
        } else {
            // Juego avanzado
            $p_facil = 20;
            $p_medio = 50;
            $p_dificil = 30;
        }

        // Selección aleatoria ponderada
        $rand = rand(1, 100);
        
        if ($rand <= $p_facil) {
            $dificultadSeleccionada = 'fácil';
        } elseif ($rand <= ($p_facil + $p_medio)) {
            $dificultadSeleccionada = 'medio';
        } else {
            $dificultadSeleccionada = 'difícil';
        }

        // Buscar nivel de esa dificultad
        $nivel = NivelRoguelike::where('dificultad', $dificultadSeleccionada)
            ->inRandomOrder()
            ->first();

        // Fallback: Si no hay niveles de esa dificultad (ej. no hemos creado difíciles aun), buscar cualquiera
        if (!$nivel) {
            $nivel = NivelRoguelike::inRandomOrder()->first();
        }

        if (!$nivel) {
            return response()->json(['message' => 'No hay niveles disponibles'], 404);
        }

        return response()->json($nivel, 200);
    }
}