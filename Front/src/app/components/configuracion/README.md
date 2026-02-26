# Ajustes y Configuración (`configuracion.component.ts`)

## Descripción General
El panel de **Configuración** aborda las preferencias y personalización de la experiencia del usuario y engloba operaciones críticas de cuenta. Aquí se decide en qué idioma hablará *Codeo*, la intrusión de los Efectos Especiales Sonoros y la baja permanente del servidor.

## Flujo de Funcionamiento
1. **Lógica Modular e Inyectada**: Las preferencias están descentralizadas para cada tipo de acción, mediante la Inyección de Dependencias, aislando las lógicas sin importar componentes masivos:
   - Sonido -> `AudioService`
   - Traducción -> `LanguageService`
   - Sesión -> `AuthService`
2. **Ciclo Pícaro Multi-Estado (Borrar Cuenta)**: La supresión permanente (`desactivarCuenta`) incluye lógica de *Soft Delete* y prevención de fallos transicionales en el Frontend para no desloguear sin confirmaciones.

## Elementos Complejos Explicados

### 1. Traducciones Multi-Componentes ("Magia del Contexto")
¿Cómo se actualiza toda la página con solo clicar una bandera en configuración, sin refrescar la caché del navegador?

El componente de configuración inyecta la lógica abstracta del `LanguageService` de forma pública y manipula las directivas en memoria utilizando la caché del Signal. Al interactuar con `selectLanguage(...)` el DOM completo mutativiza porque el Signal cambia en caliente:

```typescript
// Dentro de ConfiguracionComponent

selectLanguage(code: string) {
  // El language Service tiene un método setter 
  // Que sobreescribe su Signal Privado.
  this.languageService.setLanguage(code);
  this.closeLanguageSelector(); // Modal cierra en bucle
}
```

```html
<!-- En TODOS lados de Angular usamos nuestro pipe reactivo `| translate` -->
<button (click)="executeDeleteAccount()">
  <!-- El pipe se está subscribiendo de fondo al cambio de idioma de selectLanguage() -->
  {{ 'SETTINGS.DELETE_CONFIRM' | translate }}
</button>
```

### 2. Flujo Complejo de Deslogueo / Desactivación de Seguridad (Manejo Auth)
No llamamos a *window.location.reload()*. Eliminamos las referencias internas, borramos los encabezados y forzamos la ruta base *segura*. 

```typescript
// Desconexión Suave
onLogout() {
  this.isProcessing.set(true);
  
  // Tratamos de decírselo al servidor para que destruya el Payload Sanctum/JWT 
  this.authService.logout().subscribe({
    next: () => {
      this.router.navigate(['/']); // Devuelve a Home
    },
    error: (err) => {
      // Si el servidor explotó o no responde en 5 min igualemente LO MATAMOS 
      // del Token Cache visualmente Forzado 
      sessionStorage.clear();
      this.router.navigate(['/']);
    }
  });
}
```

### 3. Mutismo y Sonido Eficiente (`AudioService`)
El usuario oprime un switch en UI. El componente escucha pero delega la memoria en un inyectable:

```typescript
// Component: 
isMuted = this.audioService.isMuted; // Señal Atada directamente a un servicio

toggleSound() {
  // Almacena en LocalStorage (Navegador persistente) el valor y silencia
  // todo intento emitido por un juego del usuario.
  this.audioService.toggleMute(); 
}
```
