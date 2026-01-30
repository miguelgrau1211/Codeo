import { Component, signal, ViewEncapsulation, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Component({
  selector: 'app-modo-infinito',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './modo-infinito.html',
  styleUrl: './modo-infinito.css',
  encapsulation: ViewEncapsulation.None // Needed for dynamic syntax regex classes to apply
})
export class ModoInfinito implements OnInit {
  showIntro = signal(true);
  startExit = signal(false);
  isInstructionsOpen = signal(true);
  codeContent = signal(`function findHighestPrime($numbers) {
    $highest = null;

    foreach ($numbers as $num) {
        // Tu lógica aquí
        if (isPrime($num)) {
            $highest = $num;
        }
    }

    return $highest;
}`);
  highlightedCode = signal<SafeHtml>('');
  currentLine = signal<number | null>(null);
  lineNumbers = signal<number[]>([1]);
  scrollTop = signal(0); // Track scroll position

  constructor(private sanitizer: DomSanitizer) {
    // Initial highlight
    this.updateCode(this.codeContent());
  }

  ngOnInit() {
    // Reveal editor after animation
    setTimeout(() => {
        this.startExit.set(true); // Fade out overlay
    }, 2000);
    setTimeout(() => {
        this.showIntro.set(false); // Remove from DOM
    }, 2500);
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
}
