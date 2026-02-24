import { Component, inject, OnInit, signal, ChangeDetectionStrategy, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ThemeService, Tema } from '../services/theme-service';
import { UserDataService } from '../services/user-data-service';
import { TranslatePipe } from '../pipes/translate.pipe';

@Component({
  selector: 'app-tienda-temas',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  templateUrl: './tienda-temas.component.html',
  styleUrl: './tienda-temas.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TiendaTemasComponent implements OnInit {
  private themeService = inject(ThemeService);
  private userDataService = inject(UserDataService);

  // States
  temasDisponibles = signal<Tema[]>([]);
  misTemas = signal<number[]>([]); // Array of IDs
  isLoading = signal(true);

  // Computed
  userCoins = computed(() => this.userDataService.userDataSignal()?.coins ?? 0);
  activeThemeId = computed(() => this.themeService.currentTheme()?.id);

  ngOnInit() {
    this.loadData();
  }

  loadData() {
    this.isLoading.set(true);

    // Initial user data load if not loaded
    if (!this.userDataService.isLoaded()) {
      this.userDataService.getUserData().subscribe();
    }

    // Load available themes
    this.themeService.getTemas().subscribe(temas => {
      this.temasDisponibles.set(temas);
      this.isLoading.set(false);
    });

    // Load owned themes
    this.themeService.getMisTemas().subscribe(temas => {
      this.misTemas.set(temas.map(t => t.id));
    });
  }

  isOwned(temaId: number): boolean {
    return this.misTemas().includes(temaId);
  }

  comprarTema(tema: Tema) {
    if (this.userCoins() < tema.precio) {
      alert('¡No tienes suficientes monedas!');
      return;
    }

    if (confirm(`¿Quieres comprar el tema "${tema.nombre}" por ${tema.precio} monedas?`)) {
      this.themeService.comprarTema(tema.id).subscribe({
        next: () => {
          this.misTemas.update(prev => [...prev, tema.id]);
          this.userDataService.getUserData(true).subscribe(); // Refresh coins
          alert('¡Tema comprado con éxito!');
        },
        error: (err) => alert(err.error?.message || 'Error al comprar el tema')
      });
    }
  }

  activarTema(tema: Tema) {
    this.themeService.activarTema(tema.id).subscribe({
      next: () => {
        // Theme applied via service effect
      },
      error: (err) => alert(err.error?.message || 'Error al activar el tema')
    });
  }
}
