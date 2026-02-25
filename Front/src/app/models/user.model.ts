export interface ActivityItem {
  titulo: string;
  subtitulo: string;
  xp: number;
  tipo: 'historia' | 'logro' | 'roguelike';
  fecha: string;
}

export interface UserData {
  nickname: string;
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
  tema_actual_id?: number | null;
  preferencias?: any;
  nuevos_logros?: any[];
}

