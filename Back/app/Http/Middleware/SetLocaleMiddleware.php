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

        // Map 'val' to 'ca' for Laravel/Carbon as 'val' is usually not supported
        $laravelLocale = ($locale === 'val') ? 'ca' : $locale;

        app()->setLocale($laravelLocale);

        return $next($request);
    }
}
