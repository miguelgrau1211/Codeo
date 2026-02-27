import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, tap } from 'rxjs';
import { NivelRoguelike } from '../models/level.model';

/**
 * Servicio de niveles roguelike.
 *
 * Se encarga de obtener niveles aleatorios del modo infinito (roguelike)
 * desde el backend, escalando la dificultad según los niveles completados.
 */
@Injectable({
  providedIn: 'root',
})
export class RoguelikeService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = `${environment.apiUrl}/niveles-roguelike/aleatorio`;

  /** Signal reactivo con el nivel roguelike actualmente cargado. */
  readonly nivelActual = signal<NivelRoguelike | null>(null);

  /** Genera las cabeceras HTTP con el token de autenticación. */
  private getHeaders() {
    const token = sessionStorage.getItem('token') || '';
    return {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    };
  }

  /**
   * Obtiene un nivel aleatorio del backend.
   * La dificultad escala según la cantidad de niveles ya completados en la sesión.
   * @param nivelesCompletados Cantidad de niveles superados en la sesión actual.
   */
  getNivelAleatorio(nivelesCompletados: number = 0): Observable<NivelRoguelike> {
    return this.http
      .get<NivelRoguelike>(`${this.apiUrl}?niveles_completados=${nivelesCompletados}`, {
        headers: this.getHeaders(),
      })
      .pipe(tap((nivel) => this.nivelActual.set(nivel)));
  }
}
