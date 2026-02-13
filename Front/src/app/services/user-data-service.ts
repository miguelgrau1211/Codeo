import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';



interface UserData {
  nickname: string;
  avatar: string;
  level: number;
  experience: number;
  coins: number;
  streak: number;
  n_achievements: number;
  total_levels_completed: number;
}

@Injectable({
  providedIn: 'root',
})
export class UserDataService {

  private readonly http = inject(HttpClient);
  private readonly apiUrl = 'http://localhost/api/users/data';

  // UserData
  userDataSignal = signal<UserData | null>(null);

  getUserData(): Observable<UserData> {
    const token = sessionStorage.getItem('token');
    return this.http.get<UserData>(this.apiUrl, {
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
}