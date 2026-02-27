import { TranslatePipe } from '../../pipes/translate.pipe';
import { Component, signal, computed, inject, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { LogroComponent } from './logro/logro.component';
import { LogrosService } from '../../services/logros.service';
import { NotificationService } from '../../services/notification.service';
import { Logro, PorcentajeLogrosResponse } from '../../models/achievement.model';

type FiltroLogros = 'todos' | 'desbloqueados' | 'bloqueados';
/**
 * Componente de logros (achievements).
 *
 * Muestra la colección completa de logros del juego con:
 * - Filtros: todos, desbloqueados, bloqueados.
 * - Barra de progreso con porcentaje de completitud.
 * - Tarjetas individuales por logro (delegadas al componente LogroComponent).
 * - Easter egg oculto que se activa con 5 clics rápidos.
 */
@Component({
    selector: 'app-logros',
    standalone: true,
    imports: [CommonModule, RouterModule, LogroComponent, TranslatePipe],
    templateUrl: './logros.component.html',
    styleUrl: './logros.component.css',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LogrosComponent implements OnInit {
    private readonly logrosService = inject(LogrosService);
    protected readonly notificationService = inject(NotificationService);

    // ── State ───────────────────────────────────────────────
    readonly logros = signal<Logro[]>([]);
    readonly stats = signal<PorcentajeLogrosResponse | null>(null);
    readonly loading = signal(true);
    readonly error = signal<string | null>(null);
    readonly filtroActivo = signal<FiltroLogros>('todos');

    // ── Easter Egg ──────────────────────────────────────────
    easterEggClicks = 0;
    isEasterEggActive = false;
    showConfetti = false;

    // ── Computed ─────────────────────────────────────────────
    readonly logrosFiltrados = computed(() => {
        const filtro = this.filtroActivo();
        const todos = this.logros();

        switch (filtro) {
            case 'desbloqueados':
                return todos.filter(l => l.desbloqueado);
            case 'bloqueados':
                return todos.filter(l => !l.desbloqueado);
            default:
                return todos;
        }
    });

    readonly totalDesbloqueados = computed(() =>
        this.logros().filter(l => l.desbloqueado).length
    );

    readonly totalLogros = computed(() => this.logros().length);

    readonly porcentaje = computed(() => {
        const s = this.stats();
        return s ? Math.round(s.porcentaje * 10) / 10 : 0;
    });

    // ── Lifecycle ───────────────────────────────────────────
    ngOnInit(): void {
        this.cargarLogros();
    }

    // ── Methods ──────────────────────────────────────────────
    cargarLogros(): void {
        this.loading.set(true);
        this.error.set(null);

        this.logrosService.getLogrosDesbloqueados().subscribe({
            next: (res) => {
                this.logros.set(res.lista_completa);
                this.loading.set(false);
            },
            error: (err) => {
                console.error('Error cargando logros:', err);
                this.error.set('ACHIEVEMENTS.ERROR_LOADING');
                this.loading.set(false);
            },
        });

        this.logrosService.getPorcentajeLogros().subscribe({
            next: (res) => this.stats.set(res),
            error: (err) => console.error('Error cargando stats:', err),
        });
    }

    setFiltro(filtro: FiltroLogros): void {
        this.filtroActivo.set(filtro);
    }

    trackByLogro(index: number, logro: Logro): number {
        return logro.id;
    }

    triggerEasterEgg(): void {
        this.easterEggClicks++;
        if (this.easterEggClicks >= 5) {
            this.isEasterEggActive = !this.isEasterEggActive;
            this.easterEggClicks = 0;

            if (this.isEasterEggActive) {
                this.showConfetti = true;
                setTimeout(() => (this.showConfetti = false), 3000);

                // Llamar al backend para validar el logro
                this.logrosService.checkEasterEggAchievement().subscribe({
                    next: (res) => {
                        if (res.nuevos_logros && res.nuevos_logros.length > 0) {
                            // Importar localmente el inyector no se puede, por lo que asumo NotificationService inyectado si es posible o recargar.
                            // Para mantener local la dependencia, usaremos NotificationService inyectado en la clase.
                            this.notificationService.showAchievement(res.nuevos_logros[0]);
                            this.cargarLogros(); // Para actualizar la UI
                        }
                    },
                    error: (err) => console.error('Error procesando easter egg', err)
                });
            }
        }
    }
}



