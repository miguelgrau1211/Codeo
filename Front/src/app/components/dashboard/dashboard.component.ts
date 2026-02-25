import { Component, signal, computed, inject, ChangeDetectionStrategy, OnInit, AfterViewChecked } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ProgresoHistoriaService } from '../../services/progreso-historia-service';
import { UserDataService } from '../../services/user-data-service';
import { ThemeService } from '../../services/theme-service';
import { AuthService } from '../../services/auth-service';
import { TranslatePipe } from '../../pipes/translate.pipe';

// Stripe.js global types
declare const Stripe: any;

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
export class DashboardComponent implements OnInit, AfterViewChecked {
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
  paymentStep = signal<'loading' | 'form' | 'processing' | 'success' | 'error'>('loading');
  paymentError = signal<string | null>(null);
  stripeCardReady = signal(false);
  cardHolder = signal('');

  // Stripe internals (not signals - mutable references)
  private stripe: any = null;
  private cardElement: any = null;
  private clientSecret: string | null = null;
  private stripeElementMounted = false;

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

  // Init logic
  ngOnInit() {
    if (!this.serviceProgreso()) {
      this.progresoHistoriaService.getProgresoHistoria().subscribe();
    }
    this.userDataService.getUserData().subscribe();

    const token = sessionStorage.getItem('token');
    if (token) {
      this.authService.esAdmin(token).subscribe();
    }
  }

  // Mount Stripe Element when the modal container appears in the DOM
  ngAfterViewChecked() {
    if (this.showPaymentModal() && this.paymentStep() === 'form' && !this.stripeElementMounted) {
      const container = document.getElementById('stripe-card-element');
      if (container && this.cardElement) {
        this.cardElement.mount('#stripe-card-element');
        this.stripeElementMounted = true;
      }
    }
  }

  // Sidebar State
  isSidebarOpen = signal(true);

  toggleSidebar() {
    this.isSidebarOpen.update(v => !v);
  }

  // --- Stripe Payment Methods ---

  openPaymentModal() {
    this.paymentStep.set('loading');
    this.paymentError.set(null);
    this.stripeCardReady.set(false);
    this.stripeElementMounted = false;
    this.cardHolder.set('');
    this.showPaymentModal.set(true);

    const token = sessionStorage.getItem('token');
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    };

    // Step 1: Get Stripe status and publishable key
    this.http.get<any>('http://localhost/api/battle-pass/status', { headers }).subscribe({
      next: (statusRes) => {
        const publishableKey = statusRes.stripe_publishable_key;

        if (!publishableKey || publishableKey.includes('REEMPLAZA')) {
          this.paymentError.set('Error: Stripe API Key de test no configurada en el servidor (.env).');
          this.paymentStep.set('error');
          return;
        }

        // Step 2: Create PaymentIntent on Backend
        this.http.post<any>('http://localhost/api/battle-pass/create-intent', {}, { headers }).subscribe({
          next: (intentRes) => {
            if (!intentRes.success) {
              this.paymentError.set(intentRes.message || 'Error al iniciar el pago.');
              this.paymentStep.set('error');
              return;
            }

            this.clientSecret = intentRes.client_secret;

            // Step 3: Initialize Stripe.js and Elements
            this.stripe = Stripe(publishableKey);
            const elements = this.stripe.elements();

            // Create a styled Card Element
            this.cardElement = elements.create('card', {
              style: {
                base: {
                  color: '#ffffff',
                  fontFamily: '"Outfit", "Segoe UI", sans-serif',
                  fontSmoothing: 'antialiased',
                  fontSize: '16px',
                  '::placeholder': {
                    color: 'rgba(255, 255, 255, 0.4)',
                  },
                },
                invalid: {
                  color: '#ff6b6b',
                  iconColor: '#ff6b6b',
                },
              },
              hidePostalCode: true,
            });

            // Listen for card readiness/errors
            this.cardElement.on('change', (event: any) => {
              if (event.error) {
                this.paymentError.set(event.error.message);
              } else {
                this.paymentError.set(null);
              }
              this.stripeCardReady.set(event.complete);
            });

            // Show the form
            this.paymentStep.set('form');
          },
          error: (err) => {
            this.paymentError.set(err.error?.message || 'Error al conectar con la pasarela de pagos.');
            this.paymentStep.set('error');
          }
        });
      },
      error: (err) => {
        this.paymentError.set('Error al verificar el estado del Pase de Batalla.');
        this.paymentStep.set('error');
      }
    });
  }

  closePaymentModal() {
    if (this.cardElement) {
      this.cardElement.unmount();
      this.cardElement.destroy();
      this.cardElement = null;
    }
    this.stripe = null;
    this.clientSecret = null;
    this.stripeElementMounted = false;
    this.showPaymentModal.set(false);
  }

  async submitPayment() {
    if (!this.stripe || !this.cardElement || !this.clientSecret || !this.cardHolder().trim()) return;

    this.paymentStep.set('processing');

    try {
      // Step 4: Confirm payment with Stripe.js
      const { error, paymentIntent } = await this.stripe.confirmCardPayment(
        this.clientSecret,
        {
          payment_method: {
            card: this.cardElement,
            billing_details: {
              name: this.cardHolder(),
            },
          }
        }
      );

      if (error) {
        console.error('🔴 [STRIPE] Error:', error.message);
        this.paymentError.set(error.message || 'Error al procesar el pago.');
        this.paymentStep.set('error');
        return;
      }

      if (paymentIntent.status === 'succeeded') {
        // Step 5: Notify our backend to activate premium status
        const token = sessionStorage.getItem('token');
        this.http.post<any>('http://localhost/api/battle-pass/confirm', 
          { payment_intent_id: paymentIntent.id },
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          }
        ).subscribe({
          next: (res) => {
            if (res.success) {
              // === CONSOLE CONFIRMATION ===
              console.log('%c ✅ PAGO REALIZADO CON ÉXITO ', 'background: #00ca4e; color: #fff; font-size: 16px; font-weight: bold; border-radius: 4px; padding: 4px 8px;');
              console.log('ID Transacción:', paymentIntent.id);
              console.log('Estado:', 'Confirmed');
              console.log('Premium activado para el usuario.');

              this.paymentStep.set('success');
              this.userDataService.getUserData(true).subscribe();
            } else {
              this.paymentError.set(res.message || 'El pago fue exitoso pero hubo un error al activar el premium en tu cuenta.');
              this.paymentStep.set('error');
            }
          },
          error: (err) => {
            this.paymentError.set(err.error?.message || 'Error de conexión final con el servidor.');
            this.paymentStep.set('error');
          }
        });
      }
    } catch (err: any) {
      this.paymentError.set(err.message || 'Ocurrió un error inesperado durante el pago.');
      this.paymentStep.set('error');
    }
  }
}
