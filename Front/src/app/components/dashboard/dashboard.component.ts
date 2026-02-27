import { Component, signal, computed, inject, ChangeDetectionStrategy, OnInit, AfterViewChecked } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ProgresoHistoriaService } from '../../services/progreso-historia.service';
import { UserDataService } from '../../services/user-data.service';
import { ThemeService } from '../../services/theme.service';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../services/auth.service';
import { TranslatePipe } from '../../pipes/translate.pipe';

// Tipos globales para Stripe.js
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

/**
 * Componente del dashboard principal.
 *
 * Pantalla principal del usuario autenticado que muestra:
 * - Estadísticas del jugador (nivel, XP, monedas, racha, ranking).
 * - Progreso en el modo historia (barra de progreso y último nivel).
 * - Actividad reciente del usuario.
 * - Estado y compra del Pase de Batalla (integración con Stripe).
 * - Accesos directos a las secciones principales del juego.
 *
 * Es un componente "Smart" que orquesta datos de múltiples servicios.
 */
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

  // Datos de Actividad Reciente
  recentActivity = signal<Activity[]>([
    { id: 1, type: 'complete', title: 'Completado "Bucles For"', xpEarned: 150, time: 'Hace 2h' },
    { id: 2, type: 'achievement', title: 'Logro Desbloqueado: "Bug Hunter"', xpEarned: 300, time: 'Hace 5h' },
    { id: 3, type: 'challenge', title: 'Derrotaste al Boss "NullPointer"', xpEarned: 500, time: 'Ayer' }
  ]);

  // Lógica refactorizada:
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

  // --- Premium / Pase de Batalla ---
  isPremium = this.userDataService.isPremium;

  // --- Estado del Modal de Pago ---
  showPaymentModal = signal(false);
  paymentStep = signal<'loading' | 'form' | 'processing' | 'success' | 'error'>('loading');
  paymentError = signal<string | null>(null);
  stripeCardReady = signal(false);
  cardHolder = signal('');

  // Variables internas de Stripe (no son señales - referencias mutables)
  private stripe: any = null;
  private cardElement: any = null;
  private clientSecret: string | null = null;
  private stripeElementMounted = false;

  // --- Lógica del Pase de Batalla ---
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

  // Lógica de inicialización
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

  // Montar el elemento de Stripe cuando el contenedor del modal aparece en el DOM
  ngAfterViewChecked() {
    if (this.showPaymentModal() && this.paymentStep() === 'form' && !this.stripeElementMounted) {
      const container = document.getElementById('stripe-card-element');
      if (container && this.cardElement) {
        this.cardElement.mount('#stripe-card-element');
        this.stripeElementMounted = true;
      }
    }
  }

  // Estado de la barra lateral (Sidebar)
  isSidebarOpen = signal(true);

  toggleSidebar() {
    this.isSidebarOpen.update(v => !v);
  }

  // --- Métodos de pago con Stripe ---

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

    // Paso 1: Obtener estado de Stripe y clave pública
    this.http.get<any>(`${environment.apiUrl}/battle-pass/status`, { headers }).subscribe({
      next: (statusRes) => {
        const publishableKey = statusRes.stripe_publishable_key;

        if (!publishableKey || publishableKey.includes('REEMPLAZA')) {
          this.paymentError.set('Error: Stripe API Key de test no configurada en el servidor (.env).');
          this.paymentStep.set('error');
          return;
        }

        // Paso 2: Crear PaymentIntent en el Backend
        this.http.post<any>(`${environment.apiUrl}/battle-pass/create-intent`, {}, { headers }).subscribe({
          next: (intentRes) => {
            if (!intentRes.success) {
              this.paymentError.set(intentRes.message || 'Error al iniciar el pago.');
              this.paymentStep.set('error');
              return;
            }

            this.clientSecret = intentRes.client_secret;

            // Paso 3: Inicializar Stripe.js y Elements
            this.stripe = Stripe(publishableKey);
            const elements = this.stripe.elements();

            // Crear un elemento de tarjeta (Card Element) con estilos
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

            // Escuchar cambios de estado o errores en la tarjeta
            this.cardElement.on('change', (event: any) => {
              if (event.error) {
                this.paymentError.set(event.error.message);
              } else {
                this.paymentError.set(null);
              }
              this.stripeCardReady.set(event.complete);
            });

            // Esperar al evento 'ready' de Stripe para confirmar que está montado
            this.cardElement.on('ready', () => {
              this.stripeCardReady.set(false); // Ready pero aún sin datos completos
            });

            // Mostrar el formulario y montar tras renderizado del DOM
            this.paymentStep.set('form');
            setTimeout(() => {
              const container = document.getElementById('stripe-card-element');
              if (container && this.cardElement && !this.stripeElementMounted) {
                this.cardElement.mount('#stripe-card-element');
                this.stripeElementMounted = true;
              }
            }, 100);
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
    if (!this.stripe || !this.cardElement || !this.clientSecret || !this.cardHolder().trim()) {
      console.warn('⚠️ [PAYMENT] Faltan datos para procesar el pago:', {
        stripe: !!this.stripe,
        cardElement: !!this.cardElement,
        clientSecret: !!this.clientSecret,
        holder: this.cardHolder()
      });
      return;
    }

    if (!this.stripeElementMounted) {
      this.paymentError.set('El formulario de pago aún no está listo. Espera un momento e inténtalo de nuevo.');
      return;
    }

    console.log('🔵 [PAGO] Iniciando procesamiento con Stripe...');
    this.paymentStep.set('processing');
    this.paymentError.set(null);

    try {
      // Paso 4: Confirmar el pago con Stripe.js
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
        this.paymentError.set(error.message || 'Error al procesar el pago con la tarjeta.');
        this.paymentStep.set('error');
        return;
      }

      console.log('🟢 [STRIPE] PaymentIntent status:', paymentIntent.status);

      if (paymentIntent.status === 'succeeded') {
        // Paso 5: Notificar a nuestro backend para activar el estado premium
        console.log('🔵 [BACKEND] Notificando confirmación al servidor...');
        const token = sessionStorage.getItem('token');
        this.http.post<any>(`${environment.apiUrl}/battle-pass/confirm`, 
          { payment_intent_id: paymentIntent.id },
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          }
        ).subscribe({
          next: (res) => {
            console.log('🟢 [BACKEND] Respuesta confirmación:', res);
            if (res.success) {
              // === CONSOLE CONFIRMATION ===
              console.log('%c ✅ PAGO REALIZADO CON ÉXITO ', 'background: #00ca4e; color: #fff; font-size: 16px; font-weight: bold; border-radius: 4px; padding: 4px 8px;');
              
              this.paymentStep.set('success');
              // Silently refresh data
              this.userDataService.getUserData(true).subscribe();
            } else {
              this.paymentError.set(res.message || 'El pago fue exitoso pero hubo un problema al activar el premium.');
              this.paymentStep.set('error');
            }
          },
          error: (err) => {
            console.error('🔴 [BACKEND] Error en confirmación:', err);
            this.paymentError.set(err.error?.message || 'Error de conexión con el servidor al confirmar el pago.');
            this.paymentStep.set('error');
          }
        });
      } else {
        // Status like 'requires_action' or others should be handled but for an MVP we treat as error
        console.warn('🟡 [STRIPE] Estado de pago inesperado:', paymentIntent.status);
        this.paymentError.set('El pago requiere una acción adicional o no se ha completado correctamente (Status: ' + paymentIntent.status + ').');
        this.paymentStep.set('error');
      }
    } catch (err: any) {
      console.error('🔴 [FATAL] Error inesperado:', err);
      this.paymentError.set(err.message || 'Ocurrió un error inesperado durante el procesamiento del pago.');
      this.paymentStep.set('error');
    }
  }
}
