import { Injectable, inject } from '@angular/core';
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

@Injectable({
    providedIn: 'root'
})
export class AdminService {
    private http = inject(HttpClient);
    private apiUrl = 'http://localhost/api/admin';

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
