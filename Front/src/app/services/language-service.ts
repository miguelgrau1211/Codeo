import { Injectable, signal, effect, inject, untracked } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { UserDataService } from './user-data-service';
import { catchError, EMPTY } from 'rxjs';

export interface Language {
    code: string;
    name: string;
    nativeName: string;
    flag: string;
}

@Injectable({
    providedIn: 'root'
})
export class LanguageService {
    private http = inject(HttpClient);
    private userDataService = inject(UserDataService);

    languages: Language[] = [
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

    currentLang = signal<string>('es');
    translations = signal<any>({});

    constructor() {
        // 1. Initial load from localStorage (fastest)
        const saved = localStorage.getItem('app_lang');
        if (saved) {
            this.currentLang.set(saved);
        }

        this.loadTranslations(this.currentLang());

        // 2. React to Language Changes -> Sync to LocalStorage & DB
        effect(() => {
            const lang = this.currentLang();
            localStorage.setItem('app_lang', lang);
            this.loadTranslations(lang);

            // Sync to DB (Untracked to avoid loop)
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

        // 3. React to User Data Load -> Initial Sync DB to UI
        let initialSyncDone = false;
        effect(() => {
            const user = this.userDataService.userDataSignal();
            if (user) {
                untracked(() => {
                    const dbLang = user.preferencias?.lang;
                    const localLang = this.currentLang();

                    if (dbLang && dbLang !== localLang && !initialSyncDone) {
                        this.currentLang.set(dbLang);
                        initialSyncDone = true;
                    }
                });
            }
        });
    }

    private loadTranslations(lang: string) {
        this.http.get(`./i18n/${lang}.json`).subscribe({
            next: (data) => {
                this.translations.set(data);
            },
            error: (err) => {
                console.warn(`Could not load translations for ${lang}, falling back to ES`, err);
                if (lang !== 'es') {
                    this.loadTranslations('es');
                }
            }
        });
    }

    setLanguage(code: string) {
        this.currentLang.set(code);
    }

    getCurrentLanguage(): Language {
        return this.languages.find(l => l.code === this.currentLang()) || this.languages[0];
    }

    /**
     * Helper to get a translation by key (e.g., 'SETTINGS.TITLE')
     */
    translate(path: string, params?: Record<string, any>): string {
        const keys = path.split('.');
        let result = this.translations();

        for (const key of keys) {
            if (result && result[key]) {
                result = result[key];
            } else {
                return path; // Return key if not found
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
