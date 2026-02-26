# Cabecera Principal (`header.component.ts`)

## Descripción General
El **Header Component** es el "Nav Bar" perpetuo de la aplicación. Gestiona la identidad visual principal (Logo), la navegación primaria para invitados y los menús colapsables para usuarios registrados, ajustándose reactivamente en versiones de escritorio y plataformas móviles.

## Flujo de Funcionamiento
1. **Re-Banderizado de Contexto (Logged In)**: Dependiendo si existe confirmación de sesión, destruye la lista de rutas públicas ("Iniciar Sesión", "Soporte") y renderiza cartuchos dinámicos ("Dashboard", "Admin" si aplica, "Dropdown").
2. **Componente Desacoplado con Routing**: Está montado directamente en el `app.component.html` por fuera de las ventanas `<router-outlet>`. Al estar siempre visible, no se detruye, sino que "escucha" cambios de ruta o de variables en los *Services Singleton* (`AuthService`, `UserDataService`) para mutar sus valores (Monedas actuales, Nombre...).
3. **Manejo Mobile (`MenuToggle`)**: Se contrae a un icono hamburguesa usando clases CSS semánticas de *Tailwind*.

## Elementos Complejos Explicados

### 1. El Menú Desplegable Asignado (`isDropdownOpen()`)
El *Dropdown* del Header incluye gestión de eventos globales de cierre para que, si el usuario cliquea "Fuera" del panel en cualquier lugar del documento, este se esfume sin necesidad de arrastrar un botón de (X).

```html
<!-- Dentro de HTML: Usando la directiva de captura fuera de Elemento -->
<div class="user-menu" (click)="toggleDropdown()">
   <img [src]="userData()?.avatar" />
   
   @if (isDropdownOpen()) {
     <div class="dropdown" (clickOutside)="closeDropdown()">
       <a routerLink="/perfil">Perfil</a>
       <button (click)="logout()">Cerrar Sesión</button>
     </div>
   }
</div>
```

### 2. Sincronización Ininterrumpida "Singleton" de Perfil
Un cambio de nombre en `/configuración` debe reflejarse EN SEGUNDOS en el Header sin refrescar. Porque el Header se alimenta del mismo río de datos:

```typescript
export class HeaderComponent {
   // El header jamás pide llamadas HTTP de sí mismo.
   // Solo mira el agua pasar por el tubo reactivo de user-data:
   userData = computed(() => this.userDataService.userDataSignal());
   
   // Esto evalúa automáticamente si debemos mostrar el Link VIP del Panel 
   // de Administradores sin arriesgar datos de Backend extra.
   isAdmin = computed(() => this.authService.isAdminSignal());
}
```
