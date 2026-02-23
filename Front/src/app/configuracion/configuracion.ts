import { Component, signal, effect, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-configuracion',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule],
  templateUrl: './configuracion.html',
  styleUrl: './configuracion.css'
})
export class Configuracion {
  platformId = inject(PLATFORM_ID);
  
  // Mock User Settings
  userSettings = signal({
    notifications: true,
    sound: true,
    music: false,
    language: 'es',
    autoSave: true
  });

  constructor() {
    // Other initializations...
  }

  toggleSetting(key: keyof typeof this.userSettings) {
    // This is a bit tricky with signals of objects, simplified for now
    /* 
       Note: In a real app with nested signals, you might structure this differently.
       For this mock, we just won't deeply mutate the signal for the UI toggles simple state.
       Actually, let's just use local inputs in the HTML or simple methods.
       We will bind the toggles in HTML to update a local mutable object or use update.
    */
  }
}
