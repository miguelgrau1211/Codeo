import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = 'http://localhost/api';

  // State
  isAdminSignal = signal<boolean>(false);

  constructor(private http: HttpClient) { }

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials);
  }

  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/users`, userData);
  }

  // Modified to update signal
  esAdmin(token: string): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/es-admin`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(response => {
        this.isAdminSignal.set(!!response.es_admin);
      })
    );
  }

  validateUser(token: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/validate-user`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
  }
}
