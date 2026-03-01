import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NotificationService } from '../../services/notification.service';
import { TranslatePipe } from '../../pipes/translate.pipe';

/**
 * Componente de notificaciones de logros.
 *
 * Muestra toasts animados cuando el usuario desbloquea un logro o sube de nivel.
 * Se posiciona fijo en pantalla y cada notificación se auto-elimina tras 5 segundos.
 * Componente "Dumb" (presentacional): solo lee del NotificationService.
 */
@Component({
  selector: 'app-achievement-notification',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  templateUrl: './achievement-notification.component.html'
})
export class AchievementNotificationComponent {
  public readonly notificationService = inject(NotificationService);
}
