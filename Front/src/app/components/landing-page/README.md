# Página Princial / Landing Page (`landing-page.component.ts`)

## Descripción General
La **Landing Page** no es un componente funcional interno; es la *"Tarjeta de Presentación Digital"*. Es el primer archivo cargado al navegar ciegamente a `https://codeo.com/`. Sirve propósitos informativos en marketing, gamificación y demostración técnica.

## Flujo de Funcionamiento
1. **Intercepción de Logueo**: Comprueba reactivamente, en micro-segundos de su instanciación en el ciclo de vida, si el visitante posee `sessionStorage` validado. De existir, bloquea el renderizado inútil del marketing y transporta secretamente al jugador al `/dashboard`.
2. **Navegación Intramodular**: Ofrece *links de resbalón* para anclar al usuario a diferentes zonas explicativas y convencerlo de registrarse.
3. **Escalado Periférico Visual**: Carga animaciones, gradientes y estructuras HTML amplias.

## Elementos Complejos Explicados

### 1. Intercepción del Flujo Reactiva
Aunque exista un `AuthGuard` de enrutamiento a nivel aplicación, en `OnInit` la Landing trata de matar la lectura aburrida para un usuario legítimo.

```typescript
ngOnInit() {
  // 1. Rescata la sesión. (¡Codeo lo comprueba de forma síncrona visualmente en este caso!)
  const token = sessionStorage.getItem('token');
  
  if (token) {
     // Usuario Legítimo pero escribió mal la URL y fue al Home -> Devuélvelo a la batalla.
     this.router.navigate(['/dashboard']);
  }
  // Si es Null -> No hagas nada, renderiza la magia del Frontend a este "Guest".
}
```

### 2. Presentación Angular "Standalone" Aislada
Al igual que todos los nuevos componentes del sistema de Angular 17.5+, ha sido importada de forma autónoma sin `NgModule`, pero en la Landing se inyectan clases universales de los temas:

```typescript
export class LandingPageComponent {
   // La propia hoja SCSS aplica un tema forzado independiente al ThemeService
   // O podría, si decidimos mutar "La Landing" al tema por defecto del SO del sujeto.
}
```
