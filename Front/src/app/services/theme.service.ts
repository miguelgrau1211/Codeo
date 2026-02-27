import { Injectable, signal, inject, effect } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, tap, map } from 'rxjs';
import { UserDataService } from './user-data.service';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';

/** Modelo de un tema visual de la tienda. */
export interface Tema {
  id: number;
  nombre: string;
  descripcion: string;
  precio: number;
  es_exclusivo?: boolean;
  css_variables: Record<string, string>;
  preview_img: string;
}

/**
 * Servicio de temas visuales.
 *
 * Gestiona el sistema de theming dinámico de la aplicación:
 * - Carga y aplicación de temas comprados por el usuario.
 * - Sincronización con el backend (tema activo persistido en BD).
 * - Aplicación de variables CSS en tiempo real.
 * - Re-aplicación automática del tema al navegar entre rutas.
 *
 * El tema activo se aplica como CSS custom properties en :root.
 */
@Injectable({
  providedIn: 'root',
})
export class ThemeService {
  private readonly http = inject(HttpClient);
  private readonly userDataService = inject(UserDataService);
  private readonly router = inject(Router);
  private readonly apiUrl = environment.apiUrl;

  /** Signal reactivo con el tema actualmente activo. */
  readonly currentTheme = signal<Tema | null>(null);

  constructor() {
    // -------------------------------------------------------------------------------- //
    // 1. APLICAR TEMA O CACHÉ DE FORMA SÍNCRONA AL INSTANCIAR EL SERVICIO
    // Esto evita parpadeos mientras se obtienen los datos de la API.
    // -------------------------------------------------------------------------------- //
    const savedThemeCss = localStorage.getItem('applied-theme-css');
    if (savedThemeCss) {
      try {
        const cssVars = JSON.parse(savedThemeCss);
        const root = document.documentElement;
        Object.entries(cssVars).forEach(([key, value]) => {
          root.style.setProperty(key, value as string);
        });
      } catch (e) {
        console.error('Error al parsear el tema cacheado', e);
      }
    }

    // Intentar inicializar con caché local antes de cargar datos asíncronos para evitar parpadeos
    const savedThemeId = localStorage.getItem('applied-theme-id');
    if (savedThemeId && !this.currentTheme()) {
      this.getTemas().subscribe(temas => {
        const active = temas.find(t => t.id.toString() === savedThemeId);
        if (active && !this.currentTheme()) {
          // Solo si no se ha cargado todavía otra cosa mas fuerte desde UserDataService
          this.currentTheme.set(active);
        }
      });
    }

    // Aplicar tema automáticamente cuando cambia el signal y guardar caché
    effect(() => {
      const theme = this.currentTheme();
      if (theme) {
        this.applyTheme(theme);
        localStorage.setItem('applied-theme-id', theme.id.toString());
        localStorage.setItem('applied-theme-css', JSON.stringify(theme.css_variables));
      } else if (!sessionStorage.getItem('token')) {
        this.clearTheme();
        localStorage.removeItem('applied-theme-id');
        localStorage.removeItem('applied-theme-css');
      }
    });

    // Re-aplicar tema al navegar entre rutas
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: any) => {
      // Ignorar la página de landing, allí sí queremos limpiar
      if (event.url === '/' && !sessionStorage.getItem('token')) {
        this.clearTheme();
        return;
      }

      const theme = this.currentTheme();
      if (theme) {
        this.applyTheme(theme);
      }
    });

    // Sincronizar tema cuando se cargan los datos del usuario
    effect(() => {
      const userData = this.userDataService.userDataSignal();
      if (userData) {
        if (userData.tema_actual_id) {
          const themeId = userData.tema_actual_id;
          this.getTemas().subscribe(temas => {
            const active = temas.find(t => t.id === themeId);
            if (active) this.currentTheme.set(active);
          });
        } else {
          this.getTemas().subscribe(temas => {
            if (temas.length > 0) {
              // Buscar Deep Space por nombre como prioridad para el fallback
              const deepSpace = temas.find(t => t.nombre === 'Deep Space');
              this.currentTheme.set(deepSpace || temas[0]);
            }
          });
        }
      }
    });
  }

  /** Restaura los colores por defecto (Deep Space) cuando no hay tema activo. */
  private clearTheme(): void {
    const root = document.documentElement;
    root.style.setProperty('--primary-bg', '#050a14');
    root.style.setProperty('--secondary-bg', '#0a1020');
    root.style.setProperty('--accent-color', '#8b5cf6');
    root.style.setProperty('--text-main', '#e2e8f0');
    root.style.setProperty('--text-muted', '#64748b');
    root.style.setProperty('--editor-surface', '#020617');
    root.style.setProperty('--terminal-surface', '#0a1020');
    root.style.setProperty('--terminal-header', 'rgba(0,0,0,0.4)');
    root.style.removeProperty('--editor-bg-img');
  }

  /** Genera las cabeceras HTTP con el token de autenticación. */
  private getHeaders() {
    const token = sessionStorage.getItem('token');
    return {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    };
  }

  /** Obtiene todos los temas disponibles en la tienda. */
  getTemas(): Observable<Tema[]> {
    return this.http.get<{ data: Tema[] }>(`${this.apiUrl}/temas`, this.getHeaders()).pipe(
      map(response => response.data)
    );
  }

  /** Obtiene los temas que el usuario ya ha comprado. */
  getMisTemas(): Observable<Tema[]> {
    return this.http.get<{ data: Tema[] }>(`${this.apiUrl}/temas/mis-temas`, this.getHeaders()).pipe(
      map(response => response.data)
    );
  }

  /**
   * Compra un tema para el usuario actual.
   * @param temaId ID del tema a comprar.
   */
  comprarTema(temaId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/temas/${temaId}/comprar`, {}, this.getHeaders());
  }

  /**
   * Activa un tema comprado como tema actual del usuario.
   * Actualiza automáticamente el signal currentTheme.
   * @param temaId ID del tema a activar.
   */
  activarTema(temaId: number): Observable<any> {
    return this.http
      .post<{ message: string; tema: Tema }>(`${this.apiUrl}/temas/${temaId}/activar`, {}, this.getHeaders())
      .pipe(
        tap((response) => {
          this.currentTheme.set(response.tema);
        }),
      );
  }

  /**
   * Aplica las variables CSS de un tema en :root.
   * Limpia propiedades dinámicas que puedan no existir en todos los temas.
   */
  private applyTheme(tema: Tema): void {
    const root = document.documentElement;
    root.style.removeProperty('--editor-bg-img');

    Object.entries(tema.css_variables).forEach(([key, value]) => {
      root.style.setProperty(key, value);
    });
  }
}
