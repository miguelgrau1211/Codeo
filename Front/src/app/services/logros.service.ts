import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';
import { LogrosDesbloqueadosResponse, PorcentajeLogrosResponse } from '../models/achievement.model';

/**
 * Servicio de logros (achievements).
 *
 * Comunica con el backend para obtener la lista de logros
 * del usuario con su estado de desbloqueo y las estadísticas
 * de completitud general.
 */
@Injectable({
    providedIn: 'root',
})
export class LogrosService {
    private readonly apiUrl = `${environment.apiUrl}/users`;
    private readonly http = inject(HttpClient);

    /** Genera las cabeceras HTTP con el token de autenticación. */
    private getHeaders(): HttpHeaders {
        const token = sessionStorage.getItem('token') ?? '';
        return new HttpHeaders({
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
        });
    }

    /**
     * GET /api/users/logros-desbloqueados
     * Devuelve todos los logros del juego con flag `desbloqueado` por usuario.
     */
    getLogrosDesbloqueados(): Observable<LogrosDesbloqueadosResponse> {
        return this.http.get<LogrosDesbloqueadosResponse>(
            `${this.apiUrl}/logros-desbloqueados`,
            { headers: this.getHeaders() }
        );
    }

    /**
     * GET /api/users/porcentaje-logros
     * Devuelve estadísticas de completitud (porcentaje, total, obtenidos).
     */
    getPorcentajeLogros(): Observable<PorcentajeLogrosResponse> {
        return this.http.get<PorcentajeLogrosResponse>(
            `${this.apiUrl}/porcentaje-logros`,
            { headers: this.getHeaders() }
        );
    }

    /**
     * POST /api/users/logros/easter-egg
     * Ejecuta la comprobación manual para ver si el usuario ha desbloqueado el easter egg.
     */
    checkEasterEggAchievement(): Observable<{ message: string, nuevos_logros: any[] }> {
        return this.http.post<{ message: string, nuevos_logros: any[] }>(
            `${this.apiUrl}/logros/easter-egg`,
            {},
            { headers: this.getHeaders() }
        );
    }
}
