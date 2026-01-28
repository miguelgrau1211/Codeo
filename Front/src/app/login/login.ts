import { Component, ElementRef, ViewChild, AfterViewInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule],
  templateUrl: './login.html',
  styles: [`
    video {
      min-height: 105%;
      min-width: 105%;
    }
  `]
})
export class LoginComponent implements AfterViewInit, OnDestroy {
  loginForm: FormGroup;

  @ViewChild('bgCanvas') canvasRef!: ElementRef<HTMLCanvasElement>;
  @ViewChild('sourceVideo') video1Ref!: ElementRef<HTMLVideoElement>;
  @ViewChild('sourceVideo2') video2Ref!: ElementRef<HTMLVideoElement>;

  private animationFrameId: number | null = null;

  // Crossfade config
  private readonly FADE_TIME = 1.5; // seconds duration of crossfade
  private opacity1 = 1;
  private opacity2 = 0;

  constructor(private fb: FormBuilder, private cdr: ChangeDetectorRef) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]]
    });
  }

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

  onSubmit() {
    if (this.loginForm.valid) {
      console.log('Login data:', this.loginForm.value);
      // Aquí iría la lógica de autenticación
    }
  }
}
