import { Injectable, signal } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

// ── Interfaces ──────────────────────────────────────────────
export interface Logro {
    id: number;
    nombre: string;
    descripcion: string;
    icono_url: string | null;
    rareza: string;
    requisito_tipo: string;
    requisito_cantidad: number;
    desbloqueado: boolean;
    fecha_obtencion: string | null;
}

export interface LogrosDesbloqueadosResponse {
    usuario_id: number;
    progreso_logros: string;          // "3/26"
    lista_completa: Logro[];
}

export interface PorcentajeLogrosResponse {
    usuario_id: number;
    logros_obtenidos: number;
    total_disponibles: number;
    porcentaje: number;
    texto: string;
}

// ── Servicio ────────────────────────────────────────────────
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
