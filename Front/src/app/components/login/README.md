# Inicio de Sesión (`login.component.ts`)

## Descripción General
El componente **Login** gestiona el acceso de usuarios existentes a la plataforma de *Codeo*. Incluye integración con validación local (correo/contraseña) y un proveedor de terceros (Google). Es el punto de entrada crítico y gestiona directamente las credenciales de autorización del Frontend.

## Flujo de Funcionamiento
1. **Validación Reactiva**: El formulario (`FormGroup`) previene envíos estúpidos (ej: strings vacíos, contraseñas ridículas) antes de molestar al Backend, evitando sobrecargas de red mediante chequeo de validez `Validators.required`.
2. **Ciclo de Autenticación**: Al pulsar "Acceder", se contacta a `/api/login`. 
3. **Distribución del Token**: Si es exitoso, el servidor emite un *Sanctum Token*. Codeo almacena el token en `sessionStorage` (volátil por seguridad) e introduce la llave del usuario en cache para rehusarla en llamadas subsecuentes.
4. **Redirección de Rol**: La sesión comprueba silenciosamente el rango de poder del jugador para derivar o al `Dashboard` o al inicio de administración.

## Elementos Complejos Explicados

### 1. Sistema de Inicio con Google (OAuth 2.0)
Codeo permite acceder omitiendo correos a través del sistema federado de Google Cloud.

```typescript
loginWithGoogle() {
  // 1. Angular le dice al Browser: "Abandona Codeo y llévame a Google"
  // Esto invoca el controlador Auth de Laravel (Socialite) que se encarga del redireccionamiento OAuth.
  window.location.href = 'http://localhost/auth/google';
  
  // 2. ¿Qué pasa después? 
  // Nada en este componente. Cuándo regresen mágicamente por Callback en la URL:
  // http://localhost?token=123x... El sistema central `auth.guard` o `app.component`
  // recuperan de la URL la llave autorizada silenciosamente.
}
```

### 2. Gestión de Respuestas y Seguridad Básica
La validación post-servidor.

```typescript
onSubmit() {
  // La UI se vuelve un loader para bloquear clicks compulsivos de SPAM en el botón de login.
  this.isLoading.set(true); 

  // Petición POST al endpoint login de Laravel usando `authService`
  this.authService.login(this.loginForm.value).subscribe({
    next: (response) => {
      // ÉXITO: Recibimos el Token de personal.
      this.isLoading.set(false);
      // Angular Router lo transporta limpiamente al juego, sin refrescar el HTML.
      this.router.navigate(['/dashboard']); 
    },
    error: (error) => {
      // ERROR: El servidor grita 401 (Prohibido) o 422 (Validación Laravel Fallida)
      this.isLoading.set(false);
      
      // Traducimos un error de sistema interno a un problema legible local (Usando signals).
      this.errorMessage.set(error.error?.message || 'Usuario o contraseña incorrectos');
    }
  });
}
```
