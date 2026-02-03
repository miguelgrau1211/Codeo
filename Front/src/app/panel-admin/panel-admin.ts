import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-panel-admin',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './panel-admin.html',
  styleUrl: './panel-admin.css'
})
export class PanelAdmin {
  activeTab = signal<'users' | 'story' | 'roguelike'>('users');

  // MOCK DATA: Users
  users = signal([
    { id: 1, name: 'DevMaster_99', email: 'dev@codeo.app', role: 'admin', active: true },
    { id: 2, name: 'CyberWitch', email: 'witch@codeo.app', role: 'user', active: true },
    { id: 3, name: 'NullPointer', email: 'null@error.com', role: 'user', active: false },
    { id: 4, name: 'Pythonista', email: 'py@snake.org', role: 'user', active: true },
  ]);

  // MOCK DATA: Story Levels
  storyLevels = signal([
    { id: 1, title: 'El Despertar', chapter: 1, difficulty: 'Easy', xp: 100 },
    { id: 2, title: 'Bucles Temporales', chapter: 2, difficulty: 'Medium', xp: 250 },
    { id: 3, title: 'La Función Maestra', chapter: 3, difficulty: 'Hard', xp: 500 },
  ]);

  // MOCK DATA: Roguelike Levels (Challenges)
  roguelikeLevels = signal([
    { id: 101, name: 'Fibonacci Recursivo', tier: 'S', timeLimit: 120 },
    { id: 102, name: 'Ordenamiento Burbuja', tier: 'C', timeLimit: 60 },
    { id: 103, name: 'Busqueda Binaria', tier: 'A', timeLimit: 90 },
  ]);

  // View Helpers
  setTab(tab: 'users' | 'story' | 'roguelike') {
    this.activeTab.set(tab);
  }

  // MOCK ACTIONS
  deleteUser(id: number) {
    if(confirm('¿Estás seguro de eliminar este usuario?')) {
        this.users.update(list => list.filter(u => u.id !== id));
    }
  }

  toggleUserStatus(id: number) {
    this.users.update(list => list.map(u => u.id === id ? {...u, active: !u.active} : u));
  }

  deleteStoryLevel(id: number) {
    if(confirm('¿Borrar nivel?')) {
        this.storyLevels.update(list => list.filter(l => l.id !== id));
    }
  }

  deleteRoguelikeLevel(id: number) {
    if(confirm('¿Borrar desafío?')) {
        this.roguelikeLevels.update(list => list.filter(l => l.id !== id));
    }
  }
}
