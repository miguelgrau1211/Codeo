import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface ReportePayload {
    email_contacto?: string;
    tipo: string;
    titulo: string;
    descripcion: string;
    prioridad?: 'baja' | 'media' | 'alta' | 'critica';
}

@Injectable({
    providedIn: 'root'
})
export class ReporteService {
    private readonly http = inject(HttpClient);
    private readonly apiUrl = 'http://localhost/api/reportes';

    enviarReporte(payload: ReportePayload): Observable<any> {
        const token = sessionStorage.getItem('token');
        return this.http.post(this.apiUrl, payload, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
    }

    // Admin methods
    getReportes(): Observable<any[]> {
        const token = sessionStorage.getItem('token');
        return this.http.get<any[]>('http://localhost/api/admin/reportes', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
    }

    actualizarEstado(id: number, data: { estado?: string; prioridad?: string }): Observable<any> {
        const token = sessionStorage.getItem('token');
        return this.http.put(`http://localhost/api/admin/reportes/${id}`, data, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
    }

    eliminarReporte(id: number): Observable<any> {
        const token = sessionStorage.getItem('token');
        return this.http.delete(`http://localhost/api/admin/reportes/${id}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
    }
}
