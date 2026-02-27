<?php

namespace App\Actions\Story;

use App\Models\NivelesHistoria;
use App\Models\NivelHistoriaDesactivado;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Acción para reactivar un nivel de historia que estaba desactivado.
 * Mueve el nivel de regreso a la tabla principal garantizando la integridad de IDs.
 */
class EnableStoryLevelAction
{
    /**
     * Ejecuta la reactivación del nivel.
     * 
     * @param int $id ID del registro desactivado u original.
     * @return NivelesHistoria
     * @throws \Exception
     */
    public function execute(int $id): NivelesHistoria
    {
        // Buscamos en la tabla de desactivados.
        // El frontend puede enviarnos tanto el ID de la tabla de desactivados como el ID original.
        $desactivado = NivelHistoriaDesactivado::where('nivel_id_original', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // Verificamos si ya hay un nivel activo con el mismo orden para evitar conflictos visuales.
        if (NivelesHistoria::where('orden', $desactivado->orden)->exists()) {
            throw new \Exception('No se puede activar: Ya existe un nivel activo con el orden ' . $desactivado->orden);
        }

        return DB::transaction(function () use ($desactivado) {
            // Restauramos los datos del nivel forzando el ID original para no romper el progreso de los usuarios.
            $nuevoNivel = NivelesHistoria::create([
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

            // Una vez restaurado, borramos el registro de la tabla de "papelera".
            $desactivado->delete();

            // Guardamos el registro de administración.
            AdminLog::create([
                'user_id' => Auth::id(),
                'action' => 'ENABLE_LEVEL_STORY',
                'details' => "Reactivó nivel historia: {$nuevoNivel->titulo} (ID: {$nuevoNivel->id})",
            ]);

            return $nuevoNivel;
        });
    }
}
