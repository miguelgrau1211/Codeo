import { Component, signal, ViewEncapsulation, OnInit, OnDestroy, ChangeDetectionStrategy, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { RoguelikeService } from '../../services/roguelike.service';
import { RoguelikeSessionService, RunStats } from '../../services/roguelike-session.service';
import { EjecutarCodigoService } from '../../services/ejecutar-codigo.service';
import { UserDataService } from '../../services/user-data.service';
import { ThemeService } from '../../services/theme.service';
import { LanguageService } from '../../services/language.service';
import { TranslatePipe } from '../../pipes/translate.pipe';
import { NivelRoguelike } from '../../models/level.model';
import { AuthService } from '../../services/auth.service';

/**
 * Componente del modo infinito (roguelike).
 *
 * Juego de supervivencia con mecánicas roguelike:
 * - Sesión con vidas limitadas y temporizador regresivo.
 * - Niveles aleatorios con dificultad escalada por progresión.
 * - Editor de código con resaltado de sintaxis (Python).
 * - Tienda de mejoras (compra de upgrades con monedas de sesión).
 * - Pantalla de Game Over con estadísticas finales.
 * - Sistema server-authoritative: el servidor valida todo el estado.
 * - Botón de debug (solo admin) para modificar el tiempo.
 *
 * Toda la lógica de sesión se gestiona a través de RoguelikeSessionService.
 */
@Component({
  selector: 'app-modo-infinito',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslatePipe],
  templateUrl: './modo-infinito.component.html',
  styleUrl: './modo-infinito.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None,
})
export class ModoInfinitoComponent implements OnInit, OnDestroy {

  // ==========================================
  // ESTADO DE LA INTERFAZ
  // ==========================================
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);

  // ==========================================
  // DATOS DEL NIVEL
  // ==========================================
  titulo = signal<string>(this.langService.translate('DASHBOARD.LOADING'));
  descripcion = signal<string>('');
  dificultad = signal<string>('fácil');
  testCases = signal<any[]>([]);
  nivelId = signal<number | null>(null);

  // ==========================================
  // ESTADO DEL EDITOR
  // ==========================================
  codeContent = signal('');
  highlightedCode = signal<SafeHtml>('');
  currentLine = signal<number | null>(null);
  lineNumbers = signal<number[]>([1]);

  // ==========================================
  // ESTADO DEL JUEGO (Server-authoritative)
  // ==========================================
  lives = signal(3);
  coins = signal(0);
  nivelesCompletados = signal(0);

  // ==========================================
  // ESTADO DEL TEMPORIZADOR
  // ==========================================
  timeRemaining = signal(300);
  timerPaused = signal(false);
  private timerInterval: ReturnType<typeof setInterval> | null = null;

  // ==========================================
  // ESTADO VISUAL
  // ==========================================
  isExecuting = signal(false);
  isShowingTimeOut = signal(false);
  isFadingOutTimeOut = signal(false);

  // ==========================================
  // FIN DEL JUEGO
  // ==========================================
  showGameOver = signal(false);
  gameOverStats = signal<RunStats | null>(null);

  // ==========================================
  // TIENDA Y MEJORAS
  // ==========================================
  showMejoras = signal(false);
  purchasedUpgrades = signal<{ id: number; icon: string; nombre: string; tipo: string }[]>([]);
  mejorasDisponibles = signal<any[]>([]);
  isLoadingMejoras = signal(false);
  mejoraFeedback = signal<string | null>(null);

  // ==========================================
  // REINICIO Y TUTORIAL
  // ==========================================
  showResetConfirm = signal(false);
  initialCode = signal('');
  showNextLevelButton = signal(false);
  recompensas = signal<any>(null);
  showTutorial = signal(false);

  // ==========================================
  // SALIDA DE EJECUCIÓN
  // ==========================================
  executionResult = signal<any>(null);

  // ==========================================
  // DEPURACIÓN (Solo Admin)
  // ==========================================
  isAdmin = computed(() => this.authService.isAdminSignal());

  constructor(
    private sanitizer: DomSanitizer,
    private roguelikeService: RoguelikeService,
    private roguelikeSessionService: RoguelikeSessionService,
    private ejecutarCodigoService: EjecutarCodigoService,
    private userDataService: UserDataService,
    public themeService: ThemeService,
    private langService: LanguageService,
    private authService: AuthService
  ) {
    this.updateCode(this.codeContent());
  }

  // ==========================================
  // CICLO DE VIDA
  // ==========================================

  ngOnInit() {
    // Validar si el usuario actual ha visto el tutorial (usando nickname único)
    this.userDataService.getUserData().subscribe(user => {
      if (user && user.nickname) {
        const hasSeenTutorial = localStorage.getItem(`roguelike_tutorial_seen_${user.nickname}`);
        if (!hasSeenTutorial) {
          this.showTutorial.set(true);
        }
      }
    });
    
    this.startNewSession();
    
    // Validar admin para el botón de debug
    const token = sessionStorage.getItem('token');
    if (token) {
      this.authService.esAdmin(token).subscribe({
        next: (res) => console.log('Admin check result:', res),
        error: (err) => console.error('Admin check failed:', err)
      });
    }
  }

  ngOnDestroy() {
    this.stopTimer();
  }

  dismissTutorial() {
    const user = this.userDataService.userDataSignal();
    if (user && user.nickname) {
      localStorage.setItem(`roguelike_tutorial_seen_${user.nickname}`, 'true');
    }
    this.showTutorial.set(false);
  }

  // ==========================================
  // FUNCIONES AUXILIARES
  // ==========================================

  getDifficultyKey(): string {
    const map: Record<string, string> = {
      'fácil': 'INFINITE.DIFF_EASY',
      'medio': 'INFINITE.DIFF_MEDIUM',
      'difícil': 'INFINITE.DIFF_HARD',
      'extremo': 'INFINITE.DIFF_EXTREME',
    };
    return map[this.dificultad()] ?? 'INFINITE.DIFF_EASY';
  }

  // ==========================================
  // GESTIÓN DE LA SESIÓN
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
  // GESTIÓN DEL TEMPORIZADOR
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
    // 1. Respuesta instantánea (Optimistic UI + Visual Impact)
    this.stopTimer();
    this.timeRemaining.set(0); 
    this.isShowingTimeOut.set(true);
    this.isFadingOutTimeOut.set(false);
    
    // Restamos vida localmente para feedback inmediato
    const currentLives = this.lives();
    if (currentLives > 0) {
      this.lives.set(currentLives - 1);
    }

    // Limpiar resultado previo para evitar saltos visuales
    this.executionResult.set({
      correcto: false,
      loading: true,
      message: '¡TIEMPO AGOTADO! Sincronizando...',
      detalles: []
    });

    console.log('Timer expired, syncing with server...');
    
    this.roguelikeSessionService.checkTime().subscribe({
      next: (res) => {
        // 2. Sincronización real con el servidor
        this.lives.set(res.lives);
        this.timeRemaining.set(res.time_remaining || 60);

        if (res.game_over) {
          this.isShowingTimeOut.set(false);
          this.triggerGameOver(res.stats!);
        } else {
          // Primero iniciamos el desvanecimiento gradual (fundido a negro desapareciendo)
          this.isFadingOutTimeOut.set(true);
          
          // Esperamos a que la animación de CSS termine antes de limpiar el estado
          setTimeout(() => {
            this.isShowingTimeOut.set(false);
            this.isFadingOutTimeOut.set(false);
            this.startTimer();
            
            // Solo ahora mostramos el mensaje final para evitar glitches visuales
            this.executionResult.set({
              correcto: false,
              message: res.message || this.langService.translate('INFINITE.TIME_EXPIRED') || '¡TIEMPO AGOTADO! Se ha restado una vida y te hemos dado +1 min extra.',
              detalles: [],
            });
          }, 800);
        }
      },
      error: (err) => {
        console.error('Error checking time with server:', err);
        this.isShowingTimeOut.set(false);
        this.isFadingOutTimeOut.set(false);
        // Fallback en caso de error de conexión
        if (this.lives() <= 0) {
          this.triggerGameOver({
            niveles_superados: this.nivelesCompletados(),
            monedas_obtenidas: this.coins(),
            xp_ganada: this.nivelesCompletados() * 25,
            vidas_restantes: 0,
          });
        } else {
          this.timeRemaining.set(60);
          this.startTimer();
          this.executionResult.set({
            correcto: false,
            message: 'Error de conexión, pero te damos otra oportunidad con 60s.',
            detalles: [],
          });
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

  debugSetTime() {
    if (!this.isAdmin()) return;
    this.roguelikeSessionService.debugSetTime().subscribe({
      next: (res) => {
        this.timeRemaining.set(res.time_remaining);
        console.log('Admin Debug: Time set to 10s');
      },
      error: (err) => console.error('Admin Debug Error:', err)
    });
  }

  /**
   * Urgency level for visual effects.
   * 0 = chill (>2min), 1 = warning (1-2min), 2 = danger (30s-1min), 3 = critical (<30s)
   */
  get urgencyLevel(): number {
    const t = this.timeRemaining();
    if (t > 120) return 0;
    if (t > 60) return 1;
    if (t > 30) return 2;
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

    // Safety timeout: if checking takes too long, force error state
    const safetyTimeout = setTimeout(() => {
      if (this.showIntro()) {
        console.error('Level load timeout - forcing UI unlock');
        this.titulo.set(this.langService.translate('INFINITE.ERR_TIMEOUT'));
        this.startExit.set(true);
        this.showIntro.set(false);
      }
    }, 5000);

    this.roguelikeSessionService.startLevel().subscribe({
      next: (res) => {
        clearTimeout(safetyTimeout);
        if (res.game_over) {
          this.triggerGameOver(res.stats!);
          return;
        }

        this.lives.set(res.lives);
        this.timeRemaining.set(res.time_remaining);
        this.nivelesCompletados.set(res.levels_completed || this.nivelesCompletados());

        this.fetchLevel();
      },
      error: () => {
        clearTimeout(safetyTimeout);
        this.fetchLevel();
      },
    });
  }

  private fetchLevel() {
    this.roguelikeService.getNivelAleatorio(this.nivelesCompletados()).subscribe({
      next: (nivel: NivelRoguelike) => {
        if (!nivel) {
          console.error('Nivel inválido (null/undefined)');
          this.titulo.set(this.langService.translate('INFINITE.ERR_DATA'));
          this.startExit.set(true);
          this.showIntro.set(false);
          return;
        }
        this.nivelId.set(nivel.id);
        this.titulo.set(nivel.titulo);
        this.descripcion.set(nivel.descripcion);
        this.dificultad.set(nivel.dificultad);
        this.testCases.set(nivel.test_cases || []);

        const starterCode = this.langService.translate('INFINITE.STARTER_CODE');
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
        this.titulo.set(this.langService.translate('INFINITE.ERR_CONN'));
        this.startExit.set(true);
        this.showIntro.set(false);
      },
    });
  }

  goToNextLevel() {
    this.loadRandomLevel();
  }

  // ==========================================
  // EJECUCIÓN DE CÓDIGO
  // ==========================================

  ejecutarCodigo() {
    const nivelId = this.nivelId();
    const token = sessionStorage.getItem('token');

    if (!nivelId || !token || this.isExecuting()) return;

    this.pauseTimer();
    this.isExecuting.set(true);
    this.executionResult.set({ message: this.langService.translate('INFINITE.EXECUTING_TESTS'), loading: true });

    this.ejecutarCodigoService
      .ejecutarCodigo(this.codeContent(), 'roguelike', nivelId, token)
      .subscribe({
        next: (response) => {
          this.executionResult.set(response);
          this.isExecuting.set(false);

          if (response.correcto) {
            // ÉXITO
            this.stopTimer();
            this.showNextLevelButton.set(true);
            this.nivelesCompletados.update(n => n + 1);

            this.roguelikeSessionService.registerSuccess().subscribe({
              next: (sessionRes) => {
                this.coins.set(sessionRes.coins_earned);

                // Actualizar streak global reactivamente
                if (sessionRes.racha?.streak !== undefined) {
                  this.userDataService.setStreak(sessionRes.racha.streak);
                }

                if (sessionRes.level_up) {
                  this.userDataService.handleLevelUpResult(sessionRes.level_up);
                }
              },
            });

            if (response.recompensas?.xp) {
              this.recompensas.set(response.recompensas);
            }
          } else {
            // FALLO
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
            message: this.langService.translate('INFINITE.ERR_SERVER'),
            detalles: [],
          });
        },
      });
  }

  // ==========================================
  // FIN DE PARTIDA
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
  // TIENDA
  // ==========================================

  buyBox() {
    if (this.coins() < 100) {
      this.mejoraFeedback.set(this.langService.translate('INFINITE.NOT_ENOUGH_COINS'));
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
        this.mejoraFeedback.set(this.langService.translate('INFINITE.ERR_LOADING_UPGRADES'));
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
        const msg = err.error?.message || this.langService.translate('INFINITE.ERR_BUYING_UPGRADE');
        this.mejoraFeedback.set(msg);
        setTimeout(() => this.mejoraFeedback.set(null), 3000);
        this.showMejoras.set(false);
      },
    });
  }

  // ==========================================
  // REINICIO E INTERFAZ
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
  // EDITOR DE CÓDIGO
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
  // RESALTADO DE SINTAXIS (Interno)
  // ==========================================

  /**
   * Genera el HTML enriquecido para aplicar resaltado de sintaxis a partir
   * de código en texto plano utilizando un sistema de regex.
   * Asigna clases CSS específicas (token-*) para la paleta de colores.
   * 
   * @param code El código fuente en texto plano escrito en el editor.
   */
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
    escaped = escaped.replace(/"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*'/g, (match) => createPlaceholder(match, 'token-string'));

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





