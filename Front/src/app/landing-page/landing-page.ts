import { Component, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-landing-page',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './landing-page.html',
  styleUrl: './landing-page.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LandingPageComponent {
  // Engagement section
  activeFeature = signal<'ranking' | 'logros' | 'racha' | null>(null);

  featureText = computed(() => {
    const feature = this.activeFeature();
    if (!feature) return null;
    
    const texts: Record<string, string> = {
      'ranking': 'Compite con tus amigos por el mejor puesto en el ranking.',
      'logros': 'Desbloquea insignias y demuestra tu dominio del código.',
      'racha': 'Intenta mantener la racha todo lo que puedas.'
    };
    return texts[feature];
  });

  // Card flip states (2 cards)
  cardFlipped = signal<boolean[]>([false, false]);

  showFeature(feature: 'ranking' | 'logros' | 'racha') {
    this.activeFeature.set(feature);
  }

  toggleCard(index: number) {
    this.cardFlipped.update(states => {
      const newStates = [...states];
      newStates[index] = !newStates[index];
      return newStates;
    });
  }
}
