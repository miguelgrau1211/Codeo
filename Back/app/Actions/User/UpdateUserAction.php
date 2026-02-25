<?php

namespace App\Actions\User;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    /**
     * Actualiza los datos de un usuario.
     */
    public function execute(Usuario $usuario, array $data): Usuario
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $usuario->update($data);

        return $usuario;
    }
}
