import { Component, signal, ViewEncapsulation, OnInit, OnDestroy, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { RoguelikeService, NivelRoguelike } from '../services/roguelike-service';
import { RoguelikeSessionService, RunStats } from '../services/roguelike-session-service';
import { EjecutarCodigoService } from '../services/ejecutar-codigo-service';

@Component({
  selector: 'app-modo-infinito',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './modo-infinito.html',
  styleUrl: './modo-infinito.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None,
})
export class ModoInfinito implements OnInit, OnDestroy {

  // ==========================================
  // UI STATE
  // ==========================================
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);

  // ==========================================
  // LEVEL DATA
  // ==========================================
  titulo = signal<string>('Cargando...');
  descripcion = signal<string>('');
  dificultad = signal<string>('fácil');
  testCases = signal<any[]>([]);
  nivelId = signal<number | null>(null);

  // ==========================================
  // EDITOR STATE
  // ==========================================
  codeContent = signal('');
  highlightedCode = signal<SafeHtml>('');
  currentLine = signal<number | null>(null);
  lineNumbers = signal<number[]>([1]);

  // ==========================================
  // GAME STATE (Server-authoritative)
  // ==========================================
  lives = signal(3);
  coins = signal(0);
  nivelesCompletados = signal(0);

  // ==========================================
  // TIMER STATE
  // ==========================================
  timeRemaining = signal(300);
  timerPaused = signal(false);
  private timerInterval: ReturnType<typeof setInterval> | null = null;

  // ==========================================
  // VISUAL STATE
  // ==========================================
  isExecuting = signal(false);

  // ==========================================
  // GAME OVER
  // ==========================================
  showGameOver = signal(false);
  gameOverStats = signal<RunStats | null>(null);

  // ==========================================
  // SHOP & UPGRADES
  // ==========================================
  showMejoras = signal(false);
  purchasedUpgrades = signal<{ id: number; icon: string; nombre: string; tipo: string }[]>([]);
  mejorasDisponibles = signal<any[]>([]);
  isLoadingMejoras = signal(false);
  mejoraFeedback = signal<string | null>(null);

  // ==========================================
  // RESET
  // ==========================================
  showResetConfirm = signal(false);
  initialCode = signal('');
  showNextLevelButton = signal(false);
  recompensas = signal<any>(null);

  // ==========================================
  // EXECUTION OUTPUT
  // ==========================================
  executionResult = signal<any>(null);

  constructor(
    private sanitizer: DomSanitizer,
    private roguelikeService: RoguelikeService,
    private roguelikeSessionService: RoguelikeSessionService,
    private ejecutarCodigoService: EjecutarCodigoService
  ) {
    this.updateCode(this.codeContent());
  }

  // ==========================================
  // LIFECYCLE
  // ==========================================

  ngOnInit() {
    this.startNewSession();
  }

  ngOnDestroy() {
    this.stopTimer();
  }

  // ==========================================
  // SESSION MANAGEMENT
  // ==========================================

  startNewSession() {
    this.roguelikeSessionService.startSession().subscribe({
      next: (res) => {
        this.lives.set(res.lives);
        this.timeRemaining.set(res.time_remaining);
        this.showGameOver.set(false);
        this.gameOverStats.set(null);
        this.nivelesCompletados.set(0);
        this.coins.set(0);
        this.loadRandomLevel();
      },
      error: () => {
        // Fallback: start with defaults
        this.loadRandomLevel();
      },
    });
  }

  // ==========================================
  // TIMER MANAGEMENT
  // ==========================================

  startTimer() {
    this.stopTimer();
    this.timerPaused.set(false);

    this.timerInterval = setInterval(() => {
      if (this.timerPaused()) return;

      const remaining = this.timeRemaining();
      if (remaining <= 1) {
        this.timeRemaining.set(0);
        this.stopTimer();
        this.onTimeExpired();
      } else {
        this.timeRemaining.update(t => t - 1);
      }
    }, 1000);
  }

  stopTimer() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
      this.timerInterval = null;
    }
  }

  pauseTimer() {
    this.timerPaused.set(true);
  }

  resumeTimer() {
    this.timerPaused.set(false);
  }

  onTimeExpired() {
    this.roguelikeSessionService.checkTime().subscribe({
      next: (res) => {
        if (res.time_expired) {
          this.lives.set(res.lives);

          if (res.game_over) {
            this.triggerGameOver(res.stats!);
          } else {
            // Reiniciar timer a 90 segundos (respuesta del servidor)
            this.timeRemaining.set(res.time_remaining ?? 90);
            this.startTimer();

            this.executionResult.set({
              correcto: false,
              message: '⏱️ ¡Se acabó el tiempo! Pierdes una vida. Tienes 1:30 extra.',
              detalles: [],
            });
          }
        }
      },
      error: () => {
        // Fallback: lose a life locally, reset to 90s
        this.lives.update(l => Math.max(0, l - 1));
        if (this.lives() <= 0) {
          this.triggerGameOver({
            niveles_superados: this.nivelesCompletados(),
            monedas_obtenidas: this.coins(),
            xp_ganada: this.nivelesCompletados() * 25,
            vidas_restantes: 0,
          });
        } else {
          this.timeRemaining.set(90);
          this.startTimer();
        }
      },
    });
  }

  /** Formatted time for template display (MM:SS). */
  get formattedTime(): string {
    const total = this.timeRemaining();
    const min = Math.floor(total / 60);
    const sec = total % 60;
    return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
  }

  /**
   * Urgency level for visual effects.
   * 0 = chill (>2min), 1 = warning (1-2min), 2 = danger (30s-1min), 3 = critical (<30s)
   */
  get urgencyLevel(): number {
    const t = this.timeRemaining();
    if (t > 120) return 0;
    if (t > 60)  return 1;
    if (t > 30)  return 2;
    return 3;
  }

  // ==========================================
  // LEVEL MANAGEMENT
  // ==========================================

  loadRandomLevel() {
    this.showIntro.set(true);
    this.startExit.set(false);
    this.showNextLevelButton.set(false);
    this.executionResult.set(null);
    this.recompensas.set(null);
    this.stopTimer();

    this.roguelikeSessionService.startLevel().subscribe({
      next: (res) => {
        if (res.game_over) {
          this.triggerGameOver(res.stats!);
          return;
        }

        this.lives.set(res.lives);
        this.timeRemaining.set(res.time_remaining);
        this.nivelesCompletados.set(res.levels_completed || this.nivelesCompletados());

        this.fetchLevel();
      },
      error: () => this.fetchLevel(),
    });
  }

  private fetchLevel() {
    this.roguelikeService.getNivelAleatorio(this.nivelesCompletados()).subscribe({
      next: (nivel: NivelRoguelike) => {
        this.nivelId.set(nivel.id);
        this.titulo.set(nivel.titulo);
        this.descripcion.set(nivel.descripcion);
        this.dificultad.set(nivel.dificultad);
        this.testCases.set(nivel.test_cases || []);

        const starterCode = `# La variable input contiene los datos de entrada\n# Tu código aquí\n`;
        this.initialCode.set(starterCode);
        this.codeContent.set(starterCode);
        this.updateCode(starterCode);

        // Hide loading screen
        setTimeout(() => {
          this.startExit.set(true);
          setTimeout(() => {
            this.showIntro.set(false);
            this.startTimer();
          }, 500);
        }, 1000);
      },
      error: () => {
        this.titulo.set('Error de conexión');
        this.startExit.set(true);
        this.showIntro.set(false);
      },
    });
  }

  goToNextLevel() {
    this.loadRandomLevel();
  }

  // ==========================================
  // CODE EXECUTION
  // ==========================================

  ejecutarCodigo() {
    const nivelId = this.nivelId();
    const token = sessionStorage.getItem('token');

    if (!nivelId || !token || this.isExecuting()) return;

    this.pauseTimer();
    this.isExecuting.set(true);
    this.executionResult.set({ message: 'Ejecutando tests...', loading: true });

    this.ejecutarCodigoService
      .ejecutarCodigo(this.codeContent(), 'roguelike', nivelId, token)
      .subscribe({
        next: (response) => {
          this.executionResult.set(response);
          this.isExecuting.set(false);

          if (response.correcto) {
            // SUCCESS
            this.stopTimer();
            this.showNextLevelButton.set(true);
            this.nivelesCompletados.update(n => n + 1);

            this.roguelikeSessionService.registerSuccess().subscribe({
              next: (sessionRes) => {
                this.coins.set(sessionRes.coins_earned);
              },
            });

            if (response.recompensas?.xp) {
              this.recompensas.set(response.recompensas);
            }
          } else {
            // FAILURE
            this.resumeTimer();

            this.roguelikeSessionService.registerFailure().subscribe({
              next: (sessionRes) => {
                this.lives.set(sessionRes.lives);

                if (sessionRes.game_over) {
                  this.stopTimer();
                  this.triggerGameOver(sessionRes.stats!);
                }
              },
              error: () => {
                this.lives.update(l => Math.max(0, l - 1));
                if (this.lives() <= 0) {
                  this.stopTimer();
                  this.triggerGameOver({
                    niveles_superados: this.nivelesCompletados(),
                    monedas_obtenidas: this.coins(),
                    xp_ganada: this.nivelesCompletados() * 25,
                    vidas_restantes: 0,
                  });
                }
              },
            });
          }
        },
        error: () => {
          this.isExecuting.set(false);
          this.resumeTimer();
          this.executionResult.set({
            correcto: false,
            message: 'Error al conectar con el servidor.',
            detalles: [],
          });
        },
      });
  }

  // ==========================================
  // GAME OVER
  // ==========================================

  triggerGameOver(stats: RunStats) {
    this.stopTimer();
    this.showGameOver.set(true);
    this.gameOverStats.set(stats);
  }

  restartGame() {
    this.showGameOver.set(false);
    this.gameOverStats.set(null);
    this.lives.set(3);
    this.coins.set(0);
    this.nivelesCompletados.set(0);
    this.executionResult.set(null);
    this.purchasedUpgrades.set([]);
    this.mejoraFeedback.set(null);
    this.startNewSession();
  }

  // ==========================================
  // SHOP
  // ==========================================

  buyBox() {
    if (this.coins() < 100) {
      this.mejoraFeedback.set('No tienes suficientes monedas (Cuesta 100)');
      setTimeout(() => this.mejoraFeedback.set(null), 3000);
      return;
    }

    this.isLoadingMejoras.set(true);

    this.roguelikeSessionService.getMejorasRandom().subscribe({
      next: (mejoras) => {
        this.mejorasDisponibles.set(mejoras);
        this.isLoadingMejoras.set(false);
        this.showMejoras.set(true);
      },
      error: () => {
        this.isLoadingMejoras.set(false);
        this.mejoraFeedback.set('Error al cargar mejoras.');
        setTimeout(() => this.mejoraFeedback.set(null), 3000);
      },
    });
  }

  selectUpgrade(mejora: any) {
    this.roguelikeSessionService.buyMejora(mejora.id).subscribe({
      next: (res) => {
        // Sync session state from server
        this.lives.set(res.lives);
        this.coins.set(res.coins_earned);

        // Sync time if tiempo_extra was applied
        if (mejora.tipo === 'tiempo_extra') {
          this.timeRemaining.set(res.time_remaining);
        }

        // Add to active upgrades list
        this.purchasedUpgrades.set(res.mejoras_activas || []);

        // Show feedback
        this.mejoraFeedback.set(res.efecto);
        setTimeout(() => this.mejoraFeedback.set(null), 4000);

        this.showMejoras.set(false);
      },
      error: (err) => {
        const msg = err.error?.message || 'Error al comprar mejora.';
        this.mejoraFeedback.set(msg);
        setTimeout(() => this.mejoraFeedback.set(null), 3000);
        this.showMejoras.set(false);
      },
    });
  }

  // ==========================================
  // RESET & UI
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
  }

  cancelReset() {
    this.showResetConfirm.set(false);
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
