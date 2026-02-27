<?php

namespace App\Http\Controllers;

use App\Actions\User\HandleGoogleLoginAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

/**
 * Controlador para la autenticación social mediante Google (Socialite).
 */
class GoogleAuthController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');
        return $driver->stateless()->redirect();
    }

    /**
     * Maneja la respuesta de Google tras la autenticación.
     */
    public function handleGoogleCallback(HandleGoogleLoginAction $action): RedirectResponse
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');

            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = $driver->stateless()->user();

            $user = $action->execute($googleUser);

            // Generar token de Sanctum para autenticar al usuario en el frontend
            $token = $user->createToken('google-auth-token')->plainTextToken;

            // Redirigir al frontend con el token en la URL (Angular lo capturará)
            return redirect()->away(config('app.frontend_url') . '/login?token=' . $token);

        } catch (\Exception $e) {
            Log::error('Error en Google Auth', ['message' => $e->getMessage()]);
            return redirect()->away(config('app.frontend_url') . '/login?error=google_auth_failed');
        }
    }
}
