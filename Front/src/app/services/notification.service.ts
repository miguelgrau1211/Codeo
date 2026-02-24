import { Injectable, signal } from '@angular/core';

export interface AchievementNotification {
    id: string;
    nombre: string;
    descripcion: string;
    icono_url: string | null;
    rareza: string;
}

@Injectable({
    providedIn: 'root',
})
export class NotificationService {
    // Signal to store the current notifications
    private readonly notifications = signal<AchievementNotification[]>([]);

    // Public readonly access to notifications
    public readonly activeNotifications = this.notifications.asReadonly();

    /**
     * Adds a new achievement notification to the queue
     */
    showAchievement(achievement: Omit<AchievementNotification, 'id'>) {
        const id = Math.random().toString(36).substring(2, 9);
        
        // Normalize icon URL if it starts with /storage
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

        // a los 5 segundos quitamos la noti
        setTimeout(() => {
            this.removeNotification(id);
        }, 5000);
    }

    /**
     * Notificación especial para subida de nivel
     */
    showLevelUp(level: number) {
        this.showAchievement({
            nombre: '¡SUBIDA DE NIVEL!',
            descripcion: `Has alcanzado el nivel ${level}. ¡Sigue así!`,
            icono_url: '/common/icons/level_up.png', // O un icono genérico
            rareza: 'legendaria'
        });
    }

    /**
     * Removes a notification by ID
     */
    removeNotification(id: string) {
        this.notifications.update((prev) => prev.filter((n) => n.id !== id));
    }
}
