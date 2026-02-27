<?php

namespace App\Actions\User;

use App\Models\Usuario;
use App\Models\UsuarioDesactivado;
use Illuminate\Support\Facades\DB;

class DeactivateUserAction
{
    /**
     * Desactiva un usuario moviendo sus datos a la tabla 'usuarios_desactivados'
     * y eliminándolo de la tabla principal para evitar accesos.
     *
     * @param Usuario $usuario El modelo del usuario a desactivar.
     * @param string $reason El motivo de la desactivación/baneo.
     * @return bool Confirmación de la operación.
     */
    public function execute(Usuario $usuario, string $reason): bool
    {
        return DB::transaction(function () use ($usuario, $reason) {
            // Mover a la tabla de desactivados
            UsuarioDesactivado::create([
                'usuario_id_original' => $usuario->id,
                'nickname' => $usuario->nickname,
                'nombre' => $usuario->nombre,
                'apellidos' => $usuario->apellidos,
                'email' => $usuario->email,
                'nivel_alcanzado' => $usuario->nivel_global,
                'motivo' => $reason,
                'fecha_desactivacion' => now(),
            ]);

            // Eliminar de la tabla principal
            return $usuario->delete();
        });
    }
}
