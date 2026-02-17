import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap, map } from 'rxjs';



interface UserData {
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
}

export interface ActivityItem {
  titulo: string;
  subtitulo: string;
  xp: number;
  tipo: 'historia' | 'logro' | 'roguelike';
  fecha: string;
}

@Injectable({
  providedIn: 'root',
})
export class UserDataService {

  private readonly http = inject(HttpClient);
  private readonly apiUrl = 'http://localhost/api/users';

  // UserData
  userDataSignal = signal<UserData | null>(null);

  getUserData(): Observable<UserData> {
    const token = sessionStorage.getItem('token');
    return this.http.get<UserData>(this.apiUrl + '/data', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(data => {
        this.userDataSignal.set(data);
      })
    );
  }

  getMiPosicionRanking(): Observable<{ posicion: number }> {
    const token = sessionStorage.getItem('token');
    return this.http.get<{ posicion: number }>(this.apiUrl + '/mi-posicion', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(data => {
        this.userDataSignal.update(current => current ? { ...current, rank: data.posicion } : current);
      })
    );
  }

  updateUser(data: { nickname?: string; avatar_url?: string }): Observable<any> {
    const token = sessionStorage.getItem('token');
    return this.http.put(this.apiUrl + '/perfil', data, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      tap(() => {
        // Optimistic update or refetch could go here. 
        // For now, we rely on the component to refetch or updater to update the signal manually if needed, 
        // but let's at least allow the backend response to drive it.
        // We can invalidate the cache or just update local signal if the backend returns the new user.
        // The backend returns { message: string, data: User }.
      }),
      tap((res: any) => {
         if (res.data) {
             // Correctly mapping the response to match UserData interface if needed
             // But simpler to just force a refresh of the data
             this.getUserData().subscribe(); 
         }
      })
    );
  }

  getRecentActivity(): Observable<ActivityItem[]> {
    const token = sessionStorage.getItem('token');
    return this.http.get<{ actividad: ActivityItem[] }>(this.apiUrl + '/actividad', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).pipe(
      map(response => response.actividad)
    );
  }





}