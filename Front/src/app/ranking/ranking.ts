import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

interface Player {
  rank: number;
  name: string;
  xp: number;
  level: number;
  avatar: string;
  badges: string[];
}

@Component({
  selector: 'app-ranking',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './ranking.html',
  styleUrl: './ranking.css'
})
export class Ranking {
  players: Player[] = [
    { rank: 1, name: 'CyberWitch_99', xp: 15420, level: 42, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Cyber', badges: ['👑', '🔥'] },
    { rank: 2, name: 'CodeNinja_X', xp: 14200, level: 39, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Ninja', badges: ['⚡'] },
    { rank: 3, name: 'NullPointer', xp: 12850, level: 35, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Null', badges: ['🐛'] },
    { rank: 4, name: 'DevOps_Master', xp: 11000, level: 30, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=DevOps', badges: [] },
    { rank: 5, name: 'GitPushForce', xp: 10500, level: 28, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Git', badges: [] },
    { rank: 6, name: 'TypeScript_God', xp: 9800, level: 25, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=TS', badges: [] },
    { rank: 7, name: 'Pythonista', xp: 9200, level: 24, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Py', badges: [] },
  ];

  get topThree() {
    return this.players.slice(0, 3);
  }

  get restOfPlayers() {
    return this.players.slice(3);
  }
}
