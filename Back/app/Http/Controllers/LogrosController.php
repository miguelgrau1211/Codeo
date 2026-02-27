<?php

namespace App\Http\Controllers;

use App\Models\Logros;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para la gestión de definiciones de Logros.
 */
class LogrosController extends Controller
{
    /**
     * Lista todos los logros con traducción opcional.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = TranslationService::resolveLocale($request);
        $logros = Logros::all();

        $translated = app(TranslationService::class)->translateCollection($logros, $locale, 'logro');
        return response()->json($translated);
    }

    /**
     * Muestra un logro específico.
     */
    public function show($id, Request $request): JsonResponse
    {
        $logro = Logros::findOrFail($id);
        $locale = TranslationService::resolveLocale($request);

        $data = app(TranslationService::class)->translateLogro($logro, $locale);
        return response()->json($data);
    }

    /**
     * Crea un nuevo logro (Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icono_url' => 'required|string',
            'requisito_tipo' => 'required|string',
            'requisito_cantidad' => 'required|integer',
        ]);

        $logro = Logros::create($validatedData);
        return response()->json(['message' => 'Logro creado', 'data' => $logro], 201);
    }

    /**
     * Actualiza un logro existente (Admin).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $logro = Logros::findOrFail($id);
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icono_url' => 'required|string',
            'requisito_tipo' => 'required|string',
            'requisito_cantidad' => 'required|integer',
        ]);

        $logro->update($validatedData);
        return response()->json(['message' => 'Logro actualizado', 'data' => $logro]);
    }

    /**
     * Elimina un logro (Admin).
     */
    public function destroy($id): JsonResponse
    {
        Logros::destroy($id);
        return response()->json(['message' => 'Logro eliminado']);
    }
}
