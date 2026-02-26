import { TranslatePipe } from '../../pipes/translate.pipe';
import { Component, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { RankingService } from '../../services/ranking.service';
import { UserDataService } from '../../services/user-data.service';
import { RankingUser } from '../../models/ranking.model';
/**
 * Componente de ranking global.
 *
 * Muestra la clasificación de usuarios ordenados por experiencia.
 * Los 3 primeros se muestran en un podio destacado; el resto en lista.
 * Los datos se cargan del RankingService y los datos del usuario actual
 * se leen desde la caché global del UserDataService.
 */
@Component({
  selector: 'app-ranking',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslatePipe],
  templateUrl: './ranking.component.html',
  styleUrl: './ranking.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class RankingComponent {
  private readonly rankingService = inject(RankingService);
  private readonly userDataService = inject(UserDataService);

  // Datos del usuario desde caché global (cargados en el dashboard)
  userData = this.userDataService.userDataSignal;

  players = signal<RankingUser[]>([]);
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



