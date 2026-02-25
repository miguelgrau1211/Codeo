import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { NotificationService } from './notification.service';

export interface RunStats {
  niveles_superados: number;
  monedas_obtenidas: number;
  xp_ganada: number;
  vidas_restantes: number;
}

export interface Achievement {
  id: number;
  nombre: string;
  descripcion: string;
  icono_url: string | null;
  rareza: string;
}

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

@Injectable({
  providedIn: 'root'
})
export class RoguelikeSessionService {
  private apiUrl = 'http://localhost/api/roguelike';
  private notificationService = inject(NotificationService);

  constructor(private http: HttpClient) { }

  private getHeaders() {
    const token = sessionStorage.getItem('token') || '';
    return {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    };
  }

  startSession(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/start-session`, {},
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => {
        if (res.nuevos_logros && res.nuevos_logros.length > 0) {
          res.nuevos_logros.forEach(logro => {
            this.notificationService.showAchievement(logro);
          });
        }
      })
    );
  }

  startLevel(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/start-level`, {},
      { headers: this.getHeaders() }
    );
  }

  checkTime(): Observable<RoguelikeSession> {
    return this.http.get<RoguelikeSession>(
      `${this.apiUrl}/check-time`,
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => {
        if (res.nuevos_logros && res.nuevos_logros.length > 0) {
          res.nuevos_logros.forEach(logro => {
            this.notificationService.showAchievement(logro);
          });
        }
      })
    );
  }

  registerFailure(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/failure`, {},
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => {
        if (res.nuevos_logros && res.nuevos_logros.length > 0) {
          res.nuevos_logros.forEach(logro => {
            this.notificationService.showAchievement(logro);
          });
        }
      })
    );
  }

  registerSuccess(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/success`, {},
      { headers: this.getHeaders() }
    ).pipe(
      tap(res => {
        if (res.nuevos_logros && res.nuevos_logros.length > 0) {
          res.nuevos_logros.forEach(logro => {
            this.notificationService.showAchievement(logro);
          });
        }
      })
    );
  }

  getSession(): Observable<RoguelikeSession> {
    return this.http.get<RoguelikeSession>(
      `${this.apiUrl}/session`,
      { headers: this.getHeaders() }
    );
  }

  getMejorasRandom(): Observable<any[]> {
    return this.http.get<any[]>(
      'http://localhost/api/mejoras/random',
      { headers: this.getHeaders() }
    );
  }

  buyMejora(mejoraId: number): Observable<any> {
    return this.http.post<any>(
      `${this.apiUrl}/buy-mejora`,
      { mejora_id: mejoraId },
      { headers: this.getHeaders() }
    );
  }

  debugSetTime(): Observable<RoguelikeSession> {
    return this.http.post<RoguelikeSession>(
      `${this.apiUrl}/debug-set-time`, {},
      { headers: this.getHeaders() }
    );
  }
}

