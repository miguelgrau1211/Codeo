import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { LanguageService } from '../services/language.service';

/**
 * Interceptor funcional de idioma.
 *
 * Adjunta la cabecera `Accept-Language` con el idioma activo
 * a todas las peticiones HTTP dirigidas al backend (API Laravel).
 *
 * El backend usa esta cabecera en TranslationService::resolveLocale()
 * para traducir contenido dinámico de la BD (títulos, descripciones, etc.)
 * antes de devolver la respuesta JSON.
 *
 * Se excluyen:
 * - Peticiones a archivos JSON de i18n (assets estáticos del frontend).
 * - Peticiones a dominios externos (CDNs, APIs de terceros).
 */
export const languageInterceptor: HttpInterceptorFn = (req, next) => {
    // Excluir peticiones a archivos de traducción estáticos
    if (req.url.includes('/i18n/') && req.url.endsWith('.json')) {
        return next(req);
    }

    // Excluir peticiones a dominios externos (no nuestra API)
    if (req.url.startsWith('http') && !req.url.includes('/api/')) {
        return next(req);
    }

    const langService = inject(LanguageService);
    const lang = langService.currentLang();

    const cloned = req.clone({
        setHeaders: {
            'Accept-Language': lang,
        },
    });

    return next(cloned);
};
