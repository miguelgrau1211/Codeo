<?php

namespace App\Http\Controllers;

use App\Models\Mejoras;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Actions\Shop\GetShopUpgradesAction;

/**
 * Controlador para la gestión de Mejoras (Upgrades).
 */
class MejorasController extends Controller
{
    /**
     * Lista todas las mejoras disponibles.
     */
    public function index(Request $request, GetShopUpgradesAction $action): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        return response()->json($action->execute($locale));
    }

    /**
     * Muestra una mejora específica.
     */
    public function show($id): JsonResponse
    {
        return response()->json(Mejoras::findOrFail($id));
    }

    /**
     * Crea una nueva mejora (Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'tipo' => 'required|string',
            'precio_monedas' => 'required|integer',
        ]);

        $mejora = Mejoras::create($validatedData);
        return response()->json(['message' => 'Mejora creada', 'data' => $mejora], 201);
    }

    /**
     * Actualiza una mejora (Admin).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $mejora = Mejoras::findOrFail($id);
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'tipo' => 'required|string',
            'precio_monedas' => 'required|integer',
        ]);

        $mejora->update($validatedData);
        return response()->json(['message' => 'Mejora actualizada', 'data' => $mejora]);
    }

    /**
     * Elimina una mejora (Admin).
     */
    public function destroy($id): JsonResponse
    {
        Mejoras::destroy($id);
        return response()->json(['message' => 'Mejora eliminada']);
    }

    /**
     * Obtiene 3 mejoras aleatorias para la tienda interactiva.
     */
    public function getTresMejorasRandom(Request $request, GetShopUpgradesAction $action): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        return response()->json($action->execute($locale, true));
    }
}
