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
  isAuthenticated = signal<boolean>(false);

  constructor(private http: HttpClient) {
    this.checkAuth();
  }

  private checkAuth() {
    this.isAuthenticated.set(!!sessionStorage.getItem('token'));
  }

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
      tap((res: any) => {
        this.isAuthenticated.set(true);
        if (res.es_admin !== undefined) {
          this.isAdminSignal.set(!!res.es_admin);
        }
      })
    );
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

  logout(): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.post(`${this.apiUrl}/logout`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(() => {
        this.clearSession();
      })
    );
  }

  desactivarCuenta(): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.post(`${this.apiUrl}/users/desactivar`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(() => {
        this.clearSession();
      })
    );
  }

  private clearSession() {
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('nickname');
    this.isAuthenticated.set(false);
    this.isAdminSignal.set(false);
  }
}

