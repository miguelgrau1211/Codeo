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

    /**
     * Devuelve los niveles roguelike desactivados.
     */
    public function desactivados()
    {
        $niveles = NivelRoguelikeDesactivado::orderBy('fecha_desactivacion', 'desc')->get();
        return response()->json($niveles, 200);
    }

    /**
     * Alterna el estado de un nivel Roguelike (Activar/Desactivar).
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            // 1. Intentar desactivar
            $nivel = NivelRoguelike::find($id);

            if ($nivel) {
                return DB::transaction(function () use ($nivel, $request) {
                    NivelRoguelikeDesactivado::create([
                        'nivel_id_original' => $nivel->id,
                        'dificultad' => $nivel->dificultad,
                        'titulo' => $nivel->titulo,
                        'descripcion' => $nivel->descripcion,
                        'test_cases' => $nivel->test_cases,
                        'recompensa_monedas' => $nivel->recompensa_monedas,
                        'motivo' => $request->input('motivo', 'Desactivado por administrador'),
                        'fecha_desactivacion' => now()
                    ]);

                    $nivel->delete();

                    AdminLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'DISABLE_LEVEL_ROGUELIKE',
                        'details' => "Desactivó nivel roguelike: {$nivel->titulo} (ID: {$nivel->id})",
                    ]);

                    return response()->json(['message' => 'Nivel roguelike desactivado correctamente'], 200);
                });
            }

            // 2. Intentar activar
            $desactivado = NivelRoguelikeDesactivado::where('nivel_id_original', $id)
                ->orWhere('id', $id)
                ->first();

            if ($desactivado) {
                return DB::transaction(function () use ($desactivado) {

                    $nuevoNivel = NivelRoguelike::create([
                        'id' => $desactivado->nivel_id_original, // Mantener ID original para consistencia
                        'dificultad' => $desactivado->dificultad,
                        'titulo' => $desactivado->titulo,
                        'descripcion' => $desactivado->descripcion,
                        'test_cases' => $desactivado->test_cases,
                        'recompensa_monedas' => $desactivado->recompensa_monedas,
                    ]);

                    $desactivado->delete();

                    AdminLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'ENABLE_LEVEL_ROGUELIKE',
                        'details' => "Reactivó nivel roguelike: {$nuevoNivel->titulo} (ID: {$nuevoNivel->id})",
                    ]);

                    return response()->json(['message' => 'Nivel roguelike reactivado correctamente'], 200);
                });
            }

            return response()->json(['message' => 'Nivel no encontrado'], 404);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
        }
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