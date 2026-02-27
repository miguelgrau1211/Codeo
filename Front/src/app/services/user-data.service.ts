import { inject, Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, tap, map, of } from 'rxjs';
import { NotificationService } from './notification.service';
import { UserData, ActivityItem } from '../models/user.model';

/**
 * Servicio central de datos del usuario.
 *
 * Actúa como fuente única de verdad (single source of truth) para
 * los datos del usuario autenticado. Implementa un patrón de caché
 * reactiva con signals para evitar peticiones innecesarias.
 *
 * Los componentes consumen los signals derivados (level, coins, streak, etc.)
 * para renderizar datos siempre actualizados sin re-fetching.
 *
 * Funcionalidades principales:
 * - Carga y caché de datos completos del usuario.
 * - Signals computados para acceso rápido a propiedades frecuentes.
 * - Actualización optimista del estado local (XP, monedas, racha, nivel).
 * - Gestión de preferencias del usuario.
 * - Posición en el ranking global.
 */
@Injectable({
  providedIn: 'root',
})
export class UserDataService {
  private readonly http = inject(HttpClient);
  private readonly notificationService = inject(NotificationService);
  private readonly apiUrl = `${environment.apiUrl}/users`;

  // ── Estado central del usuario (singleton caché) ──────

  /** Signal principal con todos los datos del usuario. */
  readonly userDataSignal = signal<UserData | null>(null);

  // ── Signals computados para valores de acceso frecuente ──

  /** Racha de días consecutivos del usuario. */
  readonly streak = computed(() => this.userDataSignal()?.streak ?? 0);
  /** Monedas actuales del usuario. */
  readonly coins = computed(() => this.userDataSignal()?.coins ?? 0);
  /** Experiencia total acumulada. */
  readonly experience = computed(() => this.userDataSignal()?.experience ?? 0);
  /** Nivel global del usuario. */
  readonly level = computed(() => this.userDataSignal()?.level ?? 1);
  /** Indica si el usuario tiene el Pase de Batalla activo. */
  readonly isPremium = computed(() => this.userDataSignal()?.is_premium ?? false);

  /** True cuando se ha cargado al menos una vez desde la API. */
  private readonly _loaded = signal(false);

  /** True mientras la petición HTTP está en vuelo. */
  readonly isLoading = signal(false);

  /** Indica si hay datos disponibles en caché. */
  readonly isLoaded = computed(() => this._loaded());

  // ── Métodos de carga de datos ─────────────────────────

  /**
   * Carga los datos del usuario desde la API.
   * Si ya hay datos en caché y no se fuerza la recarga, devuelve los datos existentes.
   * @param forceRefresh Si es true, ignora la caché y consulta la API.
   */
  getUserData(forceRefresh = false): Observable<UserData> {
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

  /** Obtiene la posición del usuario en el ranking global. */
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

  /**
   * Actualiza el perfil del usuario (nickname, avatar).
   * Fuerza una recarga de datos tras la actualización.
   */
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
          this.getUserData(true).subscribe();
        }
      })
    );
  }

  /** Obtiene la actividad reciente del usuario (últimos 10 eventos). */
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

  // ── Actualización optimista del estado local ──────────

  /** Actualiza la racha del usuario localmente (sin petición HTTP). */
  setStreak(newStreak: number): void {
    this.userDataSignal.update(current => current ? { ...current, streak: newStreak } : current);
  }

  /**
   * Actualiza monedas y/o experiencia localmente.
   * Útil para feedback inmediato tras completar un nivel.
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

  /** Actualiza el nivel del usuario localmente. */
  setLevel(level: number): void {
    this.userDataSignal.update(current => current ? { ...current, level } : current);
  }

  /**
   * Procesa el resultado de level-up del backend.
   * Actualiza nivel y experiencia, y muestra notificación si hubo subida.
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
   * Útil tras acciones que modifiquen datos del usuario (compras, etc.).
   */
  invalidateCache(): void {
    this._loaded.set(false);
  }

  /**
   * Resetea completamente el estado del servicio.
   * Debe llamarse al cerrar sesión para evitar que los datos
   * del usuario anterior persistan en memoria.
   */
  reset(): void {
    this.userDataSignal.set(null);
    this._loaded.set(false);
    this.isLoading.set(false);
  }

  // ── Preferencias del usuario ──────────────────────────

  /**
   * Guarda las preferencias del usuario en el backend.
   * Actualiza el signal local inmediatamente.
   * @param preferencias Objeto con las preferencias (lang, theme, etc.).
   */
  savePreferencias(preferencias: any): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.post(this.apiUrl + '/preferencias', { preferencias }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(() => {
        this.userDataSignal.update(current => current ? { ...current, preferencias } : current);
      })
    );
  }
}
