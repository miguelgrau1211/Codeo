import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, tap } from 'rxjs';
import { NotificationService } from './notification.service';

/**
 * Servicio de progreso del modo historia.
 *
 * Gestiona la carga y actualización del progreso del usuario
 * en el modo historia. Mantiene un signal reactivo con los datos
 * del progreso que puede ser consumido por múltiples componentes.
 *
 * Al completar un nivel, notifica automáticamente los logros
 * desbloqueados a través del NotificationService.
 */
@Injectable({
  providedIn: 'root',
})
export class ProgresoHistoriaService {
  private readonly apiUrl = `${environment.apiUrl}/users/progreso-historia`;
  private readonly notificationService = inject(NotificationService);
  private readonly http = inject(HttpClient);

  /** Signal reactivo con el progreso completo del modo historia. */
  readonly progresoSignal = signal<any>(null);

  /**
   * Obtiene el progreso del usuario en el modo historia.
   * Incluye estadísticas y detalle nivel por nivel.
   */
  getProgresoHistoria(): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.get<any>(this.apiUrl, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(data => this.progresoSignal.set(data))
    );
  }

  /**
   * Envía la solución del usuario para un nivel de historia.
   * Si hay logros nuevos, los muestra automáticamente como notificaciones.
   * @param progreso Datos del progreso (nivel_id, completado, código).
   */
  updateProgresoHistoria(progreso: any): Observable<any> {
    const token = sessionStorage.getItem('token');
    const postUrl = `${environment.apiUrl}/progreso-historia`;

    return this.http.post<any>(postUrl, progreso, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(res => {
        const logros = res.gamificacion?.nuevos_logros || res.nuevos_logros;
        if (logros && logros.length > 0) {
          logros.forEach((logro: any) => {
            this.notificationService.showAchievement(logro);
          });
        }
      })
    );
  }
}
