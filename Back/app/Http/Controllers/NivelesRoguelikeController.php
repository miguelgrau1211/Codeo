<?php

namespace App\Http\Controllers;

use App\Models\NivelRoguelike;
use App\Models\NivelRoguelikeDesactivado;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\RoguelikeSessionController;

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
        $userId = Auth::id();
        $cacheKey = RoguelikeSessionController::getCacheKey($userId);
        $session = Cache::get($cacheKey);

        // 1. Si hay sesión activa, priorizar la persistencia (anti-cheat y no repetición)
        if ($session) {
            // A) Si ya hay un nivel asignado (ej: recarga de página), devolver el mismo
            if (!empty($session['current_level_id'])) {
                $nivel = NivelRoguelike::find($session['current_level_id']);
                if ($nivel) {
                    return response()->json($nivel, 200);
                }
            }

            // B) Si no hay nivel asignado, seleccionar uno nuevo excluyendo los usados
            $nivelesCompletados = $session['levels_completed'] ?? 0;
            $usados = $session['used_level_ids'] ?? [];

            // Lógica de dificultad basada en progreso de la sesión
            if ($nivelesCompletados < 4) {
                $p_facil = 80; $p_medio = 20; $p_dificil = 0;
            } elseif ($nivelesCompletados < 8) {
                $p_facil = 40; $p_medio = 50; $p_dificil = 10;
            } else {
                $p_facil = 20; $p_medio = 50; $p_dificil = 30;
            }

            $rand = rand(1, 100);
            if ($rand <= $p_facil) $dificultad = 'fácil';
            elseif ($rand <= ($p_facil + $p_medio)) $dificultad = 'medio';
            else $dificultad = 'difícil';

            // Buscar nivel no usado
            $nivel = null;
            try {
                $nivel = NivelRoguelike::where('dificultad', $dificultad)
                    ->whereNotIn('id', $usados)
                    ->inRandomOrder()
                    ->first();
            } catch (\Throwable $e) {
                // If DB fails, $nivel remains null, triggering fallback below
            }

            // Fallbacks si se agotan los niveles de esa dificultad
            if (!$nivel) {
                $nivel = NivelRoguelike::whereNotIn('id', $usados)
                    ->inRandomOrder()
                    ->first();
            }

            // Último recurso: si ha jugado TODOS, permitir repetir (o mostrar fin de juego, pero por ahora repetir)
            if (!$nivel) {
                 $nivel = NivelRoguelike::inRandomOrder()->first();
            }

            if ($nivel) {
                // Guardar en sesión que este es el nivel actual
                $session['current_level_id'] = $nivel->id;
                Cache::put($cacheKey, $session, 7200); // 2h TTL (mismo que controller)
                return response()->json($nivel, 200);
            }

             return response()->json([
                'id' => 9999,
                'titulo' => 'Nivel de Prueba (Base de Datos Vacía)',
                'dificultad' => 'fácil',
                'descripcion' => 'No se encontraron niveles en la base de datos.',
                'test_cases' => [
                    ['input' => '"test"', 'output' => '"test"']
                ],
                'recompensa_monedas' => 10
             ], 200);
             // return response()->json(['message' => 'No hay niveles disponibles'], 404);
        }

        // 2. Fallback sin sesión (comportamiento antiguo, solo aleatorio)
        $nivelesCompletados = $request->query('niveles_completados', 0);
        
        if ($nivelesCompletados < 4) {
            $p_facil = 80; $p_medio = 20; $p_dificil = 0;
        } elseif ($nivelesCompletados < 8) {
            $p_facil = 40; $p_medio = 50; $p_dificil = 10;
        } else {
            $p_facil = 20; $p_medio = 50; $p_dificil = 30;
        }

        $rand = rand(1, 100);
        $dificultadSeleccionada = ($rand <= $p_facil) ? 'fácil' : (($rand <= $p_facil + $p_medio) ? 'medio' : 'difícil');

        $nivel = NivelRoguelike::where('dificultad', $dificultadSeleccionada)->inRandomOrder()->first();

        if (!$nivel) {
            $nivel = NivelRoguelike::inRandomOrder()->first();
        }

        if (!$nivel) {
            // Fallback: If DB is empty, return a dummy level for testing
            return response()->json([
                'id' => 9999,
                'titulo' => 'Nivel de Prueba (Base de Datos Vacía)',
                'descripcion' => 'No se encontraron niveles en la base de datos. Este es un nivel de prueba.',
                'dificultad' => 'fácil',
                'test_cases' => [
                     ['input' => '"test"', 'output' => '"test"']
                ],
                'recompensa_monedas' => 10
            ], 200);
            // return response()->json(['message' => 'No hay niveles disponibles'], 404);
        }

        return response()->json($nivel, 200);
    }
}