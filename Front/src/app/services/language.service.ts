import { Injectable, signal, effect, inject, untracked } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { UserDataService } from './user-data.service';
import { catchError, EMPTY } from 'rxjs';

/** Modelo de un idioma disponible en la aplicación. */
export interface Language {
    code: string;
    name: string;
    nativeName: string;
    flag: string;
}

/**
 * Servicio de internacionalización (i18n).
 *
 * Gestiona el idioma activo de la aplicación con sincronización a tres niveles:
 * 1. localStorage (más rápido, para carga inicial).
 * 2. Base de datos (preferencias del usuario, para sincronización entre dispositivos).
 * 3. Archivos JSON estáticos (./i18n/{lang}.json) como fuente de traducciones.
 *
 * Usa effects reactivos para mantener sincronía automática entre los tres niveles.
 */
@Injectable({
    providedIn: 'root'
})
export class LanguageService {
    private readonly http = inject(HttpClient);
    private readonly userDataService = inject(UserDataService);

    /** Lista de idiomas soportados por la aplicación. */
    readonly languages: Language[] = [
        { code: 'es', name: 'Español', nativeName: 'Español', flag: '🇪🇸' },
        { code: 'en', name: 'Inglés', nativeName: 'English', flag: '🇬🇧' },
        { code: 'val', name: 'Valenciano', nativeName: 'Valencià', flag: '🚩' },
        { code: 'ru', name: 'Ruso', nativeName: 'Русский', flag: '🇷🇺' },
        { code: 'de', name: 'Alemán', nativeName: 'Deutsch', flag: '🇩🇪' },
        { code: 'zh', name: 'Chino', nativeName: '中文', flag: '🇨🇳' },
        { code: 'pt', name: 'Portugués', nativeName: 'Português', flag: '🇵🇹' },
        { code: 'fr', name: 'Francés', nativeName: 'Français', flag: '🇫🇷' },
        { code: 'it', name: 'Italiano', nativeName: 'Italiano', flag: '🇮🇹' },
        { code: 'tr', name: 'Turco', nativeName: 'Türkçe', flag: '🇹🇷' },
        { code: 'nl', name: 'Neerlandés', nativeName: 'Nederlands', flag: '🇳🇱' },
        { code: 'uk', name: 'Ucraniano', nativeName: 'Українська', flag: '🇺🇦' },
        { code: 'ar', name: 'Árabe', nativeName: 'العربية', flag: '🇸🇦' },
        { code: 'hi', name: 'Hindi', nativeName: 'हिन्दी', flag: '🇮🇳' },
    ];

    /** Signal reactivo con el código del idioma activo. */
    readonly currentLang = signal<string>('es');

    /** Signal reactivo con las traducciones cargadas del idioma activo. */
    readonly translations = signal<any>({});

    private initialSyncDone = false;

    constructor() {
        // 1. Carga inicial desde localStorage (lo más rápido)
        const saved = localStorage.getItem('app_lang');
        if (saved) {
            this.currentLang.set(saved);
        }

        this.loadTranslations(this.currentLang());

        // 2. React a cambios de idioma → sincronizar con localStorage y BD
        effect(() => {
            const lang = this.currentLang();
            localStorage.setItem('app_lang', lang);
            this.loadTranslations(lang);

            // Sincronizar con BD (untracked para evitar loops)
            const token = sessionStorage.getItem('token');
            if (token) {
                untracked(() => {
                    const user = this.userDataService.userDataSignal();
                    if (user && user.preferencias?.lang !== lang) {
                        const newPrefs = { ...(user.preferencias || {}), lang: lang };
                        this.userDataService.savePreferencias(newPrefs).pipe(
                            catchError(() => EMPTY)
                        ).subscribe();
                    }
                });
            }
        });

        // 3. React a carga de datos del usuario → sincronizar idioma desde BD
        effect(() => {
            const user = this.userDataService.userDataSignal();
            if (user) {
                untracked(() => {
                    const dbLang = user.preferencias?.lang;
                    const localLang = this.currentLang();

                    if (dbLang && dbLang !== localLang && !this.initialSyncDone) {
                        this.currentLang.set(dbLang);
                        this.initialSyncDone = true;
                    }
                });
            }
        });
    }

    /**
     * Carga las traducciones del idioma especificado desde archivos JSON.
     * Si no encuentra el archivo, hace fallback a español.
     */
    private loadTranslations(lang: string): void {
        this.http.get(`./i18n/${lang}.json`).subscribe({
            next: (data) => this.translations.set(data),
            error: (err) => {
                console.warn(`No se pudieron cargar las traducciones para "${lang}", usando ES`, err);
                if (lang !== 'es') {
                    this.loadTranslations('es');
                }
            }
        });
    }

    /** Cambia el idioma activo de la aplicación. */
    setLanguage(code: string): void {
        this.currentLang.set(code);
    }

    /** Resetea el tracking de sincronización (usado habitualmente tras logout). */
    resetSync(): void {
        this.initialSyncDone = false;
    }

    /** Obtiene el objeto Language del idioma actualmente seleccionado. */
    getCurrentLanguage(): Language {
        return this.languages.find(l => l.code === this.currentLang()) || this.languages[0];
    }

    /**
     * Obtiene una traducción por clave con soporte de parámetros interpolados.
     * @param path Clave de traducción separada por puntos (ej: 'SETTINGS.TITLE').
     * @param params Parámetros opcionales para interpolar (ej: {{name}} → 'Juan').
     * @returns Texto traducido o la clave original si no se encuentra.
     */
    translate(path: string, params?: Record<string, any>): string {
        const keys = path.split('.');
        let result = this.translations();

        for (const key of keys) {
            if (result && result[key]) {
                result = result[key];
            } else {
                return path;
            }
        }

        let translated = typeof result === 'string' ? result : path;

        if (params) {
            Object.keys(params).forEach(key => {
                translated = translated.split(`{{${key}}}`).join(params[key]);
            });
        }

        return translated;
    }
}
