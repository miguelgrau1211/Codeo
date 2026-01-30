import { Component, signal, effect, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './header.html',
  styleUrl: './header.css'
})
export class Header {
  isDarkMode = signal(true);
  platformId = inject(PLATFORM_ID);

  constructor() {
    // Initialize theme based on localStorage or system preference
    if (isPlatformBrowser(this.platformId)) {
        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
            this.isDarkMode.set(true);
        } else {
            this.isDarkMode.set(false);
        }

        // Apply theme on simple effect
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
}
