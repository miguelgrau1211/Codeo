import { Component, signal, effect, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { AuthService } from '../services/auth-service';
@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './header.html',
  styleUrl: './header.css'
})
export class Header {
  authService = inject(AuthService);
  platformId = inject(PLATFORM_ID);
  
  // States
  isDarkMode = signal(true);
  isLoggedIn = signal(false);
  
  // Computed signal directly from service
  isAdmin = this.authService.isAdminSignal;

  router = inject(Router);

  constructor() {
    if (isPlatformBrowser(this.platformId)) {
        this.checkAuthStatus();
        this.initTheme();

        // Listen for route changes to update auth status dynamically
        this.router.events.subscribe(event => {
            if (event instanceof NavigationEnd) {
                this.checkAuthStatus();
            }
        });
    }
  }

  checkAuthStatus() {
    const token = sessionStorage.getItem('token');
    
    if (token) {
        this.isLoggedIn.set(true);
        // We don't verify here because Login already did it, 
        // OR if refreshing page, we should trigger re-fetch if signal is false?
        // For robustness: if signal is false but we have token, fetch it.
        if (!this.isAdmin()) {
             this.authService.esAdmin(token).subscribe();
        }
    } else {
        this.isLoggedIn.set(false);
        this.isAdmin.set(false); 
    }
  }

  initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        this.isDarkMode.set(true);
    } else {
        this.isDarkMode.set(false);
    }

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

  toggleTheme() {
    this.isDarkMode.update(prev => !prev);
  }
}
