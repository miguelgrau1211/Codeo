import { Injectable, signal, inject, effect } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap, map } from 'rxjs';
import { UserDataService } from './user-data-service';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';

export interface Tema {
  id: number;
  nombre: string;
  descripcion: string;
  precio: number;
  es_exclusivo?: boolean;
  css_variables: Record<string, string>;
  preview_img: string;
}

@Injectable({
  providedIn: 'root',
})
export class ThemeService {
  private http = inject(HttpClient);
  private userDataService = inject(UserDataService);
  private router = inject(Router);
  private apiUrl = 'http://localhost/api';

  // State
  currentTheme = signal<Tema | null>(null);
  
  constructor() {
    // Automatically apply theme variables when the current theme changes or route changes
    effect(() => {
      const theme = this.currentTheme();
      if (theme) {
        this.applyTheme(theme);
        localStorage.setItem('applied-theme-id', theme.id.toString());
      } else {
        this.clearTheme();
      }
    });

    // Listen to router changes to ensure theme is applied on navigation
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe(() => {
      const theme = this.currentTheme();
      if (theme) {
        this.applyTheme(theme);
      } else {
        this.clearTheme();
      }
    });

    // Sync theme when user data is loaded
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
          // No theme active? Load default (first one available)
          this.getTemas().subscribe(temas => {
            if (temas.length > 0) {
              this.currentTheme.set(temas[0]);
            }
          });
        }
      }
    });
  }

  private clearTheme() {
    const root = document.documentElement;
    // Fallback to Deep Space colors (our new default)
    root.style.setProperty('--primary-bg', '#050a14');
    root.style.setProperty('--secondary-bg', '#0a1020');
    root.style.setProperty('--accent-color', '#8b5cf6');
    root.style.setProperty('--text-main', '#e2e8f0');
    root.style.setProperty('--text-muted', '#64748b');
    root.style.removeProperty('--editor-bg-img');
  }

  private getHeaders() {
    const token = sessionStorage.getItem('token');
    return {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    };
  }

  getTemas(): Observable<Tema[]> {
    return this.http.get<{ data: Tema[] }>(`${this.apiUrl}/temas`, this.getHeaders()).pipe(
      map(response => response.data)
    );
  }

  getMisTemas(): Observable<Tema[]> {
    return this.http.get<{ data: Tema[] }>(`${this.apiUrl}/temas/mis-temas`, this.getHeaders()).pipe(
      map(response => response.data)
    );
  }

  comprarTema(temaId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/temas/${temaId}/comprar`, {}, this.getHeaders());
  }

  activarTema(temaId: number): Observable<any> {
    return this.http
      .post<{ message: string; tema: Tema }>(`${this.apiUrl}/temas/${temaId}/activar`, {}, this.getHeaders())
      .pipe(
        tap((response) => {
          this.currentTheme.set(response.tema);
        }),
      );
  }

  private applyTheme(tema: Tema) {
    const root = document.documentElement;
    // Clear dynamic properties that might not be in all themes
    root.style.removeProperty('--editor-bg-img');

    Object.entries(tema.css_variables).forEach(([key, value]) => {
      root.style.setProperty(key, value);
    });
  }
}

