<?php

namespace App\Actions\User;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    /**
     * Actualiza los datos de un usuario de forma segura.
     * Si se incluye una contraseña, se hashea automáticamente antes de persistir.
     * 
     * @param Usuario $usuario El modelo del usuario a modificar.
     * @param array $data Los nuevos datos (nickname, email, password, etc.).
     * @return Usuario El modelo actualizado.
     */
    public function execute(Usuario $usuario, array $data): Usuario
    {
        // Encriptar la contraseña si ha sido proporcionada en el array de datos
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $usuario->update($data);

        return $usuario;
    }
}
