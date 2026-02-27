import { Component, signal, ViewEncapsulation, OnInit, effect, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { EjecutarCodigoService } from '../../services/ejecutar-codigo.service';
import { ProgresoHistoriaService } from '../../services/progreso-historia.service';
import { UserDataService } from '../../services/user-data.service';
import { ThemeService } from '../../services/theme.service';
import { LanguageService } from '../../services/language.service';
import { TranslatePipe } from '../../pipes/translate.pipe';

/**
 * Componente del modo historia.
 *
 * Editor de código con navegación secuencial de niveles educativos:
 * - Carga el siguiente nivel no completado automáticamente.
 * - Editor de código con resaltado de sintaxis (Python) y números de línea.
 * - Panel de instrucciones con contenido teórico HTML.
 * - Ejecución de código contra test cases del backend.
 * - Navegación al siguiente nivel tras completar con éxito.
 * - Reseteo del código al estado inicial del nivel.
 *
 * Los datos de progreso se sincronizan con ProgresoHistoriaService.
 */
@Component({
  selector: 'app-modo-historia',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslatePipe],
  templateUrl: './modo-historia.component.html',
  styleUrl: './modo-historia.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None,
})
export class ModoHistoriaComponent implements OnInit {

  // ==========================================
  // ESTADO DE LA INTERFAZ
  // ==========================================
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);

  // ==========================================
  // DATOS DEL NIVEL
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
  // REINICIO Y NAVEGACIÓN
  // ==========================================
  showResetConfirm = signal(false);
  initialCode = signal('');
  showNextLevelButton = signal(false);
  recompensas = signal<any>(null);

  // ==========================================
  // EJECUCIÓN
  // ==========================================
  executionResult = signal<any>(null);

  constructor(
    private sanitizer: DomSanitizer,
    private ejecutarCodigoService: EjecutarCodigoService,
    private progresoHistoriaService: ProgresoHistoriaService,
    private userDataService: UserDataService,
    public themeService: ThemeService,
    private langService: LanguageService
  ) {
    this.updateCode(this.codeContent());

    // Carga de datos reactiva: reacciona a cambios en el signal de progreso
    effect(() => {
      const data = this.progresoHistoriaService.progresoSignal();

      if (!data?.progreso_detallado) return;

      // Buscar el primer nivel no completado
      let nextLevel = data.progreso_detallado.find((l: any) => !l.completado);

      // Si todos están completados, mostrar el último nivel (modo repaso)
      if (!nextLevel && data.progreso_detallado.length > 0) {
        nextLevel = data.progreso_detallado[data.progreso_detallado.length - 1];
      }

      if (!nextLevel) return;

      // Reiniciar estado de la interfaz para el nuevo nivel
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

      // Ocultar pantalla de carga
      setTimeout(() => {
        this.startExit.set(true);
        setTimeout(() => this.showIntro.set(false), 500);
      }, 500);
    });
  }

  // ==========================================
  // CICLO DE VIDA
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
  // REINICIO
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
  // NAVEGACIÓN
  // ==========================================

  goToNextLevel() {
    this.showIntro.set(true);
    this.startExit.set(false);

    this.progresoHistoriaService.getProgresoHistoria().subscribe({
      error: (e) => console.error('Error cargando el siguiente nivel:', e),
    });
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
  // EJECUCIÓN DE CÓDIGO
  // ==========================================

  /**
   * Envía el código escrito por el usuario al servidor para su validación en el modo historia.
   * Modifica el estado `executionResult` para mostrar la pantalla de carga e interactúa
   * con el backend evaluando el código frente a los casos de prueba del nivel.
   *
   * Si las pruebas pasan (response.correcto === true):
   * 1. Llama al servicio de progreso para actualizar la base de datos marcando el nivel como completado.
   * 2. Otorga recompensas (experiencia/monedas), racha, o mensajes de subida de nivel mediante `UserDataService`.
   * 3. Despliega el botón para avanzar al siguiente nivel.
   */
  ejecutarCodigo() {
    const levelId = this.currentLevel();
    const token = sessionStorage.getItem('token');

    if (!levelId || !token) return;

    this.executionResult.set({ message: this.langService.translate('INFINITE.EXECUTING_TESTS'), loading: true });

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
                  this.userDataService.updateEconomy(undefined, res.recompensas.xp);
                }

                if (res.racha?.streak !== undefined) {
                  this.userDataService.setStreak(res.racha.streak);
                }

                if (res.level_up) {
                  this.userDataService.handleLevelUpResult(res.level_up);
                }
              },
              error: (err) => console.error('Error guardando progreso:', err),
            });
          }
        },
        error: () => {
          this.executionResult.set({
            correcto: false,
            message: this.langService.translate('INFINITE.ERR_SERVER'),
            detalles: [],
          });
        },
      });
  }

  // ==========================================
  // RESALTADO DE SINTAXIS (Interno)
  // ==========================================

  /**
   * Genera el HTML enriquecido para aplicar resaltado de sintaxis a partir
   * de código en texto plano utilizando un sistema rudimentario de regex.
   * Se asignan clases CSS específicas (token-*) para la paleta de colores final.
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
      const key = `__PH_${placeholderCounter++}__`;
      placeholders[key] = `<span class="${className}">${content}</span>`;
      return key;
    };

    // 1. Strings y comentarios -> Placeholders
    // Orden importante: Triple comillas -> Simples -> Comentarios

    // Cadenas de texto con comillas triples
    escaped = escaped.replace(/("""[\s\S]*?"""|'''[\s\S]*?''')/g, (match) => createPlaceholder(match, 'token-string'));

    // Cadenas de texto de una línea
    escaped = escaped.replace(/"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*'/g, (match) => createPlaceholder(match, 'token-string'));

    // Comentarios (#...)
    escaped = escaped.replace(/#.*/g, (match) => createPlaceholder(match, 'token-comment'));

    // --- A PARTIR DE AQUI ES SEGURO RESALTAR LOGICA ---

    // 4. Decoradores
    escaped = escaped.replace(/@[a-zA-Z_][a-zA-Z0-9_.]*/g, '<span class="token-variable">$&</span>');

    // 5. Instancias de clase locales
    escaped = escaped.replace(/\b(self|cls)\b/g, '<span class="token-variable">$1</span>');

    // 6. Palabras reservadas (Keywords)
    const keywords = [
      'def', 'return', 'if', 'elif', 'else', 'for', 'while', 'in', 'not', 'and', 'or',
      'is', 'None', 'True', 'False', 'import', 'from', 'as', 'class', 'pass',
      'break', 'continue', 'try', 'except', 'finally', 'raise', 'with',
      'yield', 'lambda', 'global', 'nonlocal', 'del', 'assert',
    ];
    const keywordRegex = new RegExp(`\\b(${keywords.join('|')})\\b`, 'g');
    escaped = escaped.replace(keywordRegex, '<span class="token-keyword">$1</span>');

    // 7. Funciones Built-in
    const builtins = [
      'print', 'len', 'range', 'int', 'str', 'float', 'list', 'dict', 'set',
      'tuple', 'type', 'isinstance', 'input', 'open', 'map', 'filter',
      'zip', 'enumerate', 'sorted', 'reversed', 'abs', 'sum', 'min', 'max',
      'round', 'any', 'all', 'hasattr', 'getattr', 'setattr', 'super',
    ];
    const builtinRegex = new RegExp(`\\b(${builtins.join('|')})(?=\\()`, 'g');
    escaped = escaped.replace(builtinRegex, '<span class="token-function">$1</span>');

    // 8. Llamadas a función generales
    escaped = escaped.replace(/([a-zA-Z_][a-zA-Z0-9_]*)(?=\()/g, '<span class="token-function">$1</span>');

    // 9. Números
    escaped = escaped.replace(/\b\d+\.?\d*\b/g, '<span class="token-number">$&</span>');

    // --- RESTAURAR LOS PLACEHOLDERS ---
    Object.keys(placeholders).reverse().forEach((key) => {
      escaped = escaped.replace(key, placeholders[key]);
    });

    this.highlightedCode.set(this.sanitizer.bypassSecurityTrustHtml(escaped));
  }
}





