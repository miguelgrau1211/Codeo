import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NotificationService } from '../../services/notification.service';

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
    imports: [CommonModule],
    template: `
    <div class="notification-container">
      @for (notif of notificationService.activeNotifications(); track notif.id) {
        <div 
          class="achievement-card" 
          [class]="notif.rareza"
        >
          <div class="close-btn" (click)="notificationService.removeNotification(notif.id)">
            <i class="bi bi-x-lg"></i>
          </div>
          
          <div class="icon-wrapper">
            @if (notif.icono_url) {
              <img [src]="notif.icono_url" [alt]="notif.nombre" class="achievement-icon">
            } @else {
              <div class="placeholder-icon">🏆</div>
            }
          </div>

          <div class="content">
            <span class="unlock-text">¡LOGRO DESBLOQUEADO!</span>
            <h3 class="achievement-title">{{ notif.nombre }}</h3>
            <p class="achievement-desc">{{ notif.descripcion }}</p>
          </div>

          <div class="rareza-badge">{{ notif.rareza | uppercase }}</div>
        </div>
      }
    </div>
  `,
    styles: [`
    .notification-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 12px;
      pointer-events: none;
    }

    .achievement-card {
      pointer-events: auto;
      width: 380px;
      padding: 16px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      gap: 16px;
      position: relative;
      overflow: hidden;
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
      
      /* CSS Entrance Animation */
      animation: slideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    /* Rarezas Styles */
    .especial { border-left: 4px solid #10b981; }
    .raro { border-left: 4px solid #3b82f6; }
    .epico { border-left: 4px solid #a855f7; }
    .legendario { border-left: 4px solid #f59e0b; }
    .celestial { 
      border-left: 4px solid #ef4444;
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(69, 10, 10, 0.4));
      animation: slideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards, celestial-glow 3s infinite alternate;
    }

    @keyframes celestial-glow {
      from { box-shadow: 0 0 10px rgba(239, 68, 68, 0.2); }
      to { box-shadow: 0 0 30px rgba(239, 68, 68, 0.4); }
    }

    .icon-wrapper {
      width: 64px;
      height: 64px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .achievement-icon {
      width: 48px;
      height: 48px;
      object-fit: contain;
      filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
    }

    .placeholder-icon {
      font-size: 32px;
    }

    .content {
      flex: 1;
    }

    .unlock-text {
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.1em;
      color: rgba(148, 163, 184, 0.8);
      display: block;
      margin-bottom: 2px;
    }

    .achievement-title {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: white;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .achievement-desc {
      margin: 2px 0 0;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.4;
    }

    .rareza-badge {
      position: absolute;
      top: 0;
      right: 0;
      padding: 4px 12px;
      font-size: 9px;
      font-weight: 900;
      background: rgba(255, 255, 255, 0.1);
      border-bottom-left-radius: 12px;
      color: rgba(255, 255, 255, 0.6);
    }

    .close-btn {
      position: absolute;
      top: 28px;
      right: 12px;
      color: rgba(255, 255, 255, 0.3);
      cursor: pointer;
      transition: color 0.2s;
      z-index: 10;
    }

    .close-btn:hover {
      color: white;
    }
  `]
})
export class AchievementNotificationComponent {
    public readonly notificationService = inject(NotificationService);
}




