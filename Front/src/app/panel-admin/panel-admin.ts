import { Component, signal, inject, OnInit, computed, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AdminService, User, DashboardStats, AdminLog, StoryLevel, RoguelikeLevel } from '../services/admin-service';
import { AdminStatCard } from './components/admin-stat-card/admin-stat-card';
import { Subject, debounceTime, distinctUntilChanged } from 'rxjs';
import { LevelEditorModalComponent } from '../components/level-editor-modal/level-editor-modal.component';

@Component({
  selector: 'app-panel-admin',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, AdminStatCard, LevelEditorModalComponent],
  templateUrl: './panel-admin.html',
  styleUrl: './panel-admin.css',
})
export class PanelAdmin implements OnInit, OnDestroy {
  private adminService = inject(AdminService);
  // Loading states per section
  isStoryLoading = signal(false);
  isRoguelikeLoading = signal(false);

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
  // Editor Modal State
  isEditorOpen = signal(false);
  editorType: 'story' | 'roguelike' = 'story';
  editorData: any = null; // null for create, object for edit

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

  // Story Levels
  storyLevels = signal<StoryLevel[]>([]);
  storyLevelsDisabled = signal<StoryLevel[]>([]);
  storyPage = signal(this.adminService.storyState().page || 1);
  storyTotalPages = signal(this.adminService.storyState().last_page || 1);

  // Roguelike Levels
  roguelikeLevels = signal<RoguelikeLevel[]>([]);
  roguelikeLevelsDisabled = signal<RoguelikeLevel[]>([]);
  roguelikePage = signal(this.adminService.roguelikeState().page || 1);
  roguelikeTotalPages = signal(this.adminService.roguelikeState().last_page || 1);

  // View Helpers
  setTab(tab: 'users' | 'story' | 'roguelike' | 'logs') {
    this.activeTab.set(tab);
    if (tab === 'logs') {
      this.loadLogs();
    } else if (tab === 'story') {
      this.loadStoryLevels();
    } else if (tab === 'roguelike') {
      this.loadRoguelikeLevels();
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

  loadStoryLevels(page: number = this.storyPage()) {
    const state = this.adminService.storyState();
    if (state.loaded && state.page === page && state.data.length > 0) {
      // Use cached data
      this.storyLevels.set(state.data);
      this.storyPage.set(state.page);
      this.storyTotalPages.set(state.last_page);
      this.isStoryLoading.set(false);
    } else {
      // Fetch fresh data
      this.isStoryLoading.set(true);
      this.adminService.getStoryLevels(page).subscribe({
        next: (res) => {
          this.storyLevels.set(res.data);
          this.storyPage.set(res.current_page);
          this.storyTotalPages.set(res.last_page);

          // Update cache
          this.adminService.storyState.set({
            data: res.data,
            page: res.current_page,
            total: res.total,
            last_page: res.last_page,
            loaded: true
          });

          this.isStoryLoading.set(false);
        },
        error: (err) => {
          console.error('Error loading story levels', err);
          this.isStoryLoading.set(false);
        }
      });
    }

    // Always fetch disabled levels for now (could be cached similarly if needed)
    this.adminService.getStoryLevelsDesactivados().subscribe({
      next: (res) => this.storyLevelsDisabled.set(res),
      error: (err) => console.error('Error loading disabled story levels', err)
    });
  }

  nextStoryPage() {
    if (this.storyPage() < this.storyTotalPages()) {
      this.loadStoryLevels(this.storyPage() + 1);
    }
  }

  prevStoryPage() {
    if (this.storyPage() > 1) {
      this.loadStoryLevels(this.storyPage() - 1);
    }
  }

  loadRoguelikeLevels(page: number = this.roguelikePage()) {
    const state = this.adminService.roguelikeState();
    if (state.loaded && state.page === page && state.data.length > 0) {
      this.roguelikeLevels.set(state.data);
      this.roguelikePage.set(state.page);
      this.roguelikeTotalPages.set(state.last_page);
      this.isRoguelikeLoading.set(false);
    } else {
      this.isRoguelikeLoading.set(true);
      this.adminService.getRoguelikeLevels(page).subscribe({
        next: (res) => {
          this.roguelikeLevels.set(res.data);
          this.roguelikePage.set(res.current_page);
          this.roguelikeTotalPages.set(res.last_page);

          // Update cache
          this.adminService.roguelikeState.set({
            data: res.data,
            page: res.current_page,
            total: res.total,
            last_page: res.last_page,
            loaded: true
          });

          this.isRoguelikeLoading.set(false);
        },
        error: (err) => {
          console.error('Error loading roguelike levels', err);
          this.isRoguelikeLoading.set(false);
        }
      });
    }

    this.adminService.getRoguelikeLevelsDesactivados().subscribe({
      next: (res) => this.roguelikeLevelsDisabled.set(res),
      error: (err) => console.error('Error loading disabled roguelike levels', err)
    });
  }

  nextRoguelikePage() {
    if (this.roguelikePage() < this.roguelikeTotalPages()) {
      this.loadRoguelikeLevels(this.roguelikePage() + 1);
    }
  }

  prevRoguelikePage() {
    if (this.roguelikePage() > 1) {
      this.loadRoguelikeLevels(this.roguelikePage() - 1);
    }
  }

  toggleStoryStatus(id: number) {
    if (!confirm('¿Cambiar estado del nivel?')) return;

    this.adminService.toggleStoryLevelStatus(id).subscribe({
      next: (res) => {
        alert(res.message);
        // Invalidate cache
        this.adminService.storyState.update(s => ({ ...s, loaded: false }));
        this.loadStoryLevels(this.storyPage());
      },
      error: (err) => alert(err.error?.message || 'Error toggling story level')
    });
  }

  toggleRoguelikeStatus(id: number) {
    if (!confirm('¿Cambiar estado del nivel?')) return;

    this.adminService.toggleRoguelikeLevelStatus(id).subscribe({
      next: (res) => {
        alert(res.message);
        // Invalidate cache
        this.adminService.roguelikeState.update(s => ({ ...s, loaded: false }));
        this.loadRoguelikeLevels(this.roguelikePage());
      },
      error: (err) => alert(err.error?.message || 'Error toggling roguelike level')
    });
  }

  // Métodos del editor

  openEditor(type: 'story' | 'roguelike', data: any = null) {
    this.editorType = type;
    if (data) {
      // Si estamos editando, pedir los datos completos al backend
      if (type === 'story') {
        this.adminService.getStoryLevel(data.id).subscribe({
          next: (fullData) => {
            this.editorData = fullData;
            this.isEditorOpen.set(true);
          },
          error: (err) => alert('Error al cargar datos del nivel: ' + (err.error?.message || err.message))
        });
      } else {
        this.adminService.getRoguelikeLevel(data.id).subscribe({
          next: (fullData) => {
            this.editorData = fullData;
            this.isEditorOpen.set(true);
          },
          error: (err) => alert('Error al cargar datos del desafío: ' + (err.error?.message || err.message))
        });
      }
    } else {
      // Crear nuevo
      this.editorData = null;
      this.isEditorOpen.set(true);
    }
  }

  closeEditor() {
    this.isEditorOpen.set(false);
    this.editorData = null;
  }

  onSaveLevel(data: any) {
    if (this.editorType === 'story') {
      if (this.editorData) {
        // Update
        this.adminService.updateStoryLevel(this.editorData.id, data).subscribe({
          next: () => {
            alert('Nivel actualizado');
            this.closeEditor();
            // Invalidate cache
            this.adminService.storyState.update(s => ({ ...s, loaded: false }));
            this.loadStoryLevels(this.storyPage());
          },
          error: err => alert('Error al actualizar: ' + (err.error?.message || err.message))
        });
      } else {
        // Create
        this.adminService.createStoryLevel(data).subscribe({
          next: () => {
            alert('Nivel creado');
            this.closeEditor();
            // Invalidate cache
            this.adminService.storyState.update(s => ({ ...s, loaded: false }));
            this.loadStoryLevels(this.storyPage());
          },
          error: err => alert('Error al crear: ' + (err.error?.message || err.message))
        });
      }
    } else {
      // Roguelike
      if (this.editorData) {
        // Update
        this.adminService.updateRoguelikeLevel(this.editorData.id, data).subscribe({
          next: () => {
            alert('Desafío actualizado');
            this.closeEditor();
            // Invalidate cache
            this.adminService.roguelikeState.update(s => ({ ...s, loaded: false }));
            this.loadRoguelikeLevels(this.roguelikePage());
          },
          error: err => alert('Error al actualizar: ' + (err.error?.message || err.message))
        });
      } else {
        // Create
        this.adminService.createRoguelikeLevel(data).subscribe({
          next: () => {
            alert('Desafío creado');
            this.closeEditor();
            // Invalidate cache
            this.adminService.roguelikeState.update(s => ({ ...s, loaded: false }));
            this.loadRoguelikeLevels(this.roguelikePage());
          },
          error: err => alert('Error al crear: ' + (err.error?.message || err.message))
        });
      }
    }
  }
}
