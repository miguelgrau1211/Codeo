import { Component, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { ReporteService } from '../services/reporte.service';
import { TranslatePipe } from '../pipes/translate.pipe';

interface Faq {
  question: string;
  answer: string;
  isFlipped: boolean;
}

@Component({
  selector: 'app-soporte',
  standalone: true,
  imports: [RouterLink, CommonModule, ReactiveFormsModule, TranslatePipe],
  templateUrl: './soporte.html',
  styleUrl: './soporte.css',
})
export class Soporte {
  private fb = inject(FormBuilder);
  private reporteService = inject(ReporteService);

  reportForm: FormGroup;
  isSubmitting = signal(false);
  submitStatus = signal<'success' | 'error' | null>(null);

  constructor() {
    this.reportForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      subject: ['', [Validators.required, Validators.minLength(5)]],
      description: ['', [Validators.required, Validators.minLength(20)]],
      tipo: ['bug', Validators.required] // Por ahora bug por defecto
    });
  }

  faqs = signal<Faq[]>([
    {
      question: "SUPPORT.FAQS.Q1",
      answer: "SUPPORT.FAQS.A1",
      isFlipped: false
    },
    {
      question: "SUPPORT.FAQS.Q2",
      answer: "SUPPORT.FAQS.A2",
      isFlipped: false
    },
    {
      question: "SUPPORT.FAQS.Q3",
      answer: "SUPPORT.FAQS.A3",
      isFlipped: false
    },
    {
      question: "SUPPORT.FAQS.Q4",
      answer: "SUPPORT.FAQS.A4",
      isFlipped: false
    }
  ]);

  toggleFaq(index: number) {
    this.faqs.update(faqs => {
      const newFaqs = [...faqs];
      newFaqs[index].isFlipped = !newFaqs[index].isFlipped;
      return newFaqs;
    });
  }

  enviarReporte() {
    if (this.reportForm.invalid) {
      this.reportForm.markAllAsTouched();
      return;
    }

    this.isSubmitting.set(true);
    this.submitStatus.set(null);

    const formValue = this.reportForm.value;

    this.reporteService.enviarReporte({
      email_contacto: formValue.email,
      titulo: formValue.subject,
      descripcion: formValue.description,
      tipo: formValue.tipo
    }).subscribe({
      next: () => {
        this.isSubmitting.set(false);
        this.submitStatus.set('success');
        this.reportForm.reset({ tipo: 'bug' });
        setTimeout(() => this.submitStatus.set(null), 5000);
      },
      error: () => {
        this.isSubmitting.set(false);
        this.submitStatus.set('error');
      }
    });
  }
}
