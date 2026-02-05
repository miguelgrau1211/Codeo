import { Component, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ProgresoHistoriaService } from '../services/progreso-historia-service';

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
export class DashboardComponent {
  constructor(private progresoHistoriaService: ProgresoHistoriaService) {}
  
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

  // Init logic
  ngOnInit() {
      if (!this.serviceProgreso()) {
          this.progresoHistoriaService.getProgresoHistoria().subscribe();
      }
  }


  // Sidebar State
  isSidebarOpen = signal(true);

  toggleSidebar() {
    this.isSidebarOpen.update(v => !v);
  }
}
