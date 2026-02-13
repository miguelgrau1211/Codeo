import { Component, computed, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { UserDataService } from '../services/user-data-service';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './perfil.html',
  styleUrl: './perfil.css',
})
export class Perfil {

  private readonly userDataService = inject(UserDataService);

  userData = computed(() => {
    const data = this.userDataService.userDataSignal();
    if (data) {
      return {
        nickname: data.nickname,
        avatar: data.avatar,
        level: data.level,
        experience: data.experience,
        coins: data.coins,
        streak: data.streak ?? 0,
        n_achievements: data.n_achievements,
        total_levels_completed: data.total_levels_completed
      };
    }
    return {
      nickname: "...",
      avatar: "",
      level: 0,
      experience: 0,
      coins: 0,
      streak: 0,
      n_achievements: 0,
      total_levels_completed: 0
    };
  });

  



}
