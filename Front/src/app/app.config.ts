import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideRouter, withHashLocation } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { routes } from './app.routes';
import { languageInterceptor } from './interceptors/language.interceptor';
/**
 * Configuración global de la aplicación Angular.
 *
 * Providers registrados:
 * - provideRouter: Navegación con tabla de rutas.
 * - provideHttpClient: Cliente HTTP con interceptor de idioma.
 * - languageInterceptor: Adjunta cabecera Accept-Language a cada request.
 */
export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes, withHashLocation()),
    provideHttpClient(
      withInterceptors([languageInterceptor])
    )
  ]
};

