import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { NotificationService } from './notification.service';

@Injectable({
  providedIn: 'root',
})
export class ProgresoHistoriaService {

  private apiUrl = 'http://localhost/api/users/progreso-historia';
  private notificationService = inject(NotificationService);

  // State
  progresoSignal = signal<any>(null);

  constructor(private http: HttpClient) { }

  getProgresoHistoria(): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.get<any>(this.apiUrl, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(data => {
        this.progresoSignal.set(data);
      })
    );
  }

  updateProgresoHistoria(progreso: any): Observable<any> {
    const token = sessionStorage.getItem('token');
    // The POST route is /api/progreso-historia, while the GET is /api/users/progreso-historia
    const postUrl = 'http://localhost/api/progreso-historia';

    return this.http.post<any>(postUrl, progreso, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(res => {
        if (res.nuevos_logros && res.nuevos_logros.length > 0) {
          res.nuevos_logros.forEach((logro: any) => {
            this.notificationService.showAchievement(logro);
          });
        }
      })
    );
  }
}
