import { Component, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { TranslatePipe } from '../../pipes/translate.pipe';

@Component({
  selector: 'app-landing-page',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslatePipe],
  templateUrl: './landing-page.component.html',
  styleUrl: './landing-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LandingPageComponent {
  // Engagement section
  activeFeature = signal<'ranking' | 'logros' | 'racha' | null>(null);

  featureText = computed(() => {
    const feature = this.activeFeature();
    if (!feature) return null;

    const texts: Record<string, string> = {
      'ranking': 'LANDING.FEAT_RANKING_DESC',
      'logros': 'LANDING.FEAT_LOGROS_DESC',
      'racha': 'LANDING.FEAT_RACHA_DESC'
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




