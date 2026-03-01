import { Component, signal, effect, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { TranslatePipe } from '../../pipes/translate.pipe';
/**
 * Componente de cabecera principal.
 *
 * Barra de navegación superior que incluye enlaces a las secciones principales,
 * botón de admin (si aplica), y se oculta automáticamente en rutas de
 * autenticación (login, registro, landing).
 * Escucha eventos de NavigationEnd para actualizar su visibilidad.
 */
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
  isVisible = signal(true);

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
          this.updateVisibility();
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

  updateVisibility() {
    const hiddenRoutes = ['', '/', '/login', '/registro', '/soporte'];
    // verificamos si la ruta actual está en hiddenRoutes
    const currentPath = this.router.url.split('?')[0];
    this.isVisible.set(!hiddenRoutes.includes(currentPath));
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        this.isLoggedIn.set(false);
        this.isAdmin.set(false);
        this.router.navigate(['/login']);
      },
      error: (err) => {
        console.error('Logout falló', err);
        // Fallback en caso de que el backend falle para forzar limpieza
        sessionStorage.clear();
        this.isLoggedIn.set(false);
        this.isAdmin.set(false);
        this.router.navigate(['/login']);
      }
    });
  }
}





