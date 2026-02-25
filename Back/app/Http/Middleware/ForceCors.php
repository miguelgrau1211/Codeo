<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCors
{
    /** CORS headers applied to every response, including errors. */
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept-Language',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Respond immediately to preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders(self::CORS_HEADERS);
        }

        $response = $next($request);

        // Add CORS headers to every response (including 4xx/5xx)
        foreach (self::CORS_HEADERS as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
