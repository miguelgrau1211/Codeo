import { inject, Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { RankingUser } from '../models/ranking.model';

@Injectable({
  providedIn: 'root',
})
export class RankingService {

    private readonly http = inject(HttpClient);
    private readonly apiUrl = 'http://localhost/api/ranking';

    getRanking(): Observable<RankingUser[]> {
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
