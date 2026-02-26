import { inject, Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { RankingUser } from '../models/ranking.model';

/**
 * Servicio del ranking global.
 *
 * Obtiene la clasificación de usuarios ordenados por experiencia total.
 * Transforma la respuesta del backend al formato del modelo RankingUser.
 */
@Injectable({
  providedIn: 'root',
})
export class RankingService {
    private readonly http = inject(HttpClient);
    private readonly apiUrl = 'http://localhost/api/ranking';

    /**
     * Obtiene el ranking global de usuarios.
     * Mapea la respuesta del backend al modelo RankingUser del frontend.
     */
    getRanking(): Observable<RankingUser[]> {
        return this.http.get<{ usuarios: any[] }>(this.apiUrl).pipe(
            map((response: { usuarios: any[] }) => response.usuarios.map((u: any) => ({
                rank: u.posicion,
                name: u.nickname,
                xp: u.puntos,
                level: u.nivel,
                avatar: u.avatar_url,
                badges: []
            })))
        );
    }
}
