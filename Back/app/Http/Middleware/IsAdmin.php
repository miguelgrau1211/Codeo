<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Maneja una solicitud entrante.
     * Verifica si el usuario está autenticado y si tiene el rol de administrador.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $user = Auth::user();
        if (!$user->es_admin) {
            return response()->json(['message' => 'Acceso denegado. Se requieren permisos de administrador.'], 403);
        }

        return $next($request);
    }
}
