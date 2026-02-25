import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { LogrosDesbloqueadosResponse, PorcentajeLogrosResponse } from '../models/achievement.model';

@Injectable({
    providedIn: 'root',
})
export class LogrosService {
    private readonly apiUrl = 'http://localhost/api/users';

    constructor(private readonly http: HttpClient) { }

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
     * Devuelve estadísticas de completitud.
     */
    getPorcentajeLogros(): Observable<PorcentajeLogrosResponse> {
        return this.http.get<PorcentajeLogrosResponse>(
            `${this.apiUrl}/porcentaje-logros`,
            { headers: this.getHeaders() }
        );
    }
}

