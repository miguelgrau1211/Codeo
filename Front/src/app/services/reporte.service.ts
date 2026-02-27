import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';

/** Estructura del payload para enviar un reporte al backend. */
export interface ReportePayload {
    email_contacto?: string;
    tipo: string;
    titulo: string;
    descripcion: string;
    prioridad?: 'baja' | 'media' | 'alta' | 'critica';
}

/**
 * Servicio de reportes y soporte.
 *
 * Permite a los usuarios enviar reportes de bugs, sugerencias o problemas.
 * También proporciona métodos de administración para gestionar
 * los reportes recibidos (listar, actualizar estado, eliminar).
 */
@Injectable({
    providedIn: 'root'
})
export class ReporteService {
    private readonly http = inject(HttpClient);
    private readonly apiUrl = `${environment.apiUrl}/reportes`;

    /** Genera las cabeceras HTTP con el token de autenticación. */
    private getAuthHeaders() {
        const token = sessionStorage.getItem('token');
        return {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        };
    }

    /** Envía un nuevo reporte desde el formulario de soporte. */
    enviarReporte(payload: ReportePayload): Observable<any> {
        return this.http.post(this.apiUrl, payload, {
            headers: this.getAuthHeaders()
        });
    }

    // ── Métodos de administración ─────────────────────────

    /** [Admin] Obtiene todos los reportes del sistema. */
    getReportes(): Observable<any[]> {
        return this.http.get<any[]>(`${environment.apiUrl}/admin/reportes`, {
            headers: this.getAuthHeaders()
        });
    }

    /**
     * [Admin] Actualiza el estado y/o prioridad de un reporte.
     * @param id ID del reporte.
     * @param data Campos a actualizar (estado, prioridad).
     */
    actualizarEstado(id: number, data: { estado?: string; prioridad?: string }): Observable<any> {
        return this.http.put(`${environment.apiUrl}/admin/reportes/${id}`, data, {
            headers: this.getAuthHeaders()
        });
    }

    /** [Admin] Elimina permanentemente un reporte. */
    eliminarReporte(id: number): Observable<any> {
        return this.http.delete(`${environment.apiUrl}/admin/reportes/${id}`, {
            headers: this.getAuthHeaders()
        });
    }
}
