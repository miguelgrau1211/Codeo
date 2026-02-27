import { Injectable, inject, signal } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';

/** Modelo de usuario para la vista de administración. */
export interface User {
    id: number;
    nickname: string;
    email: string;
    nivel_global: number;
    active: boolean | number;
    es_admin: boolean | number;
}

/** Respuesta paginada genérica del backend (Laravel). */
export interface PaginatedResponse<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: any[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

/**
 * Servicio de administración.
 *
 * Gestiona todas las operaciones CRUD del panel de administración:
 * - Usuarios (listar, eliminar, banear/desbanear).
 * - Niveles de historia y roguelike (CRUD + activar/desactivar).
 * - Estadísticas del dashboard y logs de auditoría.
 *
 * Incluye caché reactiva con signals para niveles de historia y roguelike,
 * evitando peticiones innecesarias al cambiar de pestaña.
 */
@Injectable({
    providedIn: 'root'
})
export class AdminService {
    private readonly http = inject(HttpClient);
    private readonly apiUrl = `${environment.apiUrl}/admin`;

    /** Caché reactiva de niveles de historia (paginados). */
    storyState = signal<{ data: StoryLevel[], page: number, total: number, last_page: number, loaded: boolean }>({
        data: [], page: 1, total: 0, last_page: 1, loaded: false
    });

    /** Caché reactiva de niveles roguelike (paginados). */
    roguelikeState = signal<{ data: RoguelikeLevel[], page: number, total: number, last_page: number, loaded: boolean }>({
        data: [], page: 1, total: 0, last_page: 1, loaded: false
    });

    /** Genera las cabeceras HTTP con el token de autenticación. */
    private getHeaders(): HttpHeaders {
        const token = sessionStorage.getItem('token');
        return new HttpHeaders({
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        });
    }

    /** Obtiene la lista paginada de usuarios con filtros opcionales de búsqueda y orden. */
    getUsers(page: number = 1, search: string = '', sortBy: string = 'id', sortOrder: 'asc' | 'desc' = 'desc'): Observable<PaginatedResponse<User>> {
        let url = `${this.apiUrl}/users?page=${page}&sort_by=${sortBy}&sort_order=${sortOrder}`;
        if (search) {
            url += `&search=${search}`;
        }
        return this.http.get<PaginatedResponse<User>>(url, {
            headers: this.getHeaders()
        });
    }

    /** Elimina permanentemente un usuario por su ID. */
    deleteUser(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/users/${id}`, {
            headers: this.getHeaders()
        });
    }

    /** Activa o desactiva un usuario (ban/unban). */
    toggleUserStatus(id: number, motivo: string = 'Desactivado por el administrador'): Observable<any> {
        return this.http.post(`${this.apiUrl}/users/${id}/toggle-status`, { motivo }, {
            headers: this.getHeaders()
        });
    }

    /** Obtiene las estadísticas generales del dashboard de administración. */
    getStats(): Observable<DashboardStats> {
        return this.http.get<DashboardStats>(`${this.apiUrl}/stats`, {
            headers: this.getHeaders()
        });
    }

    /** Obtiene los logs de auditoría del administrador con filtro por acción. */
    getLogs(page: number = 1, action: string = ''): Observable<PaginatedResponse<AdminLog>> {
        let url = `${this.apiUrl}/logs?page=${page}`;
        if (action) {
            url += `&action=${action}`;
        }
        return this.http.get<PaginatedResponse<AdminLog>>(url, {
            headers: this.getHeaders()
        });
    }

    // ── Niveles de Historia ────────────────────────────────

    /** Obtiene la lista paginada de niveles de historia activos. */
    getStoryLevels(page: number = 1): Observable<PaginatedResponse<StoryLevel>> {
        return this.http.get<PaginatedResponse<StoryLevel>>(`${this.apiUrl}/niveles-historia?page=${page}`, { headers: this.getHeaders() });
    }

    /** Obtiene todos los niveles de historia desactivados. */
    getStoryLevelsDesactivados(): Observable<StoryLevel[]> {
        return this.http.get<StoryLevel[]>(`${this.apiUrl}/niveles-historia/desactivados`, { headers: this.getHeaders() });
    }

    /** Activa o desactiva un nivel de historia. */
    toggleStoryLevelStatus(id: number, motivo: string = 'Admin action'): Observable<any> {
        return this.http.post(`${this.apiUrl}/niveles-historia/${id}/toggle-status`, { motivo }, { headers: this.getHeaders() });
    }

    /** Crea un nuevo nivel de historia. */
    createStoryLevel(data: any): Observable<StoryLevel> {
        return this.http.post<StoryLevel>(`${this.apiUrl}/niveles-historia`, data, { headers: this.getHeaders() });
    }

    /** Actualiza un nivel de historia existente. */
    updateStoryLevel(id: number, data: any): Observable<StoryLevel> {
        return this.http.put<StoryLevel>(`${this.apiUrl}/niveles-historia/${id}`, data, { headers: this.getHeaders() });
    }

    // ── Niveles Roguelike ──────────────────────────────────

    /** Obtiene la lista paginada de niveles roguelike activos. */
    getRoguelikeLevels(page: number = 1): Observable<PaginatedResponse<RoguelikeLevel>> {
        return this.http.get<PaginatedResponse<RoguelikeLevel>>(`${this.apiUrl}/niveles-roguelike?page=${page}`, { headers: this.getHeaders() });
    }

    /** Obtiene un nivel de historia específico por su ID (datos completos). */
    getStoryLevel(id: number): Observable<StoryLevel> {
        return this.http.get<StoryLevel>(`${this.apiUrl}/niveles-historia/${id}`, { headers: this.getHeaders() });
    }

    /** Obtiene un nivel roguelike específico por su ID (datos completos). */
    getRoguelikeLevel(id: number): Observable<RoguelikeLevel> {
        return this.http.get<RoguelikeLevel>(`${this.apiUrl}/niveles-roguelike/${id}`, { headers: this.getHeaders() });
    }

    /** Obtiene todos los niveles roguelike desactivados. */
    getRoguelikeLevelsDesactivados(): Observable<RoguelikeLevel[]> {
        return this.http.get<RoguelikeLevel[]>(`${this.apiUrl}/niveles-roguelike/desactivados`, { headers: this.getHeaders() });
    }

    /** Activa o desactiva un nivel roguelike. */
    toggleRoguelikeLevelStatus(id: number, motivo: string = 'Admin action'): Observable<any> {
        return this.http.post(`${this.apiUrl}/niveles-roguelike/${id}/toggle-status`, { motivo }, { headers: this.getHeaders() });
    }

    /** Crea un nuevo nivel roguelike. */
    createRoguelikeLevel(data: any): Observable<RoguelikeLevel> {
        return this.http.post<RoguelikeLevel>(`${this.apiUrl}/niveles-roguelike`, data, { headers: this.getHeaders() });
    }

    /** Actualiza un nivel roguelike existente. */
    updateRoguelikeLevel(id: number, data: any): Observable<RoguelikeLevel> {
        return this.http.put<RoguelikeLevel>(`${this.apiUrl}/niveles-roguelike/${id}`, data, { headers: this.getHeaders() });
    }
}

/** Estadísticas generales del panel de administración. */
export interface DashboardStats {
    total_users: number;
    active_users_24h: number;
    total_runs: number;
    success_rate: number;
}

/** Registro de log de auditoría de acciones del administrador. */
export interface AdminLog {
    id: number;
    user_id: number;
    user?: { id: number, nickname: string, email: string };
    action: string;
    details: string | null;
    created_at: string;
}

/** Modelo de un nivel del modo historia. */
export interface StoryLevel {
    id: number;
    orden: number;
    titulo: string;
    descripcion: string;
    recompensa_exp: number;
    recompensa_monedas: number;
    /** ID original del nivel (solo presente en niveles desactivados). */
    nivel_id_original?: number;
    fecha_desactivacion?: string;
}

/** Modelo de un nivel del modo roguelike (infinito). */
export interface RoguelikeLevel {
    id: number;
    dificultad: string;
    titulo: string;
    descripcion: string;
    recompensa_monedas: number;
    /** ID original del nivel (solo presente en niveles desactivados). */
    nivel_id_original?: number;
    fecha_desactivacion?: string;
}
