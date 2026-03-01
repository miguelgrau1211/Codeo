<?php

namespace App\Http\Controllers\Api;

use App\Actions\Achievements\CheckAchievementsAction;
use App\Actions\ComprarTemaAction;
use App\Actions\Roguelike\ProcessRoguelikeFailureAction;
use App\Actions\Roguelike\ProcessRoguelikeSuccessAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TemaResource;
use App\Models\Tema;
use App\Services\TranslationService;
use Illuminate\Http\Request;

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

        // Verificar si el usuario posee el tema (o si es un tema gratuito)
        if (!$usuario->temas()->where('tema_id', $tema->id)->exists() && $tema->precio > 0) {
            return response()->json(['message' => 'No posees este tema.'], 403);
        }

        $usuario->update(['tema_actual_id' => $tema->id]);

        $locale = $request->header('Accept-Language', 'es');
        $translatedTema = app(TranslationService::class)->translateTema($tema, $locale);

        $msg = 'Tema activado correctamente.';

        return response()->json(['message' => $msg, 'tema' => $translatedTema]);
    }
}
