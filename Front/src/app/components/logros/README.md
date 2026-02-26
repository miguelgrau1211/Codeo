# Logros (`logros.component.ts` y subcomponentes)

## Descripción General
El sistema de **Logros** recopila, expone e hidrata las "Medallas de Honor" de los jugadores dentro de Codeo. Actúa en un modo pasivo/exhibicionista, separando los méritos desbloqueados por los usuarios de los desafíos que aún están en curso (Ocultos o Bloqueados).

## Flujo de Funcionamiento
1. **Delegación Estricta**: Este componente "Padre" invoca la Red a través de `UserDataService` y divide inteligentemente las listas usando directivas de estructura como `@for` para pasar la información unitaria al componente "Hijo" (`logro-card`).
2. **Progreso Visual Animado**: Convierte números brutos (Porcentaje de avance: `4.5 / 10 niveles completados`) a barras visuales dinámicas de progreso y escalas de gris.
3. **Sistema Activo / Bloqueados**: Filtra matemáticamente la colección extraída para pintar primero los premios resplandecientes usando condicionales Angular.

## Elementos Complejos Explicados

### 1. Desglose en Micro-Componentes (Padre/Hijo)
Un solo Array con 50 Logros sería desastroso para el CSS en un solo archivo. Se utilizó el patrón "Dumb Component" pasando data usando `input()` (Signal Input 17.1+):

```typescript
// En LogrosComponent (Padre) HTML:
// Iteración hiper-eficiente con 'track' usando el nuevo @for framework de control flow.
<ul>
  @for (logro of logrosDesbloqueados(); track logro.id) {
    <!-- Alimentamos el "Input" del hijo pasándole la caja de datos individual -->
    <app-logro-card [logro]="logro"></app-logro-card>
  }
</ul>
```

### 2. El Hijo Independiente (`LogroCardComponent`)
La propia Tarjeta sabe cómo lucir y qué icono adoptar pero no decide cuándo existir, se encierra en su lógica encapsulada con SCSS individual:

```typescript
export class LogroCardComponent {
  // Define un requisito obligatorio proporcionado por el padre (El componente "Logros" en sí).
  logro = input.required<Logro>();
  
  // Si el logro tiene tipo "Secreto", podría alterar esta misma tarjeta usando un Switch interno.
  isSecret = computed(() => this.logro().tipo === 'oculto');
  
  /* Lógica de formato, porcentaje y colores se abstrae en su clase hija 
   * impidiendo que la clase padre tenga 500 lineas de complejidad basura.
  */ 
}
```
