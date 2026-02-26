import { Injectable, signal, effect } from '@angular/core';

/**
 * Servicio de audio.
 *
 * Controla el estado de silencio global de la aplicación
 * y permite reproducir efectos de sonido respetando la preferencia del usuario.
 * La preferencia se persiste en localStorage.
 */
@Injectable({
    providedIn: 'root'
})
export class AudioService {
    /** Signal reactivo que indica si el sonido está silenciado. */
    readonly isMuted = signal<boolean>(false);

    constructor() {
        // Cargar preferencia guardada de localStorage
        const saved = localStorage.getItem('app_muted');
        if (saved !== null) {
            this.isMuted.set(saved === 'true');
        }

        // Persistir cambios automáticamente en localStorage
        effect(() => {
            localStorage.setItem('app_muted', String(this.isMuted()));
        });
    }

    /** Alterna entre silenciado y con sonido. */
    toggleMute(): void {
        this.isMuted.update(m => !m);
    }

    /**
     * Reproduce un sonido solo si el audio no está silenciado.
     * @param path Ruta al archivo de audio.
     * @param volume Volumen de reproducción (0 a 1).
     */
    playSound(path: string, volume: number = 0.5): void {
        if (this.isMuted()) return;

        const audio = new Audio(path);
        audio.volume = volume;
        audio.play().catch(err => console.warn('Error al reproducir audio:', err));
    }
}
