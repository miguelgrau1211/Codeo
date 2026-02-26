import { Injectable, signal } from '@angular/core';

/** Modelo de notificación de logro desbloqueado. */
export interface AchievementNotification {
    id: string;
    nombre: string;
    descripcion: string;
    icono_url: string | null;
    rareza: string;
}

/**
 * Servicio de notificaciones de logros.
 *
 * Gestiona una cola de notificaciones toast que aparecen
 * cuando el usuario desbloquea logros o sube de nivel.
 * Las notificaciones se eliminan automáticamente tras 5 segundos.
 */
@Injectable({
    providedIn: 'root',
})
export class NotificationService {
    /** Cola interna de notificaciones activas. */
    private readonly notifications = signal<AchievementNotification[]>([]);

    /** Acceso público de solo lectura a las notificaciones activas. */
    public readonly activeNotifications = this.notifications.asReadonly();

    /**
     * Añade una nueva notificación de logro a la cola.
     * Se elimina automáticamente tras 5 segundos.
     * Normaliza URLs de iconos que empiezan con /storage.
     */
    showAchievement(achievement: Omit<AchievementNotification, 'id'>): void {
        const id = Math.random().toString(36).substring(2, 9);

        // Normalizar URL del icono si viene con ruta relativa del storage
        let icono_url = achievement.icono_url;
        if (icono_url && icono_url.startsWith('/storage')) {
            icono_url = `http://localhost${icono_url}`;
        }

        const newNotification: AchievementNotification = {
            ...achievement,
            icono_url,
            id
        };

        this.notifications.update((prev) => [...prev, newNotification]);

        // Auto-eliminar la notificación tras 5 segundos
        setTimeout(() => {
            this.removeNotification(id);
        }, 5000);
    }

    /**
     * Muestra una notificación especial de subida de nivel.
     * @param level Nivel al que ha subido el usuario.
     */
    showLevelUp(level: number): void {
        this.showAchievement({
            nombre: '¡SUBIDA DE NIVEL!',
            descripcion: `Has alcanzado el nivel ${level}. ¡Sigue así!`,
            icono_url: '/common/icons/level_up.png',
            rareza: 'legendaria'
        });
    }

    /** Elimina una notificación específica por su ID. */
    removeNotification(id: string): void {
        this.notifications.update((prev) => prev.filter((n) => n.id !== id));
    }
}
