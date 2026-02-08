import { Component, ElementRef, ViewChild, AfterViewInit, OnDestroy, HostListener, ChangeDetectorRef, inject, ChangeDetectionStrategy, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../services/auth-service';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';

export const matchPasswordValidator: ValidatorFn = (control: AbstractControl): ValidationErrors | null => {
  const password = control.get('password');
  const confirm = control.get('password_confirm');
  return password && confirm && password.value !== confirm.value ? { passwordMismatch: true } : null;
};

@Component({
  selector: 'app-registro',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule],
  templateUrl: './registro.html',
  styleUrl: './registro.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Registro implements AfterViewInit, OnDestroy {
  private authService = inject(AuthService);
  private router = inject(Router);

  registroForm = new FormGroup({
    nombre: new FormControl('', [Validators.required]),
    apellidos: new FormControl('', [Validators.required]),
    username: new FormControl('', [Validators.required]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(8)]),
    password_confirm: new FormControl('', [Validators.required])
  }, { validators: matchPasswordValidator });

  @ViewChild('bgCanvas') canvasRef!: ElementRef<HTMLCanvasElement>;
  @ViewChild('sourceVideo') video1Ref!: ElementRef<HTMLVideoElement>;
  @ViewChild('sourceVideo2') video2Ref!: ElementRef<HTMLVideoElement>;

  private animationFrameId: number | null = null;

  // Crossfade config
  private readonly FADE_TIME = 1.5; // seconds duration of crossfade
  private opacity1 = 1;
  private opacity2 = 0;

  constructor(private cdr: ChangeDetectorRef) { }

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
    v1.play().catch(e => console.error(e));

    // Schedule V2 to start exactly halfway
    const startV2 = () => {
      // We start V2 hidden (opacity 0)
      // Calculate offset
      const offset = v1.duration > 0 ? v1.duration / 2 : 2;
      v2.currentTime = 0; // Ensure start from 0

      // Wait for offset
      setTimeout(() => {
        v2.play().catch(e => console.error(e));
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

  errorMessage = signal<string>(''); // Para errores generales

  onSubmit() {
    this.errorMessage.set(''); // Limpiar errores previos

    if (this.registroForm.invalid) {
      this.registroForm.markAllAsTouched();
      return;
    }

    const formValue = this.registroForm.value;
    const payload = {
      nickname: formValue.username,
      nombre: formValue.nombre,
      apellidos: formValue.apellidos,
      email: formValue.email,
      password: formValue.password,
      terminos_aceptados: true
    };

    console.log('Enviando payload al backend...');

    this.authService.register(payload).subscribe({
      next: (response) => {
        console.log('Registro exitoso:', response);
        this.router.navigate(['/login']);
      },
      error: (error) => {
        console.error('Error detallado del backend:', error);

        if (error.status === 422 && error.error?.errors) {
          // Mapear errores de Laravel a los controles del formulario
          const serverErrors = error.error.errors;

          Object.keys(serverErrors).forEach(key => {
            // Mapear nombre de campo backend -> frontend si es necesario
            let controlName = key;
            if (key === 'nickname') controlName = 'username';

            const control = this.registroForm.get(controlName);
            if (control) {
              // Asignar el error al control para que se muestre en el HTML
              control.setErrors({ serverError: serverErrors[key][0] });
              control.markAsTouched();
            }
          });
        } else {
          // Error general no relacionado con campos específicos
          this.errorMessage.set(error.error?.message || 'Ocurrió un error inesperado al registrarse.');
        }
      }
    });
  }
}