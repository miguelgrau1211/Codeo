import { Component, signal } from '@angular/core';

interface Faq {
  question: string;
  answer: string;
  isFlipped: boolean;
}

import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-soporte',
  imports: [RouterLink, CommonModule],
  templateUrl: './soporte.html',
  styleUrl: './soporte.css',
})
export class Soporte {
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
}
