import { Component, ElementRef, ViewChild, AfterViewInit, OnDestroy, ChangeDetectorRef, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { ProgresoHistoriaService } from '../../services/progreso-historia.service';
import { Router, ActivatedRoute } from '@angular/router';
import { TranslatePipe } from '../../pipes/translate.pipe';

/**
 * Componente de inicio de sesión.
 *
 * Gestiona la autenticación del usuario mediante:
 * - Formulario reactivo con email y contraseña.
 * - Botón de autenticación con Google (OAuth redirect).
 * - Validación de tokens existentes en sessionStorage y URL params.
 * - Efecto visual de fondo con vídeo y canvas animado.
 *
 * Tras login exitoso, redirige al dashboard precargando los datos del usuario.
 */
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule, TranslatePipe],
  templateUrl: './login.component.html',
  styles: [`
    video {
      min-height: 105%;
      min-width: 105%;
    }
  `]
})
export class LoginComponent implements AfterViewInit, OnDestroy, OnInit {
  private authService = inject(AuthService);
  private progresoService = inject(ProgresoHistoriaService);
  private fb = inject(FormBuilder);
  private cdr = inject(ChangeDetectorRef);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  isLoading = signal(false); // Signal for loading state
  loadingMessage = signal('LOGIN.AUTHENTICATING');

  loginForm: FormGroup = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required]]
  });

  ngOnInit() {
    // 1. Check for token in URL (from Google Auth redirect)
    const urlToken = this.route.snapshot.queryParamMap.get('token');
    const error = this.route.snapshot.queryParamMap.get('error');

    if (error) {
      this.errorMessage.set('LOGIN.ERR_GOOGLE_FAIL');
      return;
    }

    if (urlToken) {
      console.log('Token detectado en URL, validando...');
      this.isLoading.set(true);
      this.loadingMessage.set('LOGIN.WAIT_GOOGLE');
      sessionStorage.setItem('token', urlToken);

      // Validar usuario y obtener nickname
      this.authService.validateUser(urlToken).subscribe({
        next: (data) => {
          console.log('Validación exitosa:', data);
          if (data.nickname) {
            sessionStorage.setItem('nickname', data.nickname);
          }
          // Pequeño delay para que se vea la animación
          setTimeout(() => {
            this.router.navigate(['/dashboard']);
          }, 800);
        },
        error: (err) => {
          console.error('Error validando token de Google:', err);
          this.isLoading.set(false);
          sessionStorage.removeItem('token');
          this.errorMessage.set('LOGIN.ERR_GOOGLE_INVALID');
        }
      });
      return;
    }

    // 2. Normal session check
    const token = sessionStorage.getItem('token');
    if (token) {
      this.authService.validateUser(token).subscribe({
        next: () => this.router.navigate(['/dashboard']),
        error: () => {
          console.log('Token guardado no válido, limpiando...');
          sessionStorage.removeItem('token');
        }
      });
    }
  }

  loginWithGoogle() {
    this.isLoading.set(true);
    this.loadingMessage.set('LOGIN.REDIRECT_GOOGLE');
    // Redirigir al endpoint del backend que inicia el flujo de Google
    window.location.href = 'http://localhost/api/auth/google';
  }


  @ViewChild('bgCanvas') canvasRef!: ElementRef<HTMLCanvasElement>;
  @ViewChild('sourceVideo') video1Ref!: ElementRef<HTMLVideoElement>;
  @ViewChild('sourceVideo2') video2Ref!: ElementRef<HTMLVideoElement>;

  private animationFrameId: number | null = null;

  // Crossfade config
  private readonly FADE_TIME = 1.5; // seconds duration of crossfade
  private opacity1 = 1;
  private opacity2 = 0;

  constructor() { }

  ngAfterViewInit() {
    const canvas = this.canvasRef.nativeElement;
    const v1 = this.video1Ref.nativeElement;
    const v2 = this.video2Ref.nativeElement;

    // Resize
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    v1.muted = true;
    v2.muted = true;
    v1.loop = true; // Native loop
    v2.loop = true;

    // Start V1
    v1.play().catch(() => { });

    // Schedule V2 to start exactly halfway
    const startV2 = () => {
      // We start V2 hidden (opacity 0)
      // Calculate offset
      const offset = v1.duration > 0 ? v1.duration / 2 : 2;
      v2.currentTime = 0; // Ensure start from 0

      // Wait for offset
      setTimeout(() => {
        v2.play().catch(() => { });
      }, offset * 1000);
    };

    if (v1.readyState >= 1) {
      startV2();
    } else {
      v1.onloadedmetadata = startV2;
    }

    this.cdr.detectChanges();
    this.startRenderingLoop(canvas, v1, v2);
  }

  private startRenderingLoop(canvas: HTMLCanvasElement, v1: HTMLVideoElement, v2: HTMLVideoElement) {
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const resizeObserver = new ResizeObserver(() => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
    resizeObserver.observe(document.body);

    const render = () => {
      // Logic: Calculate Opacity based on remaining time
      // We toggle dominance based on whomever is "safest"
      const v1Rem = v1.duration - v1.currentTime;
      const v2Rem = v2.duration - v2.currentTime;

      // Simple State Machine:
      // If V1 is ending (< FADE_TIME) -> Decrease Opacity 1, Increase Opacity 2
      // If V2 is ending (< FADE_TIME) -> Decrease Opacity 2, Increase Opacity 1
      // Else -> Maintain current dominant

      // We normalize fade: 0 to 1 based on remaining time
      if (v1Rem < this.FADE_TIME) {
        // V1 dying, transition to V2
        this.opacity1 = Math.max(0, v1Rem / this.FADE_TIME);
        this.opacity2 = 1 - this.opacity1;
      } else if (v2Rem < this.FADE_TIME) {
        // V2 dying, transition to V1
        this.opacity2 = Math.max(0, v2Rem / this.FADE_TIME);
        this.opacity1 = 1 - this.opacity2;
      } else {
        // Stable state: Keep whomever is dominant fully opaque
        // We need to know who is 'current'. 
        // Heuristic: If opacity1 > 0.5, snap to 1. 
        if (this.opacity1 > 0.5) { this.opacity1 = 1; this.opacity2 = 0; }
        else { this.opacity2 = 1; this.opacity1 = 0; }
      }

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Draw V2 first (Base)
      if (this.opacity2 > 0) {
        ctx.globalAlpha = this.opacity2;
        this.drawVideoFrame(ctx, v2);
      }

      // Draw V1 on top
      if (this.opacity1 > 0) {
        ctx.globalAlpha = this.opacity1;
        this.drawVideoFrame(ctx, v1);
      }

      // Reset Alpha
      ctx.globalAlpha = 1;

      this.animationFrameId = requestAnimationFrame(render);
    };

    render();
  }

  private drawVideoFrame(ctx: CanvasRenderingContext2D, video: HTMLVideoElement) {
    if (video.readyState < 2) return;
    const canvas = ctx.canvas;
    const vW = video.videoWidth || 1280;
    const vH = video.videoHeight || 720;
    const ratio = Math.max(canvas.width / vW, canvas.height / vH);
    const centerShift_x = (canvas.width - vW * ratio) / 2;
    const centerShift_y = (canvas.height - vH * ratio) / 2;
    ctx.drawImage(video, 0, 0, vW, vH,
      centerShift_x, centerShift_y, vW * ratio, vH * ratio);
  }

  ngOnDestroy() {
    if (this.animationFrameId) {
      cancelAnimationFrame(this.animationFrameId);
    }
  }

  errorMessage = signal<string>('');

  onSubmit() {
    if (this.isLoading()) return; // Prevent double submission
    this.errorMessage.set('');

    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    if (this.loginForm.valid) {
      this.isLoading.set(true);
      this.loadingMessage.set('LOGIN.LOGGING_IN');

      this.authService.login(this.loginForm.value).subscribe({
        next: (response) => {
          sessionStorage.setItem('token', response.access_token);
          sessionStorage.setItem('nickname', response.nickname);

          const token = response.access_token;
          // Reduced delay for the "wow" factor without being frustratingly slow
          const minAnimationTime = new Promise(resolve => setTimeout(resolve, 600));

          // Pre-fetch only progress (admin status is already in response)
          import('rxjs').then(({ from }) => {
            this.progresoService.getProgresoHistoria().subscribe({
              next: () => {
                minAnimationTime.then(() => this.router.navigate(['/dashboard']));
              },
              error: () => {
                minAnimationTime.then(() => this.router.navigate(['/dashboard']));
              }
            });
          });
        },
        error: (error) => {
          console.error('Login failed:', error);
          this.isLoading.set(false); // Stop loading on error

          if (error.status === 422 && error.error?.errors) {
            const serverErrors = error.error.errors;
            Object.keys(serverErrors).forEach(key => {
              const control = this.loginForm.get(key);
              if (control) {
                control.setErrors({ serverError: serverErrors[key][0] });
                control.markAsTouched();
              }
            });
          } else if (error.status === 401 || error.status === 403) {
            // Credenciales incorrectas
            this.errorMessage.set('LOGIN.ERR_INVALID_CREDENTIALS');
          } else {
            this.errorMessage.set(error.error?.message || 'LOGIN.ERR_UNKNOWN');
          }
        }
      });
    }
  }
}




