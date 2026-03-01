<?php

namespace App\Actions\User;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Acción para autenticar a un usuario y generar su token.
 */
class LoginUserAction
{
    /**
     * Ejecuta la autenticación.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function execute(string $email, string $password): array
    {
        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario || !Hash::check($password, $usuario->password)) {
            abort(401, 'Las credenciales proporcionadas son incorrectas.');
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $usuario
        ];
    }
}
