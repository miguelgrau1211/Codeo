import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LogroComponent } from './logro/logro';

@Component({
    selector: 'app-logros',
    standalone: true,
    imports: [CommonModule, LogroComponent],
    templateUrl: './logros.html',
    styleUrls: ['./logros.css']
})
export class LogrosComponent {
    easterEggClicks = 0;
    isEasterEggActive = false;
    showConfetti = false;

    triggerEasterEgg() {
        this.easterEggClicks++;
        if (this.easterEggClicks >= 5) {
            this.isEasterEggActive = !this.isEasterEggActive;
            this.easterEggClicks = 0; // Reset

            if (this.isEasterEggActive) {
                this.showConfetti = true;
                setTimeout(() => this.showConfetti = false, 3000);
            }
        }
    }
}