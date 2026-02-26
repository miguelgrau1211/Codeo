/** Modelo de un logro individual con estado de desbloqueo. */
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

/** Respuesta del endpoint de logros desbloqueados. */
export interface LogrosDesbloqueadosResponse {
  usuario_id: number;
  /** Formato: "3/26" (obtenidos/total). */
  progreso_logros: string;
  lista_completa: Logro[];
}

/** Respuesta del endpoint de porcentaje de logros. */
export interface PorcentajeLogrosResponse {
  usuario_id: number;
  logros_obtenidos: number;
  total_disponibles: number;
  porcentaje: number;
  texto: string;
}
