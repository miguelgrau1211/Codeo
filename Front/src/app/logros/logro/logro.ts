import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from '../../pipes/translate.pipe';

@Component({
    selector: 'app-logro',
    standalone: true,
    imports: [CommonModule, TranslatePipe],
    templateUrl: './logro.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    styles: []
})
export class LogroComponent {
    @Input() nombre = '';
    @Input() descripcion = '';
    @Input() iconoUrl: string | null = null;
    @Input() rareza = 'especial';
    @Input() desbloqueado = false;
    @Input() fechaObtencion: string | null = null;

    /** Rarity label as i18n key */
    get rarezaLabel(): string {
        const map: Record<string, string> = {
            especial: 'ACHIEVEMENTS.RARITY_SPECIAL',
            raro: 'ACHIEVEMENTS.RARITY_RARE',
            epico: 'ACHIEVEMENTS.RARITY_EPIC',
            legendario: 'ACHIEVEMENTS.RARITY_LEGENDARY',
            celestial: 'ACHIEVEMENTS.RARITY_CELESTIAL',
        };
        return map[this.rareza] ?? this.rareza;
    }

    get rarezaGradient(): string {
        const map: Record<string, string> = {
            especial: 'from-emerald-400 to-teal-500',
            raro: 'from-blue-400 to-indigo-400',
            epico: 'from-purple-400 to-pink-400',
            legendario: 'from-yellow-400 to-orange-400',
            celestial: 'from-cyan-300 to-fuchsia-400',
        };
        return map[this.rareza] ?? 'from-slate-400 to-slate-500';
    }

    get rarezaBorderGlow(): string {
        const map: Record<string, string> = {
            especial: 'shadow-[0_0_12px_rgba(16,185,129,0.3)] border-emerald-500/30',
            raro: 'shadow-[0_0_12px_rgba(99,102,241,0.3)] border-indigo-500/30',
            epico: 'shadow-[0_0_15px_rgba(168,85,247,0.4)] border-purple-500/30',
            legendario: 'shadow-[0_0_18px_rgba(245,158,11,0.4)] border-amber-500/40',
            celestial: 'shadow-[0_0_22px_rgba(6,182,212,0.5)] border-cyan-400/40',
        };
        return map[this.rareza] ?? '';
    }

    get cardHoverGlow(): string {
        const map: Record<string, string> = {
            especial: 'group-hover:from-emerald-500/40 group-hover:to-teal-500/40',
            raro: 'group-hover:from-blue-500/40 group-hover:to-indigo-500/40',
            epico: 'group-hover:from-purple-500/50 group-hover:to-pink-500/50',
            legendario: 'group-hover:from-yellow-500/50 group-hover:to-amber-500/50',
            celestial: 'group-hover:from-cyan-400/50 group-hover:to-fuchsia-400/50',
        };
        return map[this.rareza] ?? 'group-hover:from-purple-500/50 group-hover:to-pink-500/50';
    }
}