import { Component, computed, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { UserDataService, ActivityItem } from '../services/user-data-service';



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
        story_levels_completed: data.story_levels_completed,
        total_story_levels: data.total_story_levels,
        last_story_level_title: data.last_story_level_title,
        roguelike_levels_played: data.roguelike_levels_played,
        subscription_date: data.subscription_date,
        rank: data.rank
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
      story_levels_completed: 0,
      total_story_levels: 0,
      last_story_level_title: "",
      roguelike_levels_played: 0,
      subscription_date: "",
      rank: 0
    };


  });


  experiencePercentage = computed(() => {
    const userData = this.userData();
    if (userData) {
      const levelExperience = (userData.level - 1) * 500;
      const percentage = (userData.experience - levelExperience) / 500 * 100;
      return Math.min(percentage, 100);
    }
    return 0;
  });

  totalStoryLevels = computed(() => this.userData().total_story_levels || 1);

  recentActivity = signal<ActivityItem[]>([]);
  isLoadingActivity = signal(true);

  // Modal State
  isEditModalOpen = signal(false);
  editNickname = signal('');
  editAvatarUrl = signal('');
  isSaving = signal(false);
  saveFeedback = signal<string | null>(null);

  constructor() {
    this.userDataService.getUserData().subscribe();
    this.userDataService.getMiPosicionRanking().subscribe();
    this.userDataService.getRecentActivity().subscribe({
      next: (data) => {
        this.recentActivity.set(data);
        this.isLoadingActivity.set(false);
      },
      error: () => this.isLoadingActivity.set(false)
    });
  }

  openEditModal() {
    const user = this.userData();
    this.editNickname.set(user.nickname);
    this.editAvatarUrl.set(user.avatar);
    this.isEditModalOpen.set(true);
    this.saveFeedback.set(null);
  }

  closeEditModal() {
    this.isEditModalOpen.set(false);
  }

  generateRandomAvatar() {
    const seeds = ['Felix', 'Aneka', 'Zoe', 'Bear', 'Tiger', 'Leo', 'Max', 'Luna', 'Bella', 'Charlie', 'Milo', 'Simba', 'Coco', 'Rocky'];
    const randomSeed = seeds[Math.floor(Math.random() * seeds.length)] + Math.floor(Math.random() * 1000);
    const newUrl = `https://api.dicebear.com/7.x/avataaars/svg?seed=${randomSeed}`;
    this.editAvatarUrl.set(newUrl);
  }

  saveProfile() {
    if (this.isSaving()) return;

    this.isSaving.set(true);
    const data = {
      nickname: this.editNickname(),
      avatar_url: this.editAvatarUrl()
    };

    this.userDataService.updateUser(data).subscribe({
      next: () => {
        this.isSaving.set(false);
        this.saveFeedback.set('¡Perfil actualizado con éxito!');
        setTimeout(() => {
          this.closeEditModal();
          this.saveFeedback.set(null);
        }, 1500);
      },
      error: (err) => {
        this.isSaving.set(false);
        this.saveFeedback.set(err.error?.message || 'Error al actualizar perfil.');
      }
    });
  }

  // Inputs binding helpers
  updateNickname(event: Event) {
    this.editNickname.set((event.target as HTMLInputElement).value);
  }

  updateAvatarUrl(event: Event) {
    this.editAvatarUrl.set((event.target as HTMLInputElement).value);
  }
}
