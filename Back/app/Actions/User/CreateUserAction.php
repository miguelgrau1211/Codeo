<?php

namespace App\Actions\User;

use App\Models\Usuario;
use App\DTOs\User\UserRegistrationData;
use Illuminate\Support\Facades\Hash;

/**
 * Acción responsable de la creación de un nuevo usuario en el sistema.
 */
class CreateUserAction
{
    /**
     * Ejecuta la lógica de creación.
     */
    public function execute(UserRegistrationData $data): Usuario
    {
        $avatarUrl = $data->avatarUrl
            ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($data->nickname);

        return Usuario::create([
            'nickname' => $data->nickname,
            'nombre' => $data->nombre,
            'apellidos' => $data->apellidos,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'avatar_url' => $avatarUrl,
            'terminos_aceptados' => $data->terminosAceptados,
        ]);
    }
}
