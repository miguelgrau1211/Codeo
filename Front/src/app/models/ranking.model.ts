/** Modelo de un usuario en la tabla de ranking global. */
export interface RankingUser {
  name: string;
  level: number;
  xp: number;
  avatar: string;
  rank: number;
  badges: string[];
}
