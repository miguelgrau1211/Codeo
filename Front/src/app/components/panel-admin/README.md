# Panel de Administración (`panel-admin.component.ts`)

## Descripción General
El **Panel de Administración** es el centro de control exclusivo para perfiles con rango `admin`. Desde aquí pueden supervisar, alterar y gestionar todo el ecosistema de *Codeo*, incluyendo a los usuarios, la base de preguntas y métricas clave de la plataforma.

## Flujo de Funcionamiento
1. **Verificación Estricta (Guardias Dobles)**: El Frontend contiene un `admin.guard` que evitará que el código del componente cargue si el localStorage no dicta "Eres admin". Pero si se salta esto, las peticiones HTTP fallarían ya que el Backend comprueba su token real (`AuthService.esAdmin()`).
2. **Tablas Reactivas Complejas**: Renderiza listas mastodónticas divididas en "Sub-rutas" de administración o pestañas (`Usuarios`, `Base de Datos de Desafíos`).
3. **Control del CRUD (Create, Read, Update, Delete)**: Incluye herramientas incrustadas para eliminar o modificar data de forma directa, mediante modales y alertas nativas asíncronas.

## Elementos Complejos Explicados

### 1. El Switch Board (Renderizado Condicional Múltiple)
Un panel administrativo está atestado de contenido. Cargar todo a la vez hundiría el rendimiento y arruinaría la experiencia. Utilizamos estados (`Signals`) y Control Flow (`@switch`) para inyectar solo el DOM que es relevante para la pestaña pulsada.

```html
<!-- La vista principal destruye e inicializa las tabulaciones según la selección -->
<nav>
  <button (click)="activeTab.set('users')">Gestión de Usuarios</button>
  <button (click)="activeTab.set('challenges')">Moderación de Retos</button>
</nav>

<div class="admin-content">
  @switch (activeTab()) {
    @case ('users') {
      <app-admin-users-table />
    }
    @case ('challenges') {
      <app-admin-levels-editor />
    }
    @default {
      <app-admin-dashboard-metrics />
    }
  }
</div>
```

### 2. Tablas y Búsqueda Angular Pura Local
Manejar búsquedas a miles de usuarios requeriría llamadas al servidor incesantes y costosas (10 usuarios * 2 pulsaciones/seg * 1 min = Base de datos caída). Esto se optimiza en Front mediante una variable precalculada:

```typescript
// Descargamos 500 usuarios una vez al entrar a la sección:
allUsersList = signal<UserAdmin[]>([]);
searchQuery = signal<string>('');

// La tabla que el HTML pinta NO ES allUsersList. 
// Es la derivada:
filteredUsers = computed(() => {
  const list = this.allUsersList();
  const query = this.searchQuery().toLowerCase();

  if (!query) return list;

  // Renderiza en milisegundos sin llamadas Ajax (HTTP)
  return list.filter(u => u.nickname.toLowerCase().includes(query) || 
                          u.email.toLowerCase().includes(query));
});
```

### 3. Modales Complejos Propios (Editor de Niveles)
La administración convoca un componente específico `<app-level-editor-modal>` no enrutado, al estilo "Pop-Up", emitiendo un estado "Abrete con _este Nivel_ o ábrete _Vacio_" permitiendo editar el *Modo Historia* o *Modo Infinito* en crudo, interactuando con las mismas API's que usaron los *Seeders* para crearlo la primera vez.
