<?php

namespace App\Actions\Reports;

use App\Models\Reporte;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;

/**
 * Acción para crear un nuevo reporte de error o sugerencia.
 */
class CreateReportAction
{
    /**
     * @param Usuario $user
     * @param array $data ['titulo', 'descripcion', 'tipo', 'email_contacto', 'prioridad']
     * @return Reporte
     * @throws ValidationException
     */
    public function execute(Usuario $user, array $data): Reporte
    {
        // 1. Anti-Spam: Máximo 3 reportes pendientes
        $pendientes = Reporte::where('usuario_id', $user->id)
            ->where('estado', 'pendiente')
            ->count();

        if ($pendientes >= 3) {
            throw ValidationException::withMessages([
                'report' => ['Tienes demasiados reportes pendientes (máximo 3). Espera a que los revisemos.'],
            ]);
        }

        // 2. Creación
        return Reporte::create([
            'usuario_id' => $user->id,
            'email_contacto' => $data['email_contacto'] ?? $user->email,
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'prioridad' => $data['prioridad'] ?? 'media',
            'estado' => 'pendiente',
        ]);
    }
}
