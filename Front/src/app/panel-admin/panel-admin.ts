import { Component, signal, inject, OnInit, computed, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AdminService, User, DashboardStats, AdminLog } from '../services/admin-service';
import { AdminStatCard } from './components/admin-stat-card/admin-stat-card';
import { Subject, debounceTime, distinctUntilChanged } from 'rxjs';

@Component({
  selector: 'app-panel-admin',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, AdminStatCard],
  templateUrl: './panel-admin.html',
  styleUrl: './panel-admin.css'
})
export class PanelAdmin implements OnInit, OnDestroy {
  private adminService = inject(AdminService);

  activeTab = signal<'users' | 'story' | 'roguelike' | 'logs'>('users');
  isLoading = signal(false);

  // Stats
  stats = signal<DashboardStats>({
    total_users: 0,
    active_users_24h: 0,
    total_runs: 0,
    success_rate: 0
  });

  // Logs
  logs = signal<AdminLog[]>([]);
  logsCurrentPage = signal(1);
  logsTotalPages = signal(1);
  logsActionFilter = signal('');

  // Paginación y filtros de usuarios
  users = signal<User[]>([]);       // La lista de usuarios que se ve en pantalla
  currentPage = signal(1);          // En qué página estás ahora (ej. Página 1)
  totalPages = signal(1);           // Cuántas páginas hay en total (ej. 10)
  totalUsers = signal(0);           // Cuántos usuarios hay en total en la base de datos
  searchTerm = signal('');
  sortBy = signal<string>('id');
  sortOrder = signal<'asc' | 'desc'>('desc');

  private searchSubject = new Subject<string>();

  ngOnInit() {
    this.loadUsers();
    this.loadStats();

    // Configurar debounce para la búsqueda
    this.searchSubject.pipe(
      debounceTime(400),
      distinctUntilChanged()
    ).subscribe(term => {
      this.searchTerm.set(term);
      this.loadUsers(1);
    });
  }

  ngOnDestroy() {
    this.searchSubject.complete();
  }

  loadUsers(page: number = 1) {
    this.isLoading.set(true); // Muestra el spinner de carga
    // Pide los datos al backend
    this.adminService.getUsers(page, this.searchTerm(), this.sortBy(), this.sortOrder()).subscribe({
      next: (response) => {
        this.users.set(response.data);           // Guarda los usuarios recibidos
        this.currentPage.set(response.current_page); // Actualiza la página actual real
        this.totalPages.set(response.last_page);     // Actualiza el total de páginas
        this.totalUsers.set(response.total);
        this.isLoading.set(false);
      },
      error: (err) => {
        console.error('Error loading users', err);
        this.isLoading.set(false);
      }
    });
  }

  onSearchChange(event: any) {
    this.searchSubject.next(event.target.value);
  }

  toggleSort(field: string) {
    if (this.sortBy() === field) {
      // Si ya ordenamos por este campo, cambiamos el orden
      this.sortOrder.update(current => current === 'asc' ? 'desc' : 'asc');
    } else {
      // Si es un campo nuevo, ponemos desc por defecto
      this.sortBy.set(field);
      this.sortOrder.set('desc');
    }
    this.loadUsers(1);
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.loadUsers(this.currentPage() + 1);
    }
  }

  prevPage() {
    if (this.currentPage() > 1) {
      this.loadUsers(this.currentPage() - 1);
    }
  }

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
  setTab(tab: 'users' | 'story' | 'roguelike' | 'logs') {
    this.activeTab.set(tab);
    if (tab === 'logs') {
      this.loadLogs();
    }
  }

  loadStats() {
    this.adminService.getStats().subscribe({
      next: (res) => this.stats.set(res),
      error: (err) => console.error('Error loading stats', err)
    });
  }

  loadLogs(page: number = 1) {
    this.adminService.getLogs(page, this.logsActionFilter()).subscribe({
      next: (res) => {
        this.logs.set(res.data);
        this.logsCurrentPage.set(res.current_page);
        this.logsTotalPages.set(res.last_page);
      },
      error: (err) => console.error('Error loading logs', err)
    });
  }

  nextLogPage() {
    if (this.logsCurrentPage() < this.logsTotalPages()) {
      this.loadLogs(this.logsCurrentPage() + 1);
    }
  }

  prevLogPage() {
    if (this.logsCurrentPage() > 1) {
      this.loadLogs(this.logsCurrentPage() - 1);
    }
  }

  // ACTIONS

  toggleUserStatus(id: number) {
    this.adminService.toggleUserStatus(id).subscribe({
      next: (res) => {
        alert(res.message); // Notificar al usuario
        this.loadUsers(this.currentPage());
      },
      error: (err) => {
        console.error('Error toggling user status', err);
        const errorMessage = err.error?.message || 'Error al cambiar estado del usuario';
        alert(errorMessage);
      }
    });
  }

  deleteStoryLevel(id: number) {
    if (confirm('¿Borrar nivel?')) {
      this.storyLevels.update(list => list.filter(l => l.id !== id));
    }
  }

  deleteRoguelikeLevel(id: number) {
    if (confirm('¿Borrar desafío?')) {
      this.roguelikeLevels.update(list => list.filter(l => l.id !== id));
    }
  }
}
