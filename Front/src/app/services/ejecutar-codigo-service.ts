import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class EjecutarCodigoService {

  private apiUrl = 'http://localhost/api/ejecutar-codigo';

  constructor(private http: HttpClient) { }

  ejecutarCodigo(codigo: string, tipo: string, nivel_id: number, token: string): Observable<any> {
    return this.http.post<any>(this.apiUrl, { codigo, tipo, nivel_id }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
  }
}
