import { inject, Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap, map, of } from 'rxjs';

export interface UserData {
  nickname: string;
  avatar: string;
  level: number;
  experience: number;
  coins: number;
  streak: number;
  n_achievements: number;
  story_levels_completed: number;
  total_story_levels?: number;
  last_story_level_title?: string | null;
  roguelike_levels_played: number;
  subscription_date: string;
  rank: number;
}

export interface ActivityItem {
  titulo: string;
  subtitulo: string;
  xp: number;
  tipo: 'historia' | 'logro' | 'roguelike';
  fecha: string;
}

@Injectable({
  providedIn: 'root',
})
export class UserDataService {

  private readonly http = inject(HttpClient);
  private readonly apiUrl = 'http://localhost/api/users';

  // --- Estado central del usuario (singleton caché) ---
  readonly userDataSignal = signal<UserData | null>(null);

  /** True cuando se ha cargado al menos una vez */
  private readonly _loaded = signal(false);

  /** True mientras la petición está en vuelo */
  readonly isLoading = signal(false);

  /** Indica si hay datos disponibles */
  readonly isLoaded = computed(() => this._loaded());

  /**
   * Carga los datos del usuario.
   * Si ya están cacheados y no se fuerza, devuelve los datos existentes sin llamar a la API.
   */
  getUserData(forceRefresh = false): Observable<UserData> {
    // Si ya tenemos datos y no forzamos recarga, devolvemos los cacheados
    if (this._loaded() && !forceRefresh) {
      return of(this.userDataSignal()!);
    }

    this.isLoading.set(true);

    const token = sessionStorage.getItem('token');
    return this.http.get<UserData>(this.apiUrl + '/data', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(data => {
        this.userDataSignal.set(data);
        this._loaded.set(true);
        this.isLoading.set(false);
      })
    );
  }

  getMiPosicionRanking(): Observable<{ posicion: number }> {
    const token = sessionStorage.getItem('token');
    return this.http.get<{ posicion: number }>(this.apiUrl + '/mi-posicion', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(data => {
        this.userDataSignal.update(current => current ? { ...current, rank: data.posicion } : current);
      })
    );
  }

  updateUser(data: { nickname?: string; avatar_url?: string }): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.put(this.apiUrl + '/perfil', data, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap((res: any) => {
        if (res.data) {
          // Forzar recarga para obtener los datos actualizados del backend
          this.getUserData(true).subscribe();
        }
      })
    );
  }

  getRecentActivity(): Observable<ActivityItem[]> {
    const token = sessionStorage.getItem('token');
    return this.http.get<{ actividad: ActivityItem[] }>(this.apiUrl + '/actividad', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      map(response => response.actividad)
    );
  }

  /**
   * Invalida la caché, forzando que la próxima llamada recargue desde la API.
   * Útil tras acciones que modifiquen datos del usuario (comprar mejora, subir nivel, etc.)
   */
  invalidateCache(): void {
    this._loaded.set(false);
  }
}