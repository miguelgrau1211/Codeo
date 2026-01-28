import { Component, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

interface UserStats {
  level: number;
  xp: number;
  xpToNextLevel: number;
  streak: number;
  coins: number;
}

interface Activity {
  id: number;
  type: 'complete' | 'achievement' | 'challenge';
  title: string;
  xpEarned: number;
  time: string;
}

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.html',
  styles: [],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DashboardComponent {
  // User State
  user = signal({
    name: 'DevMaster_99',
    avatar: 'https://api.dicebear.com/9.x/avataaars/svg?seed=Felix',
    role: 'Novato'
  });

  stats = signal<UserStats>({
    level: 5,
    xp: 2450,
    xpToNextLevel: 3000,
    streak: 7,
    coins: 150
  });

  // Computed Progress
  xpProgress = computed(() => {
    const { xp, xpToNextLevel } = this.stats();
    return (xp / xpToNextLevel) * 100;
  });

  // Recent Activity Data
  recentActivity = signal<Activity[]>([
    { id: 1, type: 'complete', title: 'Completado "Bucles For"', xpEarned: 150, time: 'Hace 2h' },
    { id: 2, type: 'achievement', title: 'Logro Desbloqueado: "Bug Hunter"', xpEarned: 300, time: 'Hace 5h' },
    { id: 3, type: 'challenge', title: 'Derrotaste al Boss "NullPointer"', xpEarned: 500, time: 'Ayer' }
  ]);

  // Sidebar State
  isSidebarOpen = signal(true);

  toggleSidebar() {
    this.isSidebarOpen.update(v => !v);
  }
}
