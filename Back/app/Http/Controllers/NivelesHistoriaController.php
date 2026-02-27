<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelHistoriaDesactivado;
use App\Models\AdminLog;
use App\Services\TranslationService;
use App\Actions\Story\DisableStoryLevelAction;
use App\Actions\Story\EnableStoryLevelAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NivelesHistoriaController
{
    public function index(Request $request)
    {
        $locale = TranslationService::resolveLocale($request);
        $translator = app(TranslationService::class);

        $niveles = NivelesHistoria::select('id', 'orden', 'titulo', 'recompensa_exp', 'recompensa_monedas')
            ->orderBy('orden')
            ->get();

        $translated = $translator->translateCollection($niveles, $locale, 'nivel');

        return response()->json($translated, 200);
    }

    public function indexAdmin(Request $request)
    {
        // Paginación para admin - No traducimos porque admin edita el original (ES)
        $niveles = NivelesHistoria::select('id', 'orden', 'titulo', 'recompensa_exp', 'recompensa_monedas')
            ->orderBy('orden')
            ->paginate(10);
        return response()->json($niveles, 200);
    }

    public function show($id, Request $request)
    {
        $nivel = NivelesHistoria::findOrFail($id);

        // Si es una ruta de administración, devolvemos el contenido original sin traducir
        if ($request->is('api/admin/*')) {
            return response()->json($nivel, 200);
        }

        $locale = TranslationService::resolveLocale($request);
        $translator = app(TranslationService::class);
        $data = $translator->translateNivel($nivel, $locale);
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'orden' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'contenido_teorico' => 'required|string',
            'codigo_inicial' => 'required|string',
            'test_cases' => 'nullable|array',
            'recompensa_exp' => 'required|integer',
            'recompensa_monedas' => 'required|integer',
        ]);

        $nivel = NivelesHistoria::create($validatedData);

        return response()->json([
            'message' => 'Nivel creado exitosamente',
            'data' => $nivel
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $nivel = NivelesHistoria::findOrFail($id);

        $validatedData = $request->validate([
            'orden' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'contenido_teorico' => 'required|string',
            'codigo_inicial' => 'required|string',
            'test_cases' => 'nullable|array',
            'recompensa_exp' => 'required|integer',
            'recompensa_monedas' => 'required|integer',
        ]);

        $nivel->update($validatedData);

        return response()->json([
            'message' => 'Nivel actualizado exitosamente',
            'data' => $nivel
        ], 200);
    }

    /**
     * Devuelve los niveles desactivados.
     */
    public function desactivados()
    {
        $niveles = NivelHistoriaDesactivado::orderBy('fecha_desactivacion', 'desc')->get();
        return response()->json($niveles, 200);
    }

    /**
     * Alterna el estado de un nivel (Activar/Desactivar).
     * Delegamos la lógica pesada a clases Action para que el controlador sea delgado.
     */
    public function toggleStatus(Request $request, $id, DisableStoryLevelAction $disableAction, EnableStoryLevelAction $enableAction)
    {
        // 1. Intentamos buscar el nivel en la tabla de niveles activos
        $nivel = NivelesHistoria::find($id);

        if ($nivel) {
            // Si existe, lo desactivamos
            $motivo = $request->input('motivo', 'Desactivado por administrador');
            $disableAction->execute($nivel, $motivo);
            return response()->json(['message' => 'Nivel desactivado correctamente'], 200);
        }

        // 2. Si no estaba activo, intentamos reactivarlo desde la tabla de desactivados
        try {
            $enableAction->execute($id);
            return response()->json(['message' => 'Nivel reactivado correctamente'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Nivel no encontrado en ninguna tabla'], 404);
        } catch (\Exception $e) {
            // Manejamos errores de colisión de orden u otros problemas de negocio
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function destroy($id)
    {
        $nivel = NivelesHistoria::findOrFail($id);
        $nivel->delete();

        return response()->json([
            'message' => 'Nivel eliminado exitosamente'
        ], 200);
    }

}
