import { Component, signal, ViewEncapsulation, OnInit, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { RoguelikeService, NivelRoguelike } from '../services/roguelike-service';
import { EjecutarCodigoService } from '../services/ejecutar-codigo-service';

@Component({
  selector: 'app-modo-infinito',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './modo-infinito.html',
  styleUrl: './modo-infinito.css',
  encapsulation: ViewEncapsulation.None 
})
export class ModoInfinito implements OnInit {
  
  // UI State
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);
  
  // Level Data
  titulo = signal<string>('Cargando...');
  descripcion = signal<string>('');
  testCases = signal<any[]>([]);
  nivelId = signal<number | null>(null);
  
  // Editor State
  // Default template since DB lacks initial code field for now
  codeContent = signal('');

  highlightedCode = signal<SafeHtml>('');
  currentLine = signal<number | null>(null);
  lineNumbers = signal<number[]>([1]);
  
  // Game State
  lives = signal(3);
  coins = signal(150);
  time = signal('05:00');
  nivelesCompletados = signal(0);
  
  // Shop & Upgrades
  showMejoras = signal(false);
  purchasedUpgrades = signal<{icon: string, name: string, desc: string}[]>([]);
  
  // Mock Upgrades options
  upgradeOptions = [
    { icon: '⚡', name: 'Compilador Turbo', desc: 'Tu código se ejecuta un 20% más rápido.' },
    { icon: '🛡️', name: 'Escudo de Sintaxis', desc: 'Ignora el primer error de compilación.' },
    { icon: '💰', name: 'Minería de Datos', desc: 'Ganas +50% de monedas por nivel.' }
  ];

  // Reset functionality
  showResetConfirm = signal(false);
  initialCode = signal('');
  
  // Next Level Button
  showNextLevelButton = signal(false);

  constructor(
    private sanitizer: DomSanitizer,
    private roguelikeService: RoguelikeService,
    private ejecutarCodigoService: EjecutarCodigoService
  ) {
    // Initial highlight
    this.updateCode(this.codeContent());
  }

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

  ngOnInit() {
    this.loadRandomLevel();
  }

  loadRandomLevel() {
    // Show loading state
    this.showIntro.set(true);
    this.startExit.set(false);
    this.showNextLevelButton.set(false);
    this.executionResult.set(null);

    // 1. Fetch Random Level
    // 1. Fetch Random Level
    this.roguelikeService.getNivelAleatorio(this.nivelesCompletados()).subscribe({
        next: (nivel: NivelRoguelike) => {
            this.nivelId.set(nivel.id);
            this.titulo.set(nivel.titulo);
            this.descripcion.set(nivel.descripcion);
            this.testCases.set(nivel.test_cases || []);
            
            // If DB had initial code, we would set it here. For now, keep default template.
            this.initialCode.set(`# La variable $input_data contiene los datos de entrada
# Tu código aquí
`);
            this.codeContent.set(this.initialCode());
            
            this.updateCode(this.codeContent());
            
            // 2. Hide Loading Screen
            setTimeout(() => {
                this.startExit.set(true); 
                setTimeout(() => this.showIntro.set(false), 500);
            }, 1000); // Simulate brief load
        },
        error: (err) => {
            console.error('Error loading roguelike level', err);
            this.titulo.set('Error de conexión');
            this.startExit.set(true);
            this.showIntro.set(false);
        }
    });
  }

  goToNextLevel() {
      this.loadRandomLevel();
  }
  
  buyBox() {
    if (this.coins() >= 100) {
        this.coins.update(c => c - 100);
        this.showMejoras.set(true);
    } else {
        alert("No tienes suficientes monedas (Cuesta 100)");
    }
  }

  selectUpgrade(upgrade: any) {
    this.purchasedUpgrades.update(list => [...list, upgrade]);
    this.showMejoras.set(false);
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

    // 2. Comments (Grey) - # ...
    escaped = escaped.replace(/#.*/g, '<span class="token-comment">$&</span>');

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

  // Execution Output
  executionResult = signal<any>(null);

  ejecutarCodigo() {
      if (!this.nivelId()) {
          console.error("No level loaded");
          return;
      }
      
      const token = sessionStorage.getItem("token") || '';
      this.executionResult.set({ message: 'Ejecutando tests...', loading: true });
      
      this.ejecutarCodigoService.ejecutarCodigo(this.codeContent(), 'roguelike', this.nivelId()!, token).subscribe({
        next: (response) => {
            console.log("Resultado Ejecución:", response);
            this.executionResult.set(response);

            if (response.correcto) {
                this.showNextLevelButton.set(true);
                this.nivelesCompletados.update(n => n + 1);
            }
        },
        error: (err) => {
            console.error(err);
            this.executionResult.set({ 
                correcto: false, 
                message: 'Error al conectar con el servidor.',
                detalles: []
            });
        }
      });
  }
}
