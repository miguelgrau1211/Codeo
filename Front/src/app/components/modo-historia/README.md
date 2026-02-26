# Modo Historia (`modo-historia.component.ts`)

## Descripción General
El **Modo Historia** es el principal motor educativo de *Codeo*. Consiste en un editor de código interactivo donde el jugador resuelve niveles secuenciales, guiados por una narrativa o conceptos de programación.

## Flujo de Funcionamiento
1. **Carga Reactiva del Nivel**: El componente está suscrito al estado global del progreso mediante la API `effect()` de Angular Signals.
2. **Interacción del Usuario**: El usuario lee el `contenidoTeorico` (renderizado de forma segura usando `DomSanitizer`) y escribe su solución en el editor.
3. **Resaltado en Tiempo Real**: Con cada tipeo, un sistema interno de *Micro-Parsing* colorea la sintaxis y mantiene los números de línea sincronizados.
4. **Ejecución y Validación**: El código se envía al backend, se validan los casos de prueba y, si el resultado estadístico es `correcto`, se actualiza la interfaz (se restan/suman XP y variables de economía) de forma reactiva, desplegando el botón para el próximo nivel.

## Elementos Complejos Explicados

### 1. Sistema Reactivo de Carga (`effect()`)
En Angular 17+, `effect` permite ejecutar código automáticamente cuando una `Signal` dependiente cambia. Aquí lo usamos para cargar automáticamente el siguiente nivel no completado en cuanto detectamos que la lista global de progreso ha cambiado (por ejemplo, porque acabamos de superar un nivel).

```typescript
effect(() => {
  const data = this.progresoHistoriaService.progresoSignal();
  if (!data?.progreso_detallado) return;

  // Busca el primer nivel sin completar
  let nextLevel = data.progreso_detallado.find((l: any) => !l.completado);

  // Si no hay ninguno, el usuario se ha pasado el juego.
  if (!nextLevel) return;

  // Rellenar las variables del entorno
  this.codeContent.set(nextLevel.codigo_inicial || '');
  this.currentLevel.set(nextLevel.nivel_id);
});
```

### 2. Micro-Parser de Resaltado de Sintaxis
Para evitar dependencias pesadas como *Monaco Editor*, hemos construido un resaltador visual simulado superponiendo un `<pre>` (que tiene el código a color) encima y detrás del `<textarea>` invisible pero funcional.

Lo complejo aquí es el *parsing* sin romper elementos HTML, como cuando alguien usa `<` o `>` en Python.

Primero "limpiamos" el código, transformando caracteres especiales a entidades HTML, luego reemplazamos palabras clave:

```typescript
private updateCode(code: string) {
  // Escapamos del HTML malicioso o corrompido
  let escaped = code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

  // Para evitar colorear una palabra clave ("if") que esté DENTRO de un string, 
  // primero mapeamos los strings enteros y los protegemos con Placeholders:
  escaped = escaped.replace(/"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*'/g, (m) => createPlaceholder(m, 'token-string'));

  // Luego procesamos bucles y keywords (ahora es seguro):
  const keywords = ['def', 'return', 'if', 'for', 'while'];
  const keywordRegex = new RegExp(`\\b(${keywords.join('|')})\\b`, 'g');
  escaped = escaped.replace(keywordRegex, '<span class="token-keyword">$1</span>');

  // Por último, restauramos los placeholders y renderizamos.
  this.highlightedCode.set(this.sanitizer.bypassSecurityTrustHtml(escaped));
}
```

### 3. Sincronización del Scroll y el Cursor
Dado que hay un contenedor para líneas, un `<pre>` invisible falso y un `<textarea>` real, debemos asegurarnos de que hagan `scroll` a la vez y rastrear qué línea de código está editando el usuario en tiempo real:

```typescript
// En modo-historia.component.html:
// (scroll)="syncScroll($event, lineNumbers, scrollContainer)"

syncScroll(event: Event, lineNumbers: HTMLElement, scrollContainer: HTMLElement) {
  const textarea = event.target as HTMLTextAreaElement;
  // Sincroniza el "Y" del contenedor de líneas
  lineNumbers.scrollTop = textarea.scrollTop;
  // Sincroniza el "Y/X" del <pre> que renderiza el color
  scrollContainer.scrollTop = textarea.scrollTop;
  scrollContainer.scrollLeft = textarea.scrollLeft;
}
```
