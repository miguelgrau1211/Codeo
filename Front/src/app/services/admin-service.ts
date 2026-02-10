import { Injectable, inject, signal } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface User {
    id: number;
    nickname: string;
    email: string;
    nivel_global: number;
    active: boolean | number;
    es_admin: boolean | number;
}

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

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

@Injectable({
    providedIn: 'root'
})
export class AdminService {
    private http = inject(HttpClient);
    private apiUrl = 'http://localhost/api/admin';

    // State Cache
    storyState = signal<{ data: StoryLevel[], page: number, total: number, last_page: number, loaded: boolean }>({
        data: [], page: 1, total: 0, last_page: 1, loaded: false
    });

    roguelikeState = signal<{ data: RoguelikeLevel[], page: number, total: number, last_page: number, loaded: boolean }>({
        data: [], page: 1, total: 0, last_page: 1, loaded: false
    });

    private getHeaders() {
        const token = sessionStorage.getItem('token');
        return new HttpHeaders({
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        });
    }

    getUsers(page: number = 1, search: string = '', sortBy: string = 'id', sortOrder: 'asc' | 'desc' = 'desc'): Observable<PaginatedResponse<User>> {
        let url = `${this.apiUrl}/users?page=${page}&sort_by=${sortBy}&sort_order=${sortOrder}`;
        if (search) {
            url += `&search=${search}`;
        }
        return this.http.get<PaginatedResponse<User>>(url, {
            headers: this.getHeaders()
        });
    }

    deleteUser(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/users/${id}`, {
            headers: this.getHeaders()
        });
    }

    toggleUserStatus(id: number, motivo: string = 'Desactivado por el administrador'): Observable<any> {
        return this.http.post(`${this.apiUrl}/users/${id}/toggle-status`, { motivo }, {
            headers: this.getHeaders()
        });
    }

    getStats(): Observable<DashboardStats> {
        return this.http.get<DashboardStats>(`${this.apiUrl}/stats`, {
            headers: this.getHeaders()
        });
    }

    getLogs(page: number = 1, action: string = ''): Observable<PaginatedResponse<AdminLog>> {
        let url = `${this.apiUrl}/logs?page=${page}`;
        if (action) {
            url += `&action=${action}`;
        }
        return this.http.get<PaginatedResponse<AdminLog>>(url, {
            headers: this.getHeaders()
        });
    }

    // Niveles historia
    getStoryLevels(page: number = 1): Observable<PaginatedResponse<StoryLevel>> {
        return this.http.get<PaginatedResponse<StoryLevel>>(`${this.apiUrl}/niveles-historia?page=${page}`, { headers: this.getHeaders() });
    }

    getStoryLevelsDesactivados(): Observable<StoryLevel[]> {
        return this.http.get<StoryLevel[]>(`${this.apiUrl}/niveles-historia/desactivados`, { headers: this.getHeaders() });
    }

    toggleStoryLevelStatus(id: number, motivo: string = 'Admin action'): Observable<any> {
        return this.http.post(`${this.apiUrl}/niveles-historia/${id}/toggle-status`, { motivo }, { headers: this.getHeaders() });
    }

    createStoryLevel(data: any): Observable<StoryLevel> {
        return this.http.post<StoryLevel>(`${this.apiUrl}/niveles-historia`, data, { headers: this.getHeaders() });
    }

    updateStoryLevel(id: number, data: any): Observable<StoryLevel> {
        return this.http.put<StoryLevel>(`${this.apiUrl}/niveles-historia/${id}`, data, { headers: this.getHeaders() });
    }

    // Niveles roguelike
    getRoguelikeLevels(page: number = 1): Observable<PaginatedResponse<RoguelikeLevel>> {
        return this.http.get<PaginatedResponse<RoguelikeLevel>>(`${this.apiUrl}/niveles-roguelike?page=${page}`, { headers: this.getHeaders() });
    }

    getStoryLevel(id: number): Observable<StoryLevel> {
        return this.http.get<StoryLevel>(`${this.apiUrl}/niveles-historia/${id}`, { headers: this.getHeaders() });
    }

    getRoguelikeLevel(id: number): Observable<RoguelikeLevel> {
        return this.http.get<RoguelikeLevel>(`${this.apiUrl}/niveles-roguelike/${id}`, { headers: this.getHeaders() });
    }

    getRoguelikeLevelsDesactivados(): Observable<RoguelikeLevel[]> {
        return this.http.get<RoguelikeLevel[]>(`${this.apiUrl}/niveles-roguelike/desactivados`, { headers: this.getHeaders() });
    }

    toggleRoguelikeLevelStatus(id: number, motivo: string = 'Admin action'): Observable<any> {
        return this.http.post(`${this.apiUrl}/niveles-roguelike/${id}/toggle-status`, { motivo }, { headers: this.getHeaders() });
    }

    createRoguelikeLevel(data: any): Observable<RoguelikeLevel> {
        // Las rutas protegidas de CREATE/UPDATE están en /api/admin/niveles-roguelike
        return this.http.post<RoguelikeLevel>(`${this.apiUrl}/niveles-roguelike`, data, { headers: this.getHeaders() });
    }

    updateRoguelikeLevel(id: number, data: any): Observable<RoguelikeLevel> {
        return this.http.put<RoguelikeLevel>(`${this.apiUrl}/niveles-roguelike/${id}`, data, { headers: this.getHeaders() });
    }
}

export interface DashboardStats {
    total_users: number;
    active_users_24h: number;
    total_runs: number;
    success_rate: number;
}

export interface AdminLog {
    id: number;
    user_id: number;
    user?: { id: number, nickname: string, email: string };
    action: string;
    details: string | null;
    created_at: string;
}

export interface StoryLevel {
    id: number;
    orden: number;
    titulo: string;
    descripcion: string;
    recompensa_exp: number;
    recompensa_monedas: number;
    nivel_id_original?: number; // Para desactivados
    fecha_desactivacion?: string;
}

export interface RoguelikeLevel {
    id: number;
    dificultad: string;
    titulo: string;
    descripcion: string;
    recompensa_monedas: number;
    nivel_id_original?: number;
    fecha_desactivacion?: string;
}
