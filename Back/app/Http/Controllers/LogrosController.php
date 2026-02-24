<?php

namespace App\Http\Controllers;

use App\Models\Logros;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class LogrosController extends Controller
{
    /**
     * Returns all achievements, with nombre/descripcion translated
     * into the language requested via the Accept-Language header.
     */
    public function index(Request $request)
    {
        $locale = TranslationService::resolveLocale($request);
        $translator = app(TranslationService::class);

        $logros = Logros::all();
        $translated = $translator->translateCollection($logros, $locale, 'logro');

        return response()->json($translated, 200);
    }

    public function show($id, Request $request)
    {
        $locale = TranslationService::resolveLocale($request);
        $logro = Logros::findOrFail($id);
        $translator = app(TranslationService::class);
        $data = $translator->translateLogro($logro, $locale);

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icono_url' => 'required|string',
            'requisito_tipo' => 'required|string',
            'requisito_cantidad' => 'required|integer',
        ]);

        $logro = Logros::create($validatedData);

        return response()->json([
            'message' => 'Logro creado exitosamente',
            'data' => $logro,
        ], 201);
    }

    public function update(Request $request, $id)
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

        return response()->json([
            'message' => 'Logro actualizado exitosamente',
            'data' => $logro,
        ], 200);
    }

    public function destroy($id)
    {
        $logro = Logros::findOrFail($id);
        $logro->delete();

        return response()->json([
            'message' => 'Logro eliminado exitosamente',
        ], 200);
    }
}
