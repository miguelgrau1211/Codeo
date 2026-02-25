import { Component, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AudioService } from '../../services/audio-service';
import { AuthService } from '../../services/auth-service';
import { LanguageService, Language } from '../../services/language-service';
import { TranslatePipe } from '../../pipes/translate.pipe';

@Component({
  selector: 'app-configuracion',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule, TranslatePipe],
  templateUrl: './configuracion.component.html',
  styleUrl: './configuracion.component.css'
})
export class ConfiguracionComponent {
  private audioService = inject(AudioService);
  private authService = inject(AuthService);
  public languageService = inject(LanguageService);
  private router = inject(Router);

  // Settings Signals
  isMuted = this.audioService.isMuted;

  // UI State
  showDeleteModal = signal(false);
  showLangModal = signal(false);
  isProcessing = signal(false);

  constructor() { }

  toggleSound() {
    this.audioService.toggleMute();
  }

  openLanguageSelector() {
    this.showLangModal.set(true);
  }

  closeLanguageSelector() {
    this.showLangModal.set(false);
  }

  selectLanguage(code: string) {
    this.languageService.setLanguage(code);
    this.closeLanguageSelector();
  }

  onLogout() {
    this.isProcessing.set(true);
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/']);
      },
      error: (err) => {
        console.error('Error logging out:', err);
        sessionStorage.clear();
        this.router.navigate(['/']);
      }
    });
  }

  confirmDeleteAccount() {
    this.showDeleteModal.set(true);
  }

  cancelDelete() {
    this.showDeleteModal.set(false);
  }

  executeDeleteAccount() {
    this.isProcessing.set(true);
    this.authService.desactivarCuenta().subscribe({
      next: () => {
        this.showDeleteModal.set(false);
        this.router.navigate(['/']);
      },
      error: (err) => {
        console.error('Error deleting account:', err);
        this.isProcessing.set(false);
      }
    });
  }
}





