import { Pipe, PipeTransform, inject } from '@angular/core';
import { LanguageService } from '../services/language.service';

/**
 * Pipe de traducción para plantillas.
 *
 * Uso: {{ 'CLAVE.TRADUCCION' | translate }}
 * Con parámetros: {{ 'CLAVE' | translate: { nombre: 'Juan' } }}
 *
 * Es impuro (pure: false) para reaccionar a cambios de idioma
 * sin necesitar que cambie el input del pipe.
 */
@Pipe({
    name: 'translate',
    standalone: true,
    pure: false
})
export class TranslatePipe implements PipeTransform {
    private readonly langService = inject(LanguageService);

    transform(key: string | undefined | null, params?: Record<string, any>): string {
        if (!key) return '';
        return this.langService.translate(key, params);
    }
}
