import { Injectable, signal, effect } from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class AudioService {
    // Signal to control master volume/mute
    isMuted = signal<boolean>(false);

    constructor() {
        // Load preference from localStorage
        const saved = localStorage.getItem('app_muted');
        if (saved !== null) {
            this.isMuted.set(saved === 'true');
        }

        // Save preference when it changes
        effect(() => {
            localStorage.setItem('app_muted', String(this.isMuted()));
        });
    }

    toggleMute() {
        this.isMuted.update(m => !m);
    }

    /**
     * Play a sound only if not muted
     * @param path Path to audio file
     * @param volume Volume level (0 to 1)
     */
    playSound(path: string, volume: number = 0.5) {
        if (this.isMuted()) return;

        const audio = new Audio(path);
        audio.volume = volume;
        audio.play().catch(err => console.warn('Audio playback failed:', err));
    }
}

