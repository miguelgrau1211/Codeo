# Tienda de Temas Visuales (`tienda-temas.component.ts`)

## Descripción General
La **Tienda de Temas** es el corazón visual de *Codeo*. Esta interfaz permite a los usuarios utilizar las Monedas de juego desbloqueados por sus esfuerzos intelectuales y canjearlos por "Skins" funcionales o temas del entorno de desarrollo.

## Flujo de Funcionamiento
1. **Extracción y Semillas**: Los temas no viven encerrados en el CSS del Frontend, porque requeriríamos recompilar Angular para meter uno nuevo. Residen en Base de Datos (Vía `TemaSeeder.php`). 
2. **Evaluación de Inventario**: Al entrar, la sección fusiona asíncronamente qué temas nos ofrece el Backend vs. Qué temas ya tenemos desbloqueados en la tabla pivote de nuestro usuario (`user_has_themes`), marcando el condicional en la *Store*.
3. **Inyección en Tiempo Real**: Si un usuario "Equipa" un tema, este no refresca la página para aplicarse, inyecta su ADN directamente a las variables Roots del CSS y altera la página `body` al instante.

## Elementos Complejos Explicados

### 1. Extracción de Variables (CSS Injections)
Cuando el usuario pulsa "Equipar", tomamos el String de JSON con los colores mágicos que nos ha devuelto el Backend y los enchufamos sobre el DOM:

```typescript
equipTheme(themeId: number) {
  // Le informamos al servidor para que recuerde mañana lo que hicimos:
  this.tiendaTemasService.equipTheme(themeId).subscribe();
  
  // Y lo aplicamos AHORA MISMO a nosotros localmente para la UI
  const theme = this.themes().find(t => t.id === themeId);
  if (theme) {
     this.themeService.applyTheme(theme.css_variables); // El servicio inyecta en "document.documentElement"
     this.userDataService.updateEquippedTheme(themeId); // El Store asimila que somos sus dueños absolutos.
  }
}
```

### 2. Estados Visuales en la Tarjeta de Tema
Un tema en pantalla tiene 3 comportamientos radicalmente diferentes e inamovibles (No puedes "comprar" lo que tienes "equipado"):

```html
<!-- Desplegamos según el "Estado" computado -->
<div class="theme-card">
  <!-- PRECIO (Y color de candado si no alcanza) -->
  @if (!theme.is_owned) {
    <button [disabled]="userDataService.coins() < theme.precio" 
            (click)="buyTheme(theme.id)">
        Comprar ({{ theme.precio }})
    </button>
  }
  
  <!-- EQUIPAR Y CONFIRMADO (Ya es nuestro) -->
  @if (theme.is_owned && !theme.is_equipped) {
    <button (click)="equipTheme(theme.id)">Equipar Tema</button>
  }

  <!-- YA LLEVADO -->
  @if (theme.is_equipped) {
    <button disabled>En Uso</button>
  }
</div>
```
