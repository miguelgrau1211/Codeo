import { Component, signal, ViewEncapsulation, OnInit, Input, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { EjecutarCodigoService } from '../services/ejecutar-codigo-service';
import { ProgresoHistoriaService } from '../services/progreso-historia-service';

@Component({
  selector: 'app-modo-historia',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './modo-historia.html',
  styleUrl: './modo-historia.css',
  encapsulation: ViewEncapsulation.None // Needed for dynamic syntax regex classes to apply
})
export class ModoHistoria implements OnInit {
  
  // Signals
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);
  
  // Initial state empty, populated by effect
  codeContent = signal('');
  currentLevel = signal<number | undefined>(undefined);
  orden = signal<number | undefined>(undefined);
  titulo = signal<string | undefined>(undefined);  
  highlightedCode = signal<SafeHtml>('');
  descripcion = signal<string | undefined>(undefined);
  contenidoTeorico = signal<SafeHtml | undefined>(undefined);
  currentLine = signal<number | null>(null);
  lineNumbers = signal<number[]>([1]);
  scrollTop = signal(0); // Track scroll position

  constructor(
    private sanitizer: DomSanitizer, 
    private ejecutarCodigoService: EjecutarCodigoService, 
    private progresoHistoriaService: ProgresoHistoriaService
  ) {
    // Initial highlight (empty)
    this.updateCode(this.codeContent());

    // Reactive Data Loading
    effect(() => {
        const data = this.progresoHistoriaService.progresoSignal();
        
        // If we have data
        if (data && data.progreso_detallado) {
             const ultimoNivel = data.progreso_detallado.length 
                ? data.progreso_detallado[data.progreso_detallado.length - 1]
                : null;

             if (ultimoNivel) {
                 const codigo = ultimoNivel.codigo_solucion_usuario || '';
                 
                 // Update Content
                 this.codeContent.set(codigo);
                 this.updateCode(codigo);
                 
                 // Update Level Info
                 const nivelId = ultimoNivel.nivel_id;
                 
                 
                 this.currentLevel.set(nivelId);
                 this.orden.set(ultimoNivel.orden);
                 this.titulo.set(ultimoNivel.titulo);
                 this.descripcion.set(ultimoNivel.descripcion);
                 this.contenidoTeorico.set(this.sanitizer.bypassSecurityTrustHtml(ultimoNivel.contenido_teorico));
                 
                 // Hide Loading Screen safely
                 setTimeout(() => {
                     this.startExit.set(true); 
                     setTimeout(() => this.showIntro.set(false), 500); // Wait for transition
                 }, 500); // Optional small delay for smoothness
             }
        }
    });
  }

  ngOnInit() {
    // Auto-fetch logic for page refresh
    if (!this.progresoHistoriaService.progresoSignal()) {
        console.log('ModoHistoria: No progress signal detected, fetching from API...');
        this.progresoHistoriaService.getProgresoHistoria().subscribe({
            next: (data) => console.log('ModoHistoria: Progress fetched successfully'),
            error: (err) => {
                console.error('ModoHistoria: Error fetching progress', err);
                // On error, maybe show intro indefinitely or show error message?
                // For now let's hide intro to not block user
                 this.startExit.set(true); 
                 this.showIntro.set(false);
            }
        });
    }
  }
  
  toggleInstructions() {
    this.isInstructionsOpen.update(v => !v);
  }

  onCodeInput(event: Event) {
    const text = (event.target as HTMLTextAreaElement).value;
    this.codeContent.set(text);
    this.updateCode(text);
    this.onCursorActivity(event);
  }

  onCursorActivity(event: Event) {
    const textarea = event.target as HTMLTextAreaElement;
    const value = textarea.value;
    const selectionStart = textarea.selectionStart;
    
    // Calculate current line number based on cursor position
    const line = value.substring(0, selectionStart).split("\n").length - 1;
    this.currentLine.set(line);
  }

  onBlur() {
    this.currentLine.set(null);
  }

  onFocus(event: Event) {
    // Wait 0ms to allow the browser to update the selectionStart position after the focus event
    // This prevents the highlight from briefly flashing on line 0 before jumping to the clicked line
    setTimeout(() => {
        this.onCursorActivity(event);
    }, 0);
  }

  // Scroll sync: when textarea scrolls, scroll the pre block too AND the line numbers
  syncScroll(event: Event, lineNumbers: HTMLElement, scrollContainer: HTMLElement) {
    const textarea = event.target as HTMLTextAreaElement;
    
    // Sync Vertical
    lineNumbers.scrollTop = textarea.scrollTop;
    scrollContainer.scrollTop = textarea.scrollTop;
    
    // Sync Horizontal
    scrollContainer.scrollLeft = textarea.scrollLeft;
  }

  private updateCode(code: string) {
    // Update Line Numbers
    const lines = code.split('\n').length;
    this.lineNumbers.set(Array.from({ length: lines }, (_, i) => i + 1));

    // Escape HTML to prevent injection before highlighting
    let escaped = code
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Syntax Highlighting Rules (Simple Regex-based)
    // Order matters! Strings and comments first to avoid matching keywords inside them.

    // 1. Strings (Green) - '...' or "..."
    escaped = escaped.replace(/(['"])(?:(?=(\\?))\2.)*?\1/g, '<span class="token-string">$&</span>');

    // 2. Comments (Grey) - // ...
    escaped = escaped.replace(/\/\/.*/g, '<span class="token-comment">$&</span>');

    // 3. PHP Variables (Red) - $var
    escaped = escaped.replace(/\$[a-zA-Z0-9_]+/g, '<span class="token-variable">$&</span>');

    // 4. Keywords (Purple)
    // Remove 'class' from this list to handle it separately
    const keywords = ['function', 'return', 'if', 'else', 'foreach', 'as', 'null', 'true', 'false', 'public', 'private', 'new', 'extends'];
    const keywordRegex = new RegExp(`\\b(${keywords.join('|')})\\b`, 'g');
    escaped = escaped.replace(keywordRegex, '<span class="token-keyword">$1</span>');

    // Handle 'class' keyword checking it's not part of an HTML attribute (not followed by =)
    escaped = escaped.replace(/\bclass\b(?!=)/g, '<span class="token-keyword">class</span>');

    // 5. Functions Declarations/Calls (Blue) - funcName(
    escaped = escaped.replace(/([a-zA-Z0-9_]+)(?=\()/g, '<span class="token-function">$1</span>');

    // 6. Numbers (Orange)
    escaped = escaped.replace(/\b\d+\b/g, '<span class="token-number">$&</span>');

    this.highlightedCode.set(this.sanitizer.bypassSecurityTrustHtml(escaped));
  }

  @Input() nivel: number = 1;

  ejecutarCodigo() {
    console.log(this.codeContent());
    this.ejecutarCodigoService.ejecutarCodigo(this.codeContent(), 'historia', this.nivel, sessionStorage.getItem("token")!).subscribe({
      next: (response) => {
        console.log(response);
      },
      error: (error) => {
        console.error(error);
      }
    });
  }
}
