<?php

namespace App\Actions\Story;

use App\Models\NivelesHistoria;
use App\Models\NivelHistoriaDesactivado;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Acción para desactivar un nivel de historia.
 * Mueve el nivel a la tabla de desactivados y lo borra de la principal.
 */
class DisableStoryLevelAction
{
    /**
     * Ejecuta la desactivación del nivel.
     * 
     * @param NivelesHistoria $nivel
     * @param string $motivo
     * @return bool
     */
    public function execute(NivelesHistoria $nivel, string $motivo): bool
    {
        return DB::transaction(function () use ($nivel, $motivo) {
            // Guardamos una copia en la tabla de desactivados para no perder los datos
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
                'motivo' => $motivo,
                'fecha_desactivacion' => now()
            ]);

            // Eliminamos de la tabla principal
            $deleted = $nivel->delete();

            // Registramos la acción en el log de administración
            AdminLog::create([
                'user_id' => Auth::id(),
                'action' => 'DISABLE_LEVEL_STORY',
                'details' => "Desactivó nivel historia: {$nivel->titulo} (ID: {$nivel->id})",
            ]);

            return $deleted;
        });
    }
}
