import { Component, signal, inject, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HeaderComponent } from './components/header/header.component';
import { AchievementNotificationComponent } from './components/achievement-notification/achievement-notification.component';
import { UserDataService } from './services/user-data.service';
import { ThemeService } from './services/theme.service';

/**
 * Componente raíz de la aplicación Codeo.
 *
 * Contiene la estructura base con:
 * - HeaderComponent: Navegación principal (se oculta en login/registro).
 * - RouterOutlet: Renderiza el componente de la ruta activa.
 * - AchievementNotificationComponent: Toast global para logros desbloqueados.
 */
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, HeaderComponent, AchievementNotificationComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {
  protected readonly title = signal('Codeo');
  
  private readonly userDataService = inject(UserDataService);
  private readonly themeService = inject(ThemeService);

  ngOnInit() {
    // Restaurar sesión y tema al recargar cualquier página si hay un token
    const token = sessionStorage.getItem('token');
    if (token) {
      this.userDataService.getUserData().subscribe();
    }
  }
}

