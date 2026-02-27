import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, tap } from 'rxjs';
import { NotificationService } from './notification.service';

/** Estadísticas de la partida roguelike finalizada. */
export interface RunStats {
  niveles_superados: number;
  monedas_obtenidas: number;
  xp_ganada: number;
  vidas_restantes: number;
}

/** Logro desbloqueado durante la sesión. */
export interface Achievement {
  id: number;
  nombre: string;
  descripcion: string;
  icono_url: string | null;
  rareza: string;
}

/** Estado de la sesión roguelike devuelto por el servidor. */
export interface RoguelikeSession {
  lives: number;
  time_remaining: number;
  levels_completed: number;
  coins_earned: number;
  xp_earned: number;
  active?: boolean;
  game_over?: boolean;
  stats?: RunStats;
  message?: string;
  time_expired?: boolean;
  nuevos_logros?: Achievement[];
  racha?: {
    streak: number;
    max_streak: number;
    updated: boolean;
    reset: boolean;
  };
  level_up?: {
    leveled_up: boolean;
    old_level: number;
    current_level: number;
    exp_total: number;
    next_level_exp: number;
  };
}

/**
 * Servicio de sesión roguelike.
 *
 * Gestiona todo el ciclo de vida de una partida roguelike:
 * - Inicio de sesión y niveles.
 * - Control de tiempo (check-time).
 * - Registro de éxitos y fallos.
 * - Tienda de mejoras (compra de upgrades).
 * - Notificación automática de logros desbloqueados.
 *
 * Toda la lógica de juego es server-authoritative:
 * el cliente solo envía intenciones y el servidor calcula consecuencias.
 */
@Injectable({
  providedIn: 'root'
})
export class RoguelikeSessionService {
  private readonly apiUrl = `${environment.apiUrl}/roguelike`;
  private readonly notificationService = inject(NotificationService);
  private readonly http = inject(HttpClient);

  /** Genera las cabeceras HTTP con el token de autenticación. */
  private getHeaders() {
    const token = sessionStorage.getItem('token') || '';
    return {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    };
  }

  /** Inicia una nueva sesión roguelike (resetea vidas, tiempo, etc.). */
  startSession(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/start-session`, {},
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => this.notificarLogros(res))
    );
  }

  /** Inicia un nuevo nivel dentro de la sesión activa. */
  startLevel(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/start-level`, {},
      { headers: this.getHeaders() }
    );
  }

  /** Verifica el tiempo restante con el servidor (fuente de verdad). */
  checkTime(): Observable<RoguelikeSession> {
    return this.http.get<RoguelikeSession>(
      `${this.apiUrl}/check-time`,
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => this.notificarLogros(res))
    );
  }

  /** Registra un fallo (respuesta incorrecta) en la sesión actual. */
  registerFailure(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/failure`, {},
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => this.notificarLogros(res))
    );
  }

  /** Registra un éxito (respuesta correcta) en la sesión actual. */
  registerSuccess(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/success`, {},
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => this.notificarLogros(res))
    );
  }

  /** Obtiene el estado actual de la sesión roguelike. */
  getSession(): Observable<RoguelikeSession> {
    return this.http.get<RoguelikeSession>(
      `${this.apiUrl}/session`,
      { headers: this.getHeaders() }
    );
  }

  /** Obtiene 3 mejoras aleatorias disponibles para comprar. */
  getMejorasRandom(): Observable<any[]> {
    return this.http.get<any[]>(
      `${environment.apiUrl}/mejoras/random`,
      { headers: this.getHeaders() }
    );
  }

  /** Compra una mejora dentro de la sesión roguelike. */
  buyMejora(mejoraId: number): Observable<any> {
    return this.http.post<any>(
      `${this.apiUrl}/buy-mejora`,
      { mejora_id: mejoraId },
      { headers: this.getHeaders() }
    );
  }

  /** [Solo Admin] Fija el tiempo restante a 10s para pruebas. */
  debugSetTime(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/debug-set-time`, {},
      { headers: this.getHeaders() }
    );
  }

  /**
   * Muestra notificaciones para logros desbloqueados en la respuesta.
   * Método helper privado para evitar duplicación de código.
   */
  private notificarLogros(res: RoguelikeSession): void {
    if (res.nuevos_logros && res.nuevos_logros.length > 0) {
      res.nuevos_logros.forEach(logro => {
        this.notificationService.showAchievement(logro);
      });
    }
  }
}
