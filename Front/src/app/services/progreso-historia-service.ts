import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class ProgresoHistoriaService {

  private apiUrl = 'http://localhost/api/users/progreso-historia';

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
    return this.http.put<any>(this.apiUrl, progreso, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }); // TODO: update signal locally on success if optimization needed
  }
}
