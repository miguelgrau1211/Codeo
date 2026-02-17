import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { RankingService } from '../services/ranking-service';
import { User } from '../services/ranking-service';



@Component({
  selector: 'app-ranking',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './ranking.html',
  styleUrl: './ranking.css'
})
export class Ranking {
  isLoading = true;

  players= signal<User[]>([]);

  constructor(private rankingService: RankingService) { 
      this.rankingService.getRanking().subscribe({
        next: (data) => {
          this.players.set(data);
          this.isLoading = false;
        },
        error: (err) => {
          console.error('Error fetching ranking:', err);
          this.isLoading = false;
        }
      });
  }


  
  get topThree() {
    return this.players().slice(0, 3);
  }

  get restOfPlayers() {
    return this.players().slice(3);
  }
}
