import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap, map } from 'rxjs';


export interface User {
    name: string;
    level: number;
    xp: number;
    avatar: string;
    rank: number;
    badges: string[];
}
@Injectable({
  providedIn: 'root',
})

export class RankingService {

    private readonly http = inject(HttpClient);
    private readonly apiUrl = 'http://localhost/api/ranking';

    getRanking(): Observable<User[]> {
        return this.http.get<{ usuarios: any[] }>(this.apiUrl).pipe(
            map((response: { usuarios: any[] }) => response.usuarios.map((u: any) => ({
                rank: u.posicion,
                name: u.nickname,
                xp: u.puntos,
                level: u.nivel,
                avatar: u.avatar_url,
                badges: [] // Backend doesn't return badges yet, defaulting to empty
            })))
        );
    }



}