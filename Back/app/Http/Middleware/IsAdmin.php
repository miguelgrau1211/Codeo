<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }

            // Usamos una comprobacion segura por si la columna no existe
            $user = Auth::user();
            if (!$user->es_admin) {
                return response()->json(['message' => 'Acceso denegado. Se requieren permisos de administrador.'], 403);
            }

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error de autorización: ' . $e->getMessage()], 500);
        }
    }
}
