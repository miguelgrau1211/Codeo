import { Component, signal, computed, inject, ChangeDetectionStrategy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ProgresoHistoriaService } from '../../services/progreso-historia-service';
import { UserDataService } from '../../services/user-data-service';
import { ThemeService } from '../../services/theme-service';
import { AuthService } from '../../services/auth-service';
import { TranslatePipe } from '../../pipes/translate.pipe';

interface Activity {
  id: number;
  type: 'complete' | 'achievement' | 'challenge';
  title: string;
  xpEarned: number;
  time: string;
}

interface StatsHistoria {
  actual_level: number;
  total_levels: number;
  lvls_progress: string;
  titulo: string;
}

interface BattlePassReward {
  level: number;
  type: 'coins' | 'theme' | 'xp';
  value: any;
  icon: string;
  label: string;
  themeVars?: Record<string, string>;
}

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslatePipe, FormsModule],
  templateUrl: './dashboard.component.html',
  styles: [],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DashboardComponent implements OnInit {
  private readonly progresoHistoriaService = inject(ProgresoHistoriaService);
  private readonly userDataService = inject(UserDataService);
  public readonly themeService = inject(ThemeService);
  private readonly authService = inject(AuthService);
  private readonly http = inject(HttpClient);

  // Exponer el signal de admin al template
  readonly isAdmin = this.authService.isAdminSignal;

  // Recent Activity Data
  recentActivity = signal<Activity[]>([
    { id: 1, type: 'complete', title: 'Completado "Bucles For"', xpEarned: 150, time: 'Hace 2h' },
    { id: 2, type: 'achievement', title: 'Logro Desbloqueado: "Bug Hunter"', xpEarned: 300, time: 'Hace 5h' },
    { id: 3, type: 'challenge', title: 'Derrotaste al Boss "NullPointer"', xpEarned: 500, time: 'Ayer' }
  ]);

  // Refactored logic:
  serviceProgreso = this.progresoHistoriaService.progresoSignal;

  stats_historia = computed(() => {
    const data = this.serviceProgreso();
    if (data) {
      return {
        actual_level: data.stats.total_niveles,
        total_levels: data.stats.completados,
        lvls_progress: data.stats.porcentaje_progreso,
        titulo: data.stats.titulo_ultimo_nivel
      };
    }
    return {
      actual_level: 0,
      total_levels: 0,
      lvls_progress: "0%",
      titulo: "Cargando..."
    };
  });

  // Signal que indica si los datos del usuario están listos
  isReady = computed(() => !!this.userDataService.userDataSignal());

  // Signals individuales expuestos para el template
  nickname = computed(() => this.userDataService.userDataSignal()?.nickname ?? '');
  avatar = computed(() => this.userDataService.userDataSignal()?.avatar ?? '');
  level = this.userDataService.level;
  experience = this.userDataService.experience;
  coins = this.userDataService.coins;
  streak = this.userDataService.streak;
  rank = computed(() => this.userDataService.userDataSignal()?.rank ?? 0);
  n_achievements = computed(() => this.userDataService.userDataSignal()?.n_achievements ?? 0);
  story_levels_completed = computed(() => this.userDataService.userDataSignal()?.story_levels_completed ?? 0);
  
  userData = computed(() => this.userDataService.userDataSignal());

  // --- Premium / Battle Pass ---
  isPremium = this.userDataService.isPremium;

  // --- Payment Modal State ---
  showPaymentModal = signal(false);
  paymentStep = signal<'form' | 'processing' | 'success' | 'error'>('form');
  paymentError = signal<string | null>(null);
  
  // Card form fields
  cardNumber = signal('');
  cardHolder = signal('');
  cardExpiry = signal('');
  cardCvv = signal('');

  // --- Battle Pass Logic ---
  battlePassRewards = signal<BattlePassReward[]>([
    { 
      level: 5, type: 'theme', value: 'Cyber Volcanic', icon: '🌋', label: 'Cyber Volcanic',
      themeVars: { '--primary-bg': '#1a0505', '--secondary-bg': '#2d0a0a', '--accent-color': '#ff4500' }
    },
    { 
      level: 12, type: 'theme', value: 'Aurora Borealis', icon: '🌌', label: 'Aurora Borealis',
      themeVars: { '--primary-bg': '#051622', '--secondary-bg': '#1ba098', '--accent-color': '#deb992' }
    },
    { level: 20, type: 'coins', value: 500, icon: '💰', label: '500 Coins' },
    { 
      level: 28, type: 'theme', value: 'Gold Rush', icon: '💎', label: 'Gold Rush',
      themeVars: { '--primary-bg': '#000000', '--secondary-bg': '#111111', '--accent-color': '#ffd700' }
    },
    { 
      level: 35, type: 'theme', value: 'Void Master', icon: '🔮', label: 'Void Master',
      themeVars: { '--primary-bg': '#020202', '--secondary-bg': '#0a0a0c', '--accent-color': '#8a2be2' }
    },
    { level: 42, type: 'coins', value: 5000, icon: '👑', label: '5000 Coins' },
  ]);

  nextReward = computed(() => {
    const curLevel = this.level();
    return this.battlePassRewards().find(r => r.level > curLevel) || null;
  });

  completedRewardsCount = computed(() => {
    const curLevel = this.level();
    return this.battlePassRewards().filter(r => r.level <= curLevel).length;
  });

  battlePassProgress = computed(() => {
    const curLevel = this.level();
    const rewards = this.battlePassRewards();
    const lastRewardLevel = rewards[rewards.length - 1].level;
    return Math.min(100, (curLevel / lastRewardLevel) * 100);
  });

  // Init logic — el servicio cachea internamente, no hay llamadas duplicadas
  ngOnInit() {
    if (!this.serviceProgreso()) {
      this.progresoHistoriaService.getProgresoHistoria().subscribe();
    }
    this.userDataService.getUserData().subscribe();

    // Verificar si el usuario es admin usando el token del sessionStorage
    const token = sessionStorage.getItem('token');
    if (token) {
      this.authService.esAdmin(token).subscribe();
    }
  }

  // Sidebar State
  isSidebarOpen = signal(true);

  toggleSidebar() {
    this.isSidebarOpen.update(v => !v);
  }

  // --- Payment Modal Methods ---
  openPaymentModal() {
    this.paymentStep.set('form');
    this.paymentError.set(null);
    this.cardNumber.set('');
    this.cardHolder.set('');
    this.cardExpiry.set('');
    this.cardCvv.set('');
    this.showPaymentModal.set(true);
  }

  closePaymentModal() {
    this.showPaymentModal.set(false);
  }

  formatCardNumber(event: Event) {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 16);
    const formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    this.cardNumber.set(formatted);
    input.value = formatted;
  }

  formatExpiry(event: Event) {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 4);
    if (value.length >= 2) {
      value = value.substring(0, 2) + '/' + value.substring(2);
    }
    this.cardExpiry.set(value);
    input.value = value;
  }

  isFormValid(): boolean {
    return this.cardNumber().replace(/\s/g, '').length === 16
      && this.cardHolder().trim().length >= 2
      && this.cardExpiry().length === 5
      && this.cardCvv().length >= 3;
  }

  submitPayment() {
    if (!this.isFormValid()) return;

    this.paymentStep.set('processing');

    const token = sessionStorage.getItem('token');
    const payload = {
      card_number: this.cardNumber().replace(/\s/g, ''),
      card_holder: this.cardHolder(),
      expiry: this.cardExpiry(),
      cvv: this.cardCvv(),
    };

    this.http.post<any>('http://localhost/api/battle-pass/purchase', payload, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }).subscribe({
      next: (res) => {
        if (res.success) {
          this.paymentStep.set('success');
          // Refresh user data to get updated premium status
          this.userDataService.getUserData(true).subscribe();
        } else {
          this.paymentError.set(res.message || 'Error al procesar el pago.');
          this.paymentStep.set('error');
        }
      },
      error: (err) => {
        this.paymentError.set(err.error?.message || 'Error al conectar con el servidor.');
        this.paymentStep.set('error');
      }
    });
  }
}
