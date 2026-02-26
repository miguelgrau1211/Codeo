/** Modelo de un elemento de actividad reciente del usuario. */
export interface ActivityItem {
  titulo: string;
  subtitulo: string;
  xp: number;
  tipo: 'historia' | 'logro' | 'roguelike';
  fecha: string;
}

/**
 * Modelo central de datos del usuario autenticado.
 * Corresponde a la respuesta del endpoint GET /api/users/data.
 */
export interface UserData {
  nickname: string;
  email: string;
  avatar: string;
  level: number;
  experience: number;
  coins: number;
  streak: number;
  n_achievements: number;
  story_levels_completed: number;
  total_story_levels?: number;
  last_story_level_title?: string | null;
  roguelike_levels_played: number;
  subscription_date: string;
  rank: number;
  is_premium: boolean;
  /** ID del tema visual activo (null si usa el por defecto). */
  tema_actual_id?: number | null;
  /** Preferencias del usuario (idioma, configuraciones, etc.). */
  preferencias?: any;
  /** Logros desbloqueados pendientes de notificar. */
  nuevos_logros?: any[];
}
