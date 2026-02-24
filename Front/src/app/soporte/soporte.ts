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
      question: "¿Cuánto tarda en responderse un report?",
      answer: "Nuestro equipo revisa los reportes diariamente. Generalmente recibes respuesta en 24-48 horas laborables.",
      isFlipped: false
    },
    {
      question: "¿Cómo puedo resetear mi progreso?",
      answer: "Ve a Ajustes > Zona de Peligro > Resetear Progreso. Ten cuidado, esta acción no se puede deshacer.",
      isFlipped: false
    },
    {
      question: "¿Gano algo por reportar bugs?",
      answer: "¡Sí! Si el bug es validado, recibirás créditos y XP como recompensa por ayudar a mejorar Codeo.",
      isFlipped: false
    },
    {
      question: "¿Puedo contribuir al código?",
      answer: "Actualmente el core es privado, pero puedes contribuir reportando issues o creando contenido para la comunidad.",
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
