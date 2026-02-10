<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelHistoriaDesactivado;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NivelesHistoriaController
{
    public function index()
    {
        // Solo mostraremos lo necesario
        $niveles = NivelesHistoria::select('id', 'orden', 'titulo', 'recompensa_exp', 'recompensa_monedas')->orderBy('orden')->get();
        return response()->json($niveles, 200);
    }

    public function indexAdmin()
    {
        // Paginación para admin
        $niveles = NivelesHistoria::select('id', 'orden', 'titulo', 'recompensa_exp', 'recompensa_monedas')->orderBy('orden')->paginate(10);
        return response()->json($niveles, 200);
    }

    public function show($id)
    {
        $nivel = NivelesHistoria::findOrFail($id);
        return response()->json($nivel, 200);
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
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            // 1. Intentar desactivar (si está en la tabla principal)
            $nivel = NivelesHistoria::find($id);

            if ($nivel) {
                return DB::transaction(function () use ($nivel, $request) {
                    NivelHistoriaDesactivado::create([
                        'nivel_id_original' => $nivel->id,
                        'orden' => $nivel->orden,
                        'titulo' => $nivel->titulo,
                        'descripcion' => $nivel->descripcion,
                        'contenido_teorico' => $nivel->contenido_teorico,
                        'codigo_inicial' => $nivel->codigo_inicial,
                        'test_cases' => $nivel->test_cases,
                        'recompensa_exp' => $nivel->recompensa_exp,
                        'recompensa_monedas' => $nivel->recompensa_monedas,
                        'motivo' => $request->input('motivo', 'Desactivado por administrador'),
                        'fecha_desactivacion' => now()
                    ]);

                    $nivel->delete();

                    AdminLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'DISABLE_LEVEL_STORY',
                        'details' => "Desactivó nivel historia: {$nivel->titulo} (ID: {$nivel->id})",
                    ]);

                    return response()->json(['message' => 'Nivel desactivado correctamente'], 200);
                });
            }

            // 2. Intentar activar: Resolver ambigüedad del ID.
            // Como el frontend puede enviar el ID de la tabla actual o el original,
            // buscamos en 'NivelHistoriaDesactivado' coincidencias tanto por 'nivel_id_original' como por su PK.

            $desactivado = NivelHistoriaDesactivado::where('nivel_id_original', $id)
                ->orWhere('id', $id)
                ->first();

            if ($desactivado) {
                return DB::transaction(function () use ($desactivado) {

                    // Verificar colisión de orden
                    if (NivelesHistoria::where('orden', $desactivado->orden)->exists()) {
                        return response()->json(['message' => 'No se puede activar: Ya existe un nivel activo con el orden ' . $desactivado->orden], 409);
                    }

                    $nuevoNivel = NivelesHistoria::create([
                        // Restauramos el nivel forzando el uso de su ID original.
                        // Esto es para mantener la integridad con 'usuario_progreso_historia' y no romper el progreso de los usuarios.
                        'id' => $desactivado->nivel_id_original,
                        'orden' => $desactivado->orden,
                        'titulo' => $desactivado->titulo,
                        'descripcion' => $desactivado->descripcion,
                        'contenido_teorico' => $desactivado->contenido_teorico,
                        'codigo_inicial' => $desactivado->codigo_inicial,
                        'test_cases' => $desactivado->test_cases,
                        'recompensa_exp' => $desactivado->recompensa_exp,
                        'recompensa_monedas' => $desactivado->recompensa_monedas,
                    ]);

                    $desactivado->delete();

                    AdminLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'ENABLE_LEVEL_STORY',
                        'details' => "Reactivó nivel historia: {$nuevoNivel->titulo} (ID: {$nuevoNivel->id})",
                    ]);

                    return response()->json(['message' => 'Nivel reactivado correctamente'], 200);
                });
            }

            return response()->json(['message' => 'Nivel no encontrado'], 404);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
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
