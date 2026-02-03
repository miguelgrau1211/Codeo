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
  
  // Theme State
  isDarkMode = signal(true);
  
  // Mock User Settings
  userSettings = signal({
    notifications: true,
    sound: true,
    music: false,
    language: 'es',
    autoSave: true
  });

  constructor() {
    // Initialize theme state from system/storage
    if (isPlatformBrowser(this.platformId)) {
      const savedTheme = localStorage.getItem('theme');
      const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      this.isDarkMode.set(savedTheme === 'dark' || (!savedTheme && systemDark));

      // Effect to apply theme changes
      effect(() => {
        const isDark = this.isDarkMode();
        if (isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
      });
    }
  }

  toggleTheme() {
    this.isDarkMode.update(prev => !prev);
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
