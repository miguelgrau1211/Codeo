import { Component, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HeaderComponent } from './components/header/header.component';
import { AchievementNotificationComponent } from './components/achievement-notification/achievement-notification.component';

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
export class AppComponent {
  protected readonly title = signal('Codeo');
}

