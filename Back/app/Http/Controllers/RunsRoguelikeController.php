<?php

namespace App\Http\Controllers;

use App\Models\RunsRoguelike;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador para la gestión del historial de partidas (Runs) de Roguelike.
 */
class RunsRoguelikeController extends Controller
{
    /**
     * Lista todas las partidas del usuario actual.
     */
    public function index(): JsonResponse
    {
        return response()->json(RunsRoguelike::where('usuario_id', Auth::id())->orderByDesc('created_at')->get());
    }

    /**
     * Muestra el detalle de una partida específica.
     */
    public function show($id): JsonResponse
    {
        return response()->json(RunsRoguelike::where('usuario_id', Auth::id())->findOrFail($id));
    }

    /**
     * Registra manualmente una nueva partida (Generalmente automatizado por Actions).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'vidas_restantes' => 'required|integer',
            'niveles_superados' => 'required|integer',
            'monedas_obtenidas' => 'required|integer',
            'estado' => 'required|string',
            'data_partida' => 'required|array',
        ]);

        $validatedData['usuario_id'] = Auth::id();

        $run = RunsRoguelike::create($validatedData);
        return response()->json(['message' => 'Run registrada', 'data' => $run], 201);
    }

    /**
     * Actualiza los datos de una partida.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $run = RunsRoguelike::where('usuario_id', Auth::id())->findOrFail($id);
        $validatedData = $request->validate([
            'vidas_restantes' => 'required|integer',
            'niveles_superados' => 'required|integer',
            'monedas_obtenidas' => 'required|integer',
            'estado' => 'required|string',
            'data_partida' => 'required|array',
        ]);

        $run->update($validatedData);
        return response()->json(['message' => 'Run actualizada', 'data' => $run]);
    }

    /**
     * Elimina una partida del historial.
     */
    public function destroy($id): JsonResponse
    {
        RunsRoguelike::where('usuario_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['message' => 'Run eliminada']);
    }

    /**
     * Obtiene el récord histórico del usuario (Mejor nivel y monedas).
     */
    public function getNivelMejorRunUsuario(): JsonResponse
    {
        $usuario = Auth::user();

        $mejorRun = RunsRoguelike::where('usuario_id', $usuario->id)
            ->orderByDesc('niveles_superados')
            ->orderByDesc('monedas_obtenidas')
            ->first();

        if (!$mejorRun) {
            return response()->json([
                'nickname' => $usuario->nickname,
                'tiene_record' => false,
                'mejor_nivel' => 0
            ]);
        }

        return response()->json([
            'nickname' => $usuario->nickname,
            'tiene_record' => true,
            'mejor_nivel' => $mejorRun->niveles_superados,
            'monedas' => $mejorRun->monedas_obtenidas,
            'fecha' => $mejorRun->created_at->diffForHumans()
        ]);
    }
}
