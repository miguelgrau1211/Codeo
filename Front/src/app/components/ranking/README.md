# Ranking del Servidor (`ranking.component.ts`)

## Descripción General
El **Ranking** de *Codeo* implementa el sistema de *Leaderboard Global*. No solo provee datos sin procesar, sino que categoriza visualmente a los tres mejores de la lista e incorpora un motor de renderizado de alto rendimiento para largas tablas de información (DataTables renderizado estáticamente con *ChangeDetection* optimizado).

## Flujo de Funcionamiento
1. **Separación de Top vs General**: La API provee un array estándar de jugadores. El front intercepta y clona dinámicamente el Array inicial: separa a los índices 0, 1 y 2 para armar el componente "Podio" que corona los perfiles especiales, y el resto para el *scroll*.
2. **OnPush Performance**: No es necesario que Angular rastree miles de nodos en el DOM cada vez que se hace un click o *mouse over*, por consecuente, usamos Signals de una sola vía.
3. **Mapeo de Usuario Local**: Dentro de la gigantesca lista, Codeo destaca qué línea es la del jugador actual remarcando con una opacidad y tono diferente para indicar el *"Tú estás aquí"*.

## Elementos Complejos Explicados

### 1. Optimización `ChangeDetectionStrategy.OnPush`
El mayor cuello de botella en listas extensivas es el chequeo de detecciones de cambios por defecto. Hemos activado la pureza mediante `ChangeDetectionStrategy.OnPush`.

```typescript
@Component({
  selector: 'app-ranking',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  templateUrl: './ranking.component.html',
  styleUrl: './ranking.component.css',
  // Angular no re-dibujará la inmensa lista CADA VEZ que ocurra 
  // cualquier evento interno (click en un span), SOLO cuando 
  // las referencias Signals cambien en memoria.
  changeDetection: ChangeDetectionStrategy.OnPush, 
})
export class RankingComponent { 
   ranking = signal<RankingEntry[]>([]); 
   //... 
}
```

### 2. Formato Eficiente de Grandes Números y Rangos Visuales
Los rangos, dependiendo de la fama del servidor, podrían incluir un jugador con "32.000 XP" o "5 XP", rompiendo las tablas. En la plantilla usamos mapeo de rangos con colores de la hoja de estilos global y formateo numérico:

```html
<!-- Separamos oro, plata y bronce programáticamente -->
<ng-container *ngFor="let user of topThree(); let i = index">
  <div class="podium-card" 
       [ngClass]="{
         'gold': user.position === 1,
         'silver': user.position === 2,
         'bronze': user.position === 3
       }">
       
    <!-- Renderizado seguro de los badges -->
    <span class="badge">{{ user.rank_name }}</span>
    <span class="xp">{{ user.total_experience | number }} XP</span>
  </div>
</ng-container>
```

```typescript
// En TS, garantizamos que topThree sea una lista filtrada matemáticamente pura
// y no una función evaluada 5,000 veces por la vista.
topThree = computed(() => this.ranking().slice(0, 3));
restOfRanking = computed(() => this.ranking().slice(3));
```

### 3. Mapeo Rápido de Cuenta Actual en una Lista Asíncrona
El ranking trae a cientos de usuarios y nuestro ID, pero ¿cómo resaltarlo eficientemente sin saturar la memoria O(N^2) dentro del HTML?:

```typescript
// Se obtiene localmente la señal global, que pesa escasos KB's.
currentUserId = computed(() => {
   const user = this.userDataService.userDataSignal();
   // Convertimos el nick en indexador para el componente UI
   return user ? user.nickname : null;
});

// En HTML, en el ciclo principal:
// [class.highlight]="user.nickname === currentUserId()"
// Esto añade o retira un CSS en tiempo real con 0 saltos de memoria extra.
```
