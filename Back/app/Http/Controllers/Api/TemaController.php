<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tema;
use App\Http\Resources\TemaResource;
use App\Actions\ComprarTemaAction;

class TemaController extends Controller
{
    public function index(Request $request)
    {
        $temas = Tema::all();
        $locale = $request->header('Accept-Language', 'es');
        $translated = app(\App\Services\TranslationService::class)->translateCollection($temas, $locale, 'tema');
        return response()->json(['data' => $translated]);
    }

    public function misTemas(Request $request)
    {
        $usuario = $request->user();
        return TemaResource::collection($usuario->temas);
    }

    public function comprar(Request $request, Tema $tema, ComprarTemaAction $comprarTemaAction)
    {
        if ($tema->es_exclusivo) {
            return response()->json(['message' => 'Este tema es exclusivo del Pase de Batalla.'], 403);
        }

        try {
            $comprarTemaAction->execute($request->user(), $tema);
            return response()->json(['message' => 'Tema comprado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function activar(Request $request, Tema $tema)
    {
        $usuario = $request->user();

        // Check if user owns the theme (or if it's a default/free theme if we have any)
        if (!$usuario->temas()->where('tema_id', $tema->id)->exists() && $tema->precio > 0) {
            return response()->json(['message' => 'No posees este tema.'], 403);
        }

        $usuario->update(['tema_actual_id' => $tema->id]);

        $locale = $request->header('Accept-Language', 'es');
        $translatedTema = app(\App\Services\TranslationService::class)->translateTema($tema, $locale);

        $msg = ($locale === 'en') ? 'Theme activated correctly.' : 'Tema activado correctamente.';
        // For other languages we could use __() if we had lang files in the backend for these strings.
        // But the user specifically wants EVERYTHING translated.

        return response()->json(['message' => $msg, 'tema' => $translatedTema]);
    }
}
