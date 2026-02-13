import { Component, signal, ViewEncapsulation, OnInit, effect, ChangeDetectionStrategy } from '@angular/core';
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
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None,
})
export class ModoHistoria implements OnInit {

  // ==========================================
  // UI STATE
  // ==========================================
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);

  // ==========================================
  // LEVEL DATA
  // ==========================================
  codeContent = signal('');
  currentLevel = signal<number | undefined>(undefined);
  orden = signal<number | undefined>(undefined);
  titulo = signal<string | undefined>(undefined);
  highlightedCode = signal<SafeHtml>('');
  descripcion = signal<string | undefined>(undefined);
  contenidoTeorico = signal<SafeHtml | undefined>(undefined);
  testCases = signal<any[]>([]);
  currentLine = signal<number | null>(null);
  lineNumbers = signal<number[]>([1]);

  // ==========================================
  // RESET & NAVIGATION
  // ==========================================
  showResetConfirm = signal(false);
  initialCode = signal('');
  showNextLevelButton = signal(false);
  recompensas = signal<any>(null);

  // ==========================================
  // EXECUTION
  // ==========================================
  executionResult = signal<any>(null);

  constructor(
    private sanitizer: DomSanitizer,
    private ejecutarCodigoService: EjecutarCodigoService,
    private progresoHistoriaService: ProgresoHistoriaService
  ) {
    this.updateCode(this.codeContent());

    // Reactive Data Loading: react to progress signal changes
    effect(() => {
      const data = this.progresoHistoriaService.progresoSignal();

      if (!data?.progreso_detallado) return;

      // Find the first incomplete level
      let nextLevel = data.progreso_detallado.find((l: any) => !l.completado);

      // If all completed, show the last level (review mode)
      if (!nextLevel && data.progreso_detallado.length > 0) {
        nextLevel = data.progreso_detallado[data.progreso_detallado.length - 1];
      }

      if (!nextLevel) return;

      // Reset UI state for new level
      this.showNextLevelButton.set(false);
      this.recompensas.set(null);

      const codigo = nextLevel.codigo_solucion_usuario || '';

      this.codeContent.set(codigo);
      this.updateCode(codigo);

      this.currentLevel.set(nextLevel.nivel_id);
      this.orden.set(nextLevel.orden);
      this.titulo.set(nextLevel.titulo);
      this.descripcion.set(nextLevel.descripcion);
      this.contenidoTeorico.set(
        this.sanitizer.bypassSecurityTrustHtml(nextLevel.contenido_teorico)
      );
      this.testCases.set(nextLevel.test_cases || []);
      this.initialCode.set(nextLevel.codigo_inicial || '');

      // Hide loading screen
      setTimeout(() => {
        this.startExit.set(true);
        setTimeout(() => this.showIntro.set(false), 500);
      }, 500);
    });
  }

  // ==========================================
  // LIFECYCLE
  // ==========================================

  ngOnInit() {
    if (!this.progresoHistoriaService.progresoSignal()) {
      this.progresoHistoriaService.getProgresoHistoria().subscribe({
        error: () => {
          this.startExit.set(true);
          this.showIntro.set(false);
        },
      });
    }
  }

  // ==========================================
  // RESET
  // ==========================================

  requestReset() {
    this.showResetConfirm.set(true);
  }

  confirmReset() {
    const code = this.initialCode();
    this.codeContent.set(code);
    this.updateCode(code);
    this.showResetConfirm.set(false);
    this.executionResult.set(null);
    this.showNextLevelButton.set(false);
    this.recompensas.set(null);
  }

  cancelReset() {
    this.showResetConfirm.set(false);
  }

  // ==========================================
  // NAVIGATION
  // ==========================================

  goToNextLevel() {
    this.showIntro.set(true);
    this.startExit.set(false);

    this.progresoHistoriaService.getProgresoHistoria().subscribe({
      error: (e) => console.error('Error loading next level:', e),
    });
  }

  toggleInstructions() {
    this.isInstructionsOpen.update(v => !v);
  }

  // ==========================================
  // EDITOR
  // ==========================================

  onCodeInput(event: Event) {
    const text = (event.target as HTMLTextAreaElement).value;
    this.codeContent.set(text);
    this.updateCode(text);
    this.onCursorActivity(event);
  }

  onCursorActivity(event: Event) {
    const textarea = event.target as HTMLTextAreaElement;
    const selectionStart = textarea.selectionStart;
    const line = textarea.value.substring(0, selectionStart).split('\n').length - 1;
    this.currentLine.set(line);
  }

  onBlur() {
    this.currentLine.set(null);
  }

  onFocus(event: Event) {
    setTimeout(() => this.onCursorActivity(event), 0);
  }

  syncScroll(event: Event, lineNumbers: HTMLElement, scrollContainer: HTMLElement) {
    const textarea = event.target as HTMLTextAreaElement;
    lineNumbers.scrollTop = textarea.scrollTop;
    scrollContainer.scrollTop = textarea.scrollTop;
    scrollContainer.scrollLeft = textarea.scrollLeft;
  }

  // ==========================================
  // CODE EXECUTION
  // ==========================================

  ejecutarCodigo() {
    const levelId = this.currentLevel();
    const token = sessionStorage.getItem('token');

    if (!levelId || !token) return;

    this.executionResult.set({ message: 'Ejecutando tests...', loading: true });

    this.ejecutarCodigoService
      .ejecutarCodigo(this.codeContent(), 'historia', levelId, token)
      .subscribe({
        next: (response) => {
          this.executionResult.set(response);

          if (response.correcto) {
            const progreso = {
              nivel_id: this.currentLevel(),
              completado: true,
              codigo_solucion_usuario: this.codeContent(),
            };

            this.progresoHistoriaService.updateProgresoHistoria(progreso).subscribe({
              next: (res) => {
                this.showNextLevelButton.set(true);

                if (res.recompensas?.xp) {
                  this.recompensas.set(res.recompensas);
                }
              },
              error: (err) => console.error('Error guardando progreso:', err),
            });
          }
        },
        error: () => {
          this.executionResult.set({
            correcto: false,
            message: 'Error al conectar con el servidor.',
            detalles: [],
          });
        },
      });
  }

  // ==========================================
  // SYNTAX HIGHLIGHTING (private)
  // ==========================================

  private updateCode(code: string) {
    const lines = code.split('\n').length;
    this.lineNumbers.set(Array.from({ length: lines }, (_, i) => i + 1));

    let escaped = code
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

    const placeholders: Record<string, string> = {};
    let placeholderCounter = 0;

    const createPlaceholder = (content: string, className: string) => {
      const key = `__PH-${placeholderCounter++}__`;
      placeholders[key] = `<span class="${className}">${content}</span>`;
      return key;
    };

    // 1. Strings (Triple & Single) & Comments -> Placeholders
    // Order matters: Triple quotes -> Single quotes -> Comments
    
    // Triple-quoted strings
    escaped = escaped.replace(/("""[\s\S]*?"""|'''[\s\S]*?''')/g, (match) => createPlaceholder(match, 'token-string'));

    // Single-line strings
    escaped = escaped.replace(/(['"])(?:(?=(\\?))\\2.)*?\1/g, (match) => createPlaceholder(match, 'token-string'));

    // Comments
    escaped = escaped.replace(/#.*/g, (match) => createPlaceholder(match, 'token-comment'));

    // --- NOW IT IS SAFE TO HIGHLIGHT KEYWORDS & LOGIC ---

    // 4. Decorators
    escaped = escaped.replace(/@[a-zA-Z_][a-zA-Z0-9_.]*/g, '<span class="token-variable">$&</span>');

    // 5. self / cls
    escaped = escaped.replace(/\b(self|cls)\b/g, '<span class="token-variable">$1</span>');

    // 6. Keywords
    const keywords = [
      'def', 'return', 'if', 'elif', 'else', 'for', 'while', 'in', 'not', 'and', 'or',
      'is', 'None', 'True', 'False', 'import', 'from', 'as', 'class', 'pass',
      'break', 'continue', 'try', 'except', 'finally', 'raise', 'with',
      'yield', 'lambda', 'global', 'nonlocal', 'del', 'assert',
    ];
    const keywordRegex = new RegExp(`\\b(${keywords.join('|')})\\b`, 'g');
    escaped = escaped.replace(keywordRegex, '<span class="token-keyword">$1</span>');

    // 7. Built-in functions
    const builtins = [
      'print', 'len', 'range', 'int', 'str', 'float', 'list', 'dict', 'set',
      'tuple', 'type', 'isinstance', 'input', 'open', 'map', 'filter',
      'zip', 'enumerate', 'sorted', 'reversed', 'abs', 'sum', 'min', 'max',
      'round', 'any', 'all', 'hasattr', 'getattr', 'setattr', 'super',
    ];
    const builtinRegex = new RegExp(`\\b(${builtins.join('|')})(?=\\()`, 'g');
    escaped = escaped.replace(builtinRegex, '<span class="token-function">$1</span>');

    // 8. Other function calls
    escaped = escaped.replace(/([a-zA-Z_][a-zA-Z0-9_]*)(?=\()/g, '<span class="token-function">$1</span>');

    // 9. Numbers
    escaped = escaped.replace(/\b\d+\.?\d*\b/g, '<span class="token-number">$&</span>');

    // --- RESTORE PLACEHOLDERS ---
    Object.keys(placeholders).forEach((key) => {
      escaped = escaped.replace(key, placeholders[key]);
    });

    this.highlightedCode.set(this.sanitizer.bypassSecurityTrustHtml(escaped));
  }
}
