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
  // codigo_inicial? - Not in DB yet, handle in frontend
}

@Injectable({
  providedIn: 'root'
})
export class RoguelikeService {
  private apiUrl = 'http://localhost/api/niveles-roguelike/aleatorio';

  // Signal to hold current level data
  nivelActual = signal<NivelRoguelike | null>(null);

  constructor(private http: HttpClient) { }

  getNivelAleatorio(nivelesCompletados: number = 0): Observable<NivelRoguelike> {
    return this.http.get<NivelRoguelike>(`${this.apiUrl}?niveles_completados=${nivelesCompletados}`).pipe(
      tap(nivel => this.nivelActual.set(nivel))
    );
  }
}
