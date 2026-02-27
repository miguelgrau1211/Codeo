<?php

namespace App\Actions\User;

use App\Models\Usuario;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Acción para gestionar el inicio de sesión o registro mediante Google.
 */
class HandleGoogleLoginAction
{
    /**
     * @param SocialiteUser $googleUser
     * @return Usuario
     */
    public function execute(SocialiteUser $googleUser): Usuario
    {
        // 1. Buscar usuario por google_id o por email
        $user = Usuario::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            // Actualizar datos de Google si ya existe
            $user->update([
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);
            Log::info('Usuario de Google actualizado', ['id' => $user->id]);
        } else {
            // Registrar nuevo usuario
            $user = Usuario::create([
                'nickname' => $this->generateUniqueNickname($googleUser->name ?? $googleUser->nickname ?? 'user'),
                'nombre' => $googleUser->getRaw()['given_name'] ?? $googleUser->name ?? '',
                'apellidos' => $googleUser->getRaw()['family_name'] ?? '',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'password' => null,
                'avatar_url' => $googleUser->avatar,
                'terminos_aceptados' => true,
            ]);
            Log::info('Nuevo usuario de Google registrado', ['id' => $user->id]);
        }

        return $user;
    }

    /**
     * Genera un nickname único basado en el nombre de Google.
     */
    private function generateUniqueNickname(string $name): string
    {
        $baseNickname = Str::slug($name, '');
        $nickname = $baseNickname;
        $counter = 1;

        while (Usuario::where('nickname', $nickname)->exists()) {
            $nickname = $baseNickname . $counter;
            $counter++;
        }

        return $nickname;
    }
}
