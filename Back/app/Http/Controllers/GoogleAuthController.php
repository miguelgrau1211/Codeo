<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Maneja la respuesta de Google tras la autenticación.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            Log::info('Google Auth Success', [
                'id' => $googleUser->id,
                'email' => $googleUser->email,
                'name' => $googleUser->name
            ]);

            // Buscar usuario por google_id o por email
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

            // Iniciar sesión y generar token de Sanctum
            $token = $user->createToken('google-auth-token')->plainTextToken;

            // Redirigir al frontend con el token
            return redirect()->away('http://localhost:4200/login?token=' . $token);

        } catch (\Exception $e) {
            Log::error('Google Auth Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->away('http://localhost:4200/login?error=google_auth_failed');
        }
    }

    /**
     * Genera un nickname único basado en el nombre de Google.
     */
    private function generateUniqueNickname($name)
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
