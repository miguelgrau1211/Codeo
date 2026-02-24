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

  // Modal States
  showBuyModal = signal(false);
  selectedTema = signal<Tema | null>(null);
  isBuying = signal(false);
  buySuccess = signal(false);
  buyError = signal<string | null>(null);
  
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
      this.misTemas.set(temas.map(t => Number(t.id)));
    });
  }

  isOwned(temaId: any): boolean {
    return this.misTemas().includes(Number(temaId));
  }

  comprarTema(tema: Tema) {
    if (this.userCoins() < tema.precio) {
      this.buyError.set('No tienes suficientes monedas para este tema.');
      this.buySuccess.set(false);
      this.selectedTema.set(tema);
      this.showBuyModal.set(true);
      return;
    }

    this.selectedTema.set(tema);
    this.buyError.set(null);
    this.buySuccess.set(false);
    this.showBuyModal.set(true);
  }

  confirmPurchase() {
    const tema = this.selectedTema();
    if (!tema) return;

    this.isBuying.set(true);
    this.themeService.comprarTema(tema.id).subscribe({
      next: () => {
        this.misTemas.update(prev => [...prev, tema.id]);
        this.userDataService.getUserData(true).subscribe(); // Refresh coins
        this.buySuccess.set(true);
        this.isBuying.set(false);
        
        // Auto-close success modal after 2 seconds
        setTimeout(() => {
          if (this.buySuccess()) {
            this.closeModal();
          }
        }, 2500);
      },
      error: (err) => {
        this.buyError.set(err.error?.message || 'Error al procesar la compra. Inténtalo de nuevo.');
        this.isBuying.set(false);
      }
    });
  }

  closeModal() {
    this.showBuyModal.set(false);
    setTimeout(() => {
      this.selectedTema.set(null);
      this.buySuccess.set(false);
      this.buyError.set(null);
      this.isBuying.set(false);
    }, 300);
  }

  activarTema(tema: Tema) {
    this.themeService.activarTema(tema.id).subscribe({
      next: () => {
        // Theme applied via service effect
      },
      error: (err) => {
        this.buyError.set(err.error?.message || 'Error al activar el tema');
        this.showBuyModal.set(true);
      }
    });
  }
}
