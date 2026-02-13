import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

export interface NivelRoguelike {
  id: number;
  dificultad: string;
  titulo: string;
  descripcion: string;
  recompensa_monedas: number;
  test_cases?: any[];
}

@Injectable({
  providedIn: 'root',
})
export class RoguelikeService {
  private apiUrl = 'http://localhost/api/niveles-roguelike/aleatorio';

  nivelActual = signal<NivelRoguelike | null>(null);

  constructor(private http: HttpClient) {}

  private getHeaders() {
    const token = sessionStorage.getItem('token') || '';
    return {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    };
  }

  getNivelAleatorio(nivelesCompletados: number = 0): Observable<NivelRoguelike> {
    return this.http
      .get<NivelRoguelike>(`${this.apiUrl}?niveles_completados=${nivelesCompletados}`, {
        headers: this.getHeaders(),
      })
      .pipe(tap((nivel) => this.nivelActual.set(nivel)));
  }
}
