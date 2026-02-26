/** Modelo de un nivel del modo roguelike (infinito). */
export interface NivelRoguelike {
  id: number;
  dificultad: string;
  titulo: string;
  descripcion: string;
  recompensa_monedas: number;
  test_cases?: any[];
}

/** Modelo de un nivel del modo historia con progreso del usuario. */
export interface NivelHistoria {
  nivel_id: number;
  orden: number;
  titulo: string;
  descripcion: string;
  contenido_teorico: string;
  codigo_inicial: string;
  codigo_solucion_usuario: string | null;
  completado: boolean;
  test_cases?: any[];
}

/** Respuesta completa del progreso del usuario en modo historia. */
export interface ProgresoHistoriaResponse {
  usuario_id: number;
  niveles_completados: number;
  total_niveles: number;
  progreso_detallado: NivelHistoria[];
}
