<?php

namespace App\Http\Controllers;

use App\Models\NivelRoguelike;
use App\Models\NivelRoguelikeDesactivado;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Actions\RoguelikeLevels\GetNextRoguelikeLevelAction;
use App\Actions\RoguelikeLevels\ToggleRoguelikeLevelAction;

/**
 * Controlador de Niveles Roguelike.
 * Gestiona el catálogo de desafíos para el modo infinito y herramientas de administración.
 */
class NivelesRoguelikeController extends Controller
{
    /**
     * Lista básica de niveles traducidos para el usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        $niveles = NivelRoguelike::select('id', 'dificultad', 'titulo', 'recompensa_monedas')->orderBy('id')->get();

        $translated = app(TranslationService::class)->translateCollection($niveles, $locale, 'nivel');
        return response()->json($translated);
    }

    /**
     * Lista extendida para el panel de administración (sin traducción).
     */
    public function indexAdmin(): JsonResponse
    {
        return response()->json(NivelRoguelike::orderBy('id')->paginate(10));
    }

    /**
     * Muestra el detalle de un nivel.
     */
    public function show($id, Request $request): JsonResponse
    {
        $nivel = NivelRoguelike::findOrFail($id);

        if ($request->is('api/admin/*')) {
            return response()->json($nivel);
        }

        $locale = TranslationService::resolveLocale($request);
        $data = app(TranslationService::class)->translateNivel($nivel, $locale);
        return response()->json($data);
    }

    /**
     * Crea un nuevo desafío (Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'dificultad' => 'required|in:fácil,medio,difícil,extremo',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'test_cases' => 'nullable|array',
            'recompensa_monedas' => 'required|integer|min:0',
        ]);

        $nivel = NivelRoguelike::create($validatedData);
        return response()->json(['message' => 'Desafío creado', 'data' => $nivel], 201);
    }

    /**
     * Actualiza un desafío existente (Admin).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $nivel = NivelRoguelike::findOrFail($id);
        $validatedData = $request->validate([
            'dificultad' => 'required|in:fácil,medio,difícil,extremo',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'test_cases' => 'nullable|array',
            'recompensa_monedas' => 'required|integer|min:0',
        ]);

        $nivel->update($validatedData);
        return response()->json(['message' => 'Desafío actualizado', 'data' => $nivel]);
    }

    /**
     * Obtiene el siguiente nivel para el modo infinito basado en la sesión.
     */
    public function getNivelModoInfinito(Request $request, GetNextRoguelikeLevelAction $action): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        $result = $action->execute(Auth::id(), $locale);

        return response()->json($result['nivel']);
    }

    /**
     * Activa o desactiva un nivel (Admin).
     */
    public function toggleStatus(Request $request, $id, ToggleRoguelikeLevelAction $action): JsonResponse
    {
        try {
            $result = $action->execute($id, $request->input('motivo'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Lista de niveles desactivados (Admin).
     */
    public function desactivados(): JsonResponse
    {
        return response()->json(NivelRoguelikeDesactivado::orderBy('fecha_desactivacion', 'desc')->get());
    }

    /**
     * Eliminación física de un nivel (Admin).
     */
    public function destroy($id): JsonResponse
    {
        NivelRoguelike::destroy($id);
        return response()->json(['message' => 'Nivel eliminado permanentemente']);
    }
}