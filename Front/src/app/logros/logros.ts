import { Component, signal, computed, inject, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { LogroComponent } from './logro/logro';
import { LogrosService, Logro, PorcentajeLogrosResponse } from '../services/logros-service';

type FiltroLogros = 'todos' | 'desbloqueados' | 'bloqueados';

@Component({
    selector: 'app-logros',
    standalone: true,
    imports: [CommonModule, RouterModule, LogroComponent],
    templateUrl: './logros.html',
    styleUrls: ['./logros.css'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LogrosComponent implements OnInit {
    private readonly logrosService = inject(LogrosService);

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
        return s ? s.porcentaje : 0;
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
                this.error.set('No se pudieron cargar los logros. Inténtalo de nuevo.');
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
            }
        }
    }
}