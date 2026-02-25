import { Component, signal, effect, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { AuthService } from '../../services/auth-service';
import { TranslatePipe } from '../../pipes/translate.pipe';
@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, TranslatePipe],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent {
  authService = inject(AuthService);
  platformId = inject(PLATFORM_ID);

  // States
  isLoggedIn = signal(false);
  isDashboard = signal(false);

  // Computed signal directly from service
  isAdmin = this.authService.isAdminSignal;

  router = inject(Router);

  constructor() {
    if (isPlatformBrowser(this.platformId)) {
      this.checkAuthStatus();

      // Listen for route changes to update status and layout
      this.router.events.subscribe(event => {
        if (event instanceof NavigationEnd) {
          this.checkAuthStatus();
          this.isDashboard.set(this.router.url.includes('/dashboard'));
        }
      });
    }
  }

  checkAuthStatus() {
    const token = sessionStorage.getItem('token');

    if (token) {
      this.isLoggedIn.set(true);
      if (!this.isAdmin()) {
        this.authService.esAdmin(token).subscribe({
          error: (err) => {
            if (err.status === 401) {
              this.logout();
            }
          }
        });
      }
    } else {
      this.isLoggedIn.set(false);
      this.isAdmin.set(false);
    }
  }

  logout() {
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('user');
    this.isLoggedIn.set(false);
    this.isAdmin.set(false);
    this.router.navigate(['/login']);
  }
}





