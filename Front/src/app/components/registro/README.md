# Registro de Usuario (`registro.component.ts`)

## Descripción General
El **Registro** introduce nuevos aspirantes dentro de *Codeo*. Recolecta su nickname (Identificador de Jugador), cuenta de correo y contraseña. Utiliza sistemas parecidos al *Login* para establecer un usuario verificado de forma remota, y automáticamente le crea una cuenta gamificada generada procedimentalmente en el backend.

## Flujo de Funcionamiento
1. **Recolección y Reglas Flexibles**: Formulario con chequeo estricto del correo (Regex `email`) para asegurar notificaciones y formato, y reglas para la contraseña.
2. **Generación Pre-Servidor**: Si el usuario no aportó avatar, la orden se envía asumiendo que el Backend o Frontend fabricarán un avatar "Guest" temporal predeterminado.
3. **Redirección Optimizada**: Tras la respuesta de `201 Created`, si también se responde con un Token Autorizado, logramos un *Login Inmediato*, eliminando el paso molesto de validar correo (Por ahora en la Demo MVP) saltando directo a jugar.

## Elementos Complejos Explicados

### 1. Validación Personalizada Reactiva (RxJS Forms)
El componente no solo avisa "Error", sino que rastrea si un usuario escribió a medias y cambió de campo (*Touhed*) o si de verdad violó una validación del framework angular (`Validators.email` vs `Validators.minLength`).

```typescript
// Si abrimos la plantilla (HTML) encontramos el puente lógico de las Signals con RxJS ReactiveForms:

/* 
 * <!-- ¿Está sucio y es inválido el correo? Mostrar rojo y texto -->
 * <small *ngIf="registroForm.get('email')?.invalid && registroForm.get('email')?.touched">
 *    ¡Pon un email válido como dev@codeo.com!
 * </small> 
 */ 

onSubmit() {
  // Si trató de saltarse la validez pulsando Enter de Hacker
  if (this.registroForm.invalid) {
    // Marcamos todo en "Rojo" bruscamente para simular que interactuaron y lo fallaron.
    this.registroForm.markAllAsTouched();
    return;
  }
}
```

### 2. Auto-Avatar por Defecto desde Backend
Si reparamos en las tripas de la llamada enviada, solo va el Nombre, Correo y Clave. Es el Backend el lugar que, vía `CreateUserAction.php`, forja al usuario "Dummie" el avatar en código, garantizando que nadie inyecte URLs maliciosas ni en el front ni en Base de Datos de forma sencilla a la primera oportunidad.
