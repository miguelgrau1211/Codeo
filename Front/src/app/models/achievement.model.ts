export interface Logro {
  id: number;
  nombre: string;
  descripcion: string;
  icono_url: string | null;
  rareza: string;
  requisito_tipo: string;
  requisito_cantidad: number;
  desbloqueado: boolean;
  fecha_obtencion: string | null;
}

export interface LogrosDesbloqueadosResponse {
  usuario_id: number;
  progreso_logros: string; // "3/26"
  lista_completa: Logro[];
}

export interface PorcentajeLogrosResponse {
  usuario_id: number;
  logros_obtenidos: number;
  total_disponibles: number;
  porcentaje: number;
  texto: string;
}

