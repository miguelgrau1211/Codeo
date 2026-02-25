import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { LanguageService } from '../services/language-service';

/**
 * Attaches the current app language as the `Accept-Language` HTTP header
 * on every request directed to the backend API.
 *
 * The Laravel backend reads this header in TranslationService::resolveLocale()
 * to translate dynamic DB content (level titles, descriptions, etc.)
 * before returning the JSON response.
 *
 * We skip i18n JSON file fetches (./i18n/*.json) since those are static
 * assets served by the Angular dev server, not the Laravel API.
 */
export const languageInterceptor: HttpInterceptorFn = (req, next) => {
    // Skip static i18n asset fetches — they don't go through Laravel
    if (req.url.includes('/i18n/') && req.url.endsWith('.json')) {
        return next(req);
    }

    // Skip any request that is clearly not to our API
    // (e.g. external CDNs, Google Translate proxy, etc.)
    if (req.url.startsWith('http') && !req.url.includes('/api/')) {
        return next(req);
    }

    const langService = inject(LanguageService);
    const lang = langService.currentLang(); // reactive signal value

    const cloned = req.clone({
        setHeaders: {
            'Accept-Language': lang,
        },
    });

    return next(cloned);
};

