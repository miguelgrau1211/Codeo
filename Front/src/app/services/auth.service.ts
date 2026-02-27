import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { UserDataService } from './user-data.service';
import { LanguageService } from './language.service';
import { environment } from '../../environments/environment';

/**
 * Servicio de autenticación.
 *
 * Gestiona el ciclo de vida de la sesión del usuario:
 * - Login (credenciales + Google OAuth).
 * - Registro de nuevos usuarios.
 * - Validación de tokens existentes.
 * - Logout y desactivación de cuenta.
 * - Estado reactivo de autenticación y permisos de administrador.
 */
@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly userDataService = inject(UserDataService);
  private readonly languageService = inject(LanguageService);
  private readonly apiUrl = environment.apiUrl;

  /** Signal reactivo que indica si el usuario es administrador. */
  readonly isAdminSignal = signal<boolean>(false);

  /** Signal reactivo que indica si hay una sesión activa. */
  readonly isAuthenticated = signal<boolean>(false);

  constructor() {
    this.checkAuth();
  }

  /** Verifica si existe un token en sessionStorage al iniciar. */
  private checkAuth(): void {
    this.isAuthenticated.set(!!sessionStorage.getItem('token'));
  }

  /**
   * Inicia sesión con email y contraseña.
   * Actualiza los signals de autenticación y admin al recibir la respuesta.
   */
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

  /** Registra un nuevo usuario en la plataforma. */
  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/users`, userData);
  }

  /**
   * Consulta al backend si el usuario es administrador.
   * Actualiza el signal isAdminSignal con la respuesta.
   */
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

  /**
   * Valida un token de autenticación existente.
   * Usado al recargar la página o al recibir token de Google OAuth.
   */
  validateUser(token: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/validate-user`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
  }

  /** Cierra la sesión del usuario y limpia los datos locales. */
  logout(): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.post(`${this.apiUrl}/logout`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(() => this.clearSession())
    );
  }

  /**
   * Desactiva (soft-delete) la cuenta del usuario.
   * El usuario puede ser reactivado por un administrador.
   */
  desactivarCuenta(): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.post(`${this.apiUrl}/users/desactivar`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(() => this.clearSession())
    );
  }

  /** Limpia toda la sesión local: sessionStorage, caché de usuario y signals. */
  private clearSession(): void {
    sessionStorage.clear();
    this.userDataService.reset();
    this.isAuthenticated.set(false);
    this.isAdminSignal.set(false);
    this.languageService.setLanguage('es'); // Resetear a español tras logout
    this.languageService.resetSync(); // Asegurar que el próximo usuario sincronice desde BD
  }
}
