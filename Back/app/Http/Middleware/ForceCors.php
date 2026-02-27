<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCors
{
    /** Cabeceras CORS aplicadas a cada respuesta, incluyendo errores. */
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept-Language',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Responder inmediatamente a las peticiones preflight OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders(self::CORS_HEADERS);
        }

        $response = $next($request);

        // Añadir cabeceras CORS a cada respuesta (incluyendo errores 4xx/5xx)
        foreach (self::CORS_HEADERS as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
