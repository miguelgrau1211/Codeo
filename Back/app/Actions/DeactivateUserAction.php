<?php

namespace App\Actions;

use App\Models\Usuario;
use App\Models\UsuarioDesactivado;
use Illuminate\Support\Facades\DB;

class DeactivateUserAction
{
    /**
     * Deactivates a user by moving them to the 'usuarios_desactivados' table.
     *
     * @param Usuario $usuario The user to deactivate.
     * @param string $reason The reason for deactivation.
     * @return bool
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
