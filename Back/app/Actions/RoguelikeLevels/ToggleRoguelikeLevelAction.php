<?php

namespace App\Actions\RoguelikeLevels;

use App\Models\NivelRoguelike;
use App\Models\NivelRoguelikeDesactivado;
use App\Models\AdminLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Acción para alternar el estado (activo/desactivado) de un nivel de Roguelike.
 */
class ToggleRoguelikeLevelAction
{
    /**
     * @param int $id ID del nivel (en cualquiera de las dos tablas).
     * @param string|null $motivo Motivo opcional para la desactivación.
     * @return array Mensaje de éxito.
     */
    public function execute(int $id, ?string $motivo = null): array
    {
        return DB::transaction(function () use ($id, $motivo) {
            // 1. Intentar desactivar (mover de Activos a Desactivados)
            $nivel = NivelRoguelike::find($id);

            if ($nivel) {
                NivelRoguelikeDesactivado::create([
                    'nivel_id_original' => $nivel->id,
                    'dificultad' => $nivel->dificultad,
                    'titulo' => $nivel->titulo,
                    'descripcion' => $nivel->descripcion,
                    'test_cases' => $nivel->test_cases,
                    'recompensa_monedas' => $nivel->recompensa_monedas,
                    'motivo' => $motivo ?? 'Desactivado por administrador',
                    'fecha_desactivacion' => now()
                ]);

                $nivel->delete();

                $this->logAction('DISABLE_LEVEL_ROGUELIKE', "Desactivó nivel roguelike: {$nivel->titulo} (ID: {$nivel->id})");

                return ['message' => 'Nivel desactivado correctamente'];
            }

            // 2. Intentar activar (mover de Desactivados a Activos)
            $desactivado = NivelRoguelikeDesactivado::where('nivel_id_original', $id)
                ->orWhere('id', $id)
                ->first();

            if ($desactivado) {
                $nuevoNivel = NivelRoguelike::create([
                    'id' => $desactivado->nivel_id_original,
                    'dificultad' => $desactivado->dificultad,
                    'titulo' => $desactivado->titulo,
                    'descripcion' => $desactivado->descripcion,
                    'test_cases' => $desactivado->test_cases,
                    'recompensa_monedas' => $desactivado->recompensa_monedas,
                ]);

                $desactivado->delete();

                $this->logAction('ENABLE_LEVEL_ROGUELIKE', "Reactivó nivel roguelike: {$nuevoNivel->titulo} (ID: {$nuevoNivel->id})");

                return ['message' => 'Nivel reactivado correctamente'];
            }

            throw new \Exception('Nivel no encontrado en ninguna categoría.');
        });
    }

    private function logAction(string $action, string $details): void
    {
        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
