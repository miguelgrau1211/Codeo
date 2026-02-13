import { Component, signal, computed, inject, ChangeDetectionStrategy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ProgresoHistoriaService } from '../services/progreso-historia-service';
import { UserDataService } from '../services/user-data-service';

interface Activity {
  id: number;
  type: 'complete' | 'achievement' | 'challenge';
  title: string;
  xpEarned: number;
  time: string;
}

interface StatsHistoria {
  actual_level: number;
  total_levels: number;
  lvls_progress: string;
  titulo: string;
}

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.html',
  styles: [],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DashboardComponent implements OnInit {
  private readonly progresoHistoriaService = inject(ProgresoHistoriaService);
  private readonly userDataService = inject(UserDataService);

  // Recent Activity Data
  recentActivity = signal<Activity[]>([
    { id: 1, type: 'complete', title: 'Completado "Bucles For"', xpEarned: 150, time: 'Hace 2h' },
    { id: 2, type: 'achievement', title: 'Logro Desbloqueado: "Bug Hunter"', xpEarned: 300, time: 'Hace 5h' },
    { id: 3, type: 'challenge', title: 'Derrotaste al Boss "NullPointer"', xpEarned: 500, time: 'Ayer' }
  ]);

  // Refactored logic:
  serviceProgreso = this.progresoHistoriaService.progresoSignal;

  stats_historia = computed(() => {
      const data = this.serviceProgreso();
      if (data) {
          return {
              actual_level: data.stats.total_niveles,
              total_levels: data.stats.completados,
              lvls_progress: data.stats.porcentaje_progreso,
              titulo: data.stats.titulo_ultimo_nivel
          };
      }
      return {
          actual_level: 0,
          total_levels: 0,
          lvls_progress: "0%",
          titulo: "Cargando..."
      };
  });

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

  // Init logic
  ngOnInit() {
      if (!this.serviceProgreso()) {
          this.progresoHistoriaService.getProgresoHistoria().subscribe();
      }
      if (!this.userDataService.userDataSignal()) {
        this.userDataService.getUserData().subscribe();
      }
  }

  // Sidebar State
  isSidebarOpen = signal(true);

  toggleSidebar() {
    this.isSidebarOpen.update(v => !v);
  }
}
