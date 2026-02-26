# Notificación de Logros Re-Utilizable (`achievement-notification.component.ts`)

## Descripción General
**Achievement Notification** no es un componente de "Vista de página", sino un componente de "Montaje Flotante Global". Actúa en base de los Toastrs o Notificaciones de los sistemas operativos y videojuegos para premiar con espectáculo visual cuando superas una barrera por sorpresa.

## Flujo de Funcionamiento
1. **Desacoplamiento Silencioso**: Es invocado por el Service Singleton global de la aplicación (Generalmente encanchado arriba en el `app.component`) y duerme invisible.
2. **Inyección por Evento**: Tras recibir una llamada del Servidor diciendo "¡Has logrado nivel 10!", su *Signal* interior cambia y detona un `@if` angular renderizando un div con HTML en absolute pos de la esquina superior.
3. **Destrucción de Memoria Automática**: Posee su propio ciclo de "Reloj Relajante". A los 3-5 segundos, retira su opacidad y se auto-destruye del HTML para no interferir en los clicks o en la experiencia del desarrollador, usando un hilo asíncrono.

## Elementos Complejos Explicados

### 1. El Temporizador con Garbage Collection Resguardado
Para evitar problemas en la web si ocurren 20 logros simultáneos (Memory Leaks o destrozos en la consola por invocar timeouts en la nada):

```typescript
export class AchievementNotificationComponent {
  
  notificationData = signal<Notification | null>(null);
  private timer: any; // El guardián del tiempo

  show(achievement: Notification) {
    // Si ya te estaba enseñando algo, cancela el borrado automático que venía:
    if (this.timer) clearTimeout(this.timer);

    // Muestra la nueva medalla
    this.notificationData.set(achievement);

    // Programa un asesinato silencioso del componente a los 5 segundos
    this.timer = setTimeout(() => {
      this.close();
    }, 5000);
  }

  close() {
    // Suelta la referencia
    this.notificationData.set(null);
    clearTimeout(this.timer);
  }
}
```
