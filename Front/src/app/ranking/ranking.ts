import { Component, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { RankingService, User } from '../services/ranking-service';
import { UserDataService } from '../services/user-data-service';

@Component({
  selector: 'app-ranking',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './ranking.html',
  styleUrl: './ranking.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Ranking {
  private readonly rankingService = inject(RankingService);
  private readonly userDataService = inject(UserDataService);

  // Datos del usuario desde caché global (cargados en el dashboard)
  userData = this.userDataService.userDataSignal;

  players = signal<User[]>([]);
  isLoading = signal(true);
  hasError = signal(false);

  topThree = computed(() => this.players().slice(0, 3));
  restOfPlayers = computed(() => this.players().slice(3));

  constructor() {
    // Asegura que userData está cargado (usa caché si ya existe)
    this.userDataService.getUserData().subscribe();

    this.rankingService.getRanking().subscribe({
      next: (data) => {
        this.players.set(data);
        this.isLoading.set(false);
      },
      error: (err) => {
        console.error('Error fetching ranking:', err);
        this.isLoading.set(false);
        this.hasError.set(true);
      }
    });
  }
}
