import { inject, Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap, map, of } from 'rxjs';
import { NotificationService } from './notification.service';

export interface ActivityItem {
  titulo: string;
  subtitulo: string;
  xp: number;
  tipo: 'historia' | 'logro' | 'roguelike';
  fecha: string;
}

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
  tema_actual_id?: number | null;
  nuevos_logros?: any[];
}

@Injectable({
  providedIn: 'root',
})
export class UserDataService {

  private readonly http = inject(HttpClient);
  private readonly notificationService = inject(NotificationService);
  private readonly apiUrl = 'http://localhost/api/users';

  // --- Estado central del usuario (singleton caché) ---
  readonly userDataSignal = signal<UserData | null>(null);

  /** Signals reactivos para valores que cambian frecuentemente */
  readonly streak = computed(() => this.userDataSignal()?.streak ?? 0);
  readonly coins = computed(() => this.userDataSignal()?.coins ?? 0);
  readonly experience = computed(() => this.userDataSignal()?.experience ?? 0);
  readonly level = computed(() => this.userDataSignal()?.level ?? 1);

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
        if (data.nuevos_logros && data.nuevos_logros.length > 0) {
          data.nuevos_logros.forEach(logro => {
            this.notificationService.showAchievement(logro);
          });
        }
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
   * Actualiza el streak manualmente tras una acción
   */
  setStreak(newStreak: number): void {
    this.userDataSignal.update(current => current ? { ...current, streak: newStreak } : current);
  }

  /**
   * Actualiza monedas y XP manualmente tras una acción
   */
  updateEconomy(coins?: number, xp?: number): void {
    this.userDataSignal.update(current => {
      if (!current) return null;
      return {
        ...current,
        coins: coins !== undefined ? coins : current.coins,
        experience: xp !== undefined ? xp : current.experience
      };
    });
  }

  /**
   * Actualiza el nivel manualmente
   */
  setLevel(level: number): void {
    this.userDataSignal.update(current => current ? { ...current, level: level } : current);
  }

  /**
   * Procesa el resultado de level_up del backend
   */
  handleLevelUpResult(levelUpData: any): void {
    if (!levelUpData) return;

    if (levelUpData.leveled_up) {
      this.setLevel(levelUpData.current_level);
      this.notificationService.showLevelUp(levelUpData.current_level);
    }
    
    if (levelUpData.exp_total !== undefined) {
      this.userDataSignal.update(current => current ? { ...current, experience: levelUpData.exp_total } : current);
    }
  }

  /**
   * Invalida la caché, forzando que la próxima llamada recargue desde la API.
   * Útil tras acciones que modifiquen datos del usuario (comprar mejora, subir nivel, etc.)
   */
  invalidateCache(): void {
    this._loaded.set(false);
  }
}