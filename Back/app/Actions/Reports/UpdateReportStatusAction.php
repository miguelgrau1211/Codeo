<?php

namespace App\Actions\Reports;

use App\Models\Reporte;
use App\Actions\ProcessLevelUpAction;

/**
 * Acción para actualizar el estado de un reporte y gestionar consecuencias (XP/Sanciones).
 */
class UpdateReportStatusAction
{
    /**
     * @param int $reportId
     * @param array $data ['estado', 'prioridad']
     * @return array ['reporte' => Reporte, 'mensaje' => string]
     */
    public function execute(int $reportId, array $data): array
    {
        $reporte = Reporte::findOrFail($reportId);
        $estadoAnterior = $reporte->estado;

        $reporte->update($data);

        $mensajeExtra = "";
        $usuario = $reporte->usuario;

        if ($usuario) {
            // Caso 1: Solucionado (Recompensa XP)
            if ($reporte->estado === 'solucionado' && $estadoAnterior !== 'solucionado') {
                $xp = ($reporte->prioridad === 'critica' || $reporte->prioridad === 'alta') ? 100 : 50;
                $usuario->increment('exp_total', $xp);

                // Usamos la acción global de nivel que ya tenemos para consistencia
                (new ProcessLevelUpAction())->execute($usuario);
                $mensajeExtra = " y usuario recompensado con $xp XP";
            }

            // Caso 2: Spam (Penalización XP)
            if ($reporte->estado === 'spam' && $estadoAnterior !== 'spam') {
                $penalizacion = 100;
                $usuario->exp_total = max(0, $usuario->exp_total - $penalizacion);
                $usuario->save();
                $mensajeExtra = " y usuario penalizado con -$penalizacion XP";
            }
        }

        return [
            'reporte' => $reporte,
            'mensaje' => 'Reporte actualizado correctamente' . $mensajeExtra
        ];
    }
}
