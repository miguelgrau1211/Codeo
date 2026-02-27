import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

/**
 * Servicio para ejecutar código del usuario.
 *
 * Envía el código escrito por el usuario al backend para su evaluación
 * contra los test cases del nivel correspondiente.
 * Soporta tanto niveles de historia como de roguelike.
 */
@Injectable({
  providedIn: 'root',
})
export class EjecutarCodigoService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = `${environment.apiUrl}/ejecutar-codigo`;

  /**
   * Envía el código al backend para su ejecución y evaluación.
   * @param codigo Código fuente escrito por el usuario.
   * @param tipo Tipo de nivel ('historia' o 'roguelike').
   * @param nivel_id ID del nivel contra el que se evalúa.
   * @param token Token de autenticación del usuario.
   * @returns Observable con el resultado de la ejecución (correcto, detalles, etc.).
   */
  ejecutarCodigo(codigo: string, tipo: string, nivel_id: number, token: string): Observable<any> {
    return this.http.post<any>(this.apiUrl, { codigo, tipo, nivel_id }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
  }
}
