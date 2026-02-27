<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TranslationService;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = TranslationService::resolveLocale($request);

        // Mapeamos 'val' a 'ca' para Laravel/Carbon, ya que 'val' no suele estar soportado de forma nativa
        $laravelLocale = ($locale === 'val') ? 'ca' : $locale;

        app()->setLocale($laravelLocale);

        return $next($request);
    }
}
