# Modo Infinito - Roguelike (`modo-infinito.component.ts`)

## Descripción General
El **Modo Infinito** es la principal capa de Gamificación de *Codeo*. Funciona con una arquitectura *Server-Authoritative*, lo que significa que el Front-End **"Simula y Muestra"** la partida, pero es el Back-End quien decide qué es válido. ¡Si actualizas la página no recuperas vidas, el servidor ya las guardó!

## Flujo de Funcionamiento
1. **Petición Inicial**: Se manda una petición `/startSession`. El backend crea/retoma el estado en base de datos.
2. **Ciclo de Nivel**:
   - Traemos un nivel aleatorio.
   - El temporizador empieza una cuenta atrás de forma local por estética (`setInterval`).
   - El código se envía. Si es correcto, el servidor avanza la "Racha" de niveles. Si es falso, devuelve los fallos, se resta la vida *localmente* y el backend *también lo hace*.
3. **Muerte Segura**: Si el temporizador llega a cero o el jugador pierde por fallar un código, las vistas de Game Over son devueltas con estadísticas globales del "Run".
4. **La Tienda**: Si tiene dinero, el servidor le envía *mejoras aleatorias (Cajas)* de forma generada al instante, las aplica contra la BD de la sesión actual y actualizan variables FrontEnd (Ej: tiempo extra).

## Elementos Complejos Explicados

### 1. Doble Temporizador (Optimista vs Autoridad del Servidor)
La web no puede contar el tiempo exactamente a niveles de micro-segundos debido al la gestión del *Event Loop* en JavaScript y a los procesos en segundo plano. Si el temporizador dice `"00:00"`, el FrontEnd reacciona **inmediatamente** restando la vida por estética (*UI Optimista*), pero le pide confirmación al Backend:

```typescript
onTimeExpired() {
  // 1. Reacción Rápida Visual (Optimistic UI)
  this.stopTimer();
  this.timeRemaining.set(0); 
  this.isShowingTimeOut.set(true); // Pantalla roja
  
  // Limpiamos una vida visualmente para dar un golpe de realidad al usuario.
  this.lives.update(l => Math.max(0, l - 1));

  // 2. Validación de la verdad con el Server-Side
  this.roguelikeSessionService.checkTime().subscribe((res) => {
    // Aquí el servidor devuelve el estado REAL. 
    // Quizás el servidor tenía la vida ya 0 o no, él nos setea.
    this.lives.set(res.lives);
    this.timeRemaining.set(res.time_remaining); // Resetea a 60s extra si nos sobraba al menos 1 vida.

    if (res.game_over) {
      this.triggerGameOver(res.stats!);
    } else {
      this.startTimer(); // Retomamos
    }
  });
}
```

### 2. Sincronización de Tienda Roguelike
Comprar mejoras requiere estar sincronizado, el jugador podría haber alterado sus "Monedas" con el *Inspector del Navegador*, pero al estar validado en el servidor, no servirá de nada.

```typescript
selectUpgrade(mejora: any) {
  // Nos devuelve cuántas vidas tenemos AHORA MISMO
  this.roguelikeSessionService.buyMejora(mejora.id).subscribe({
    next: (res) => {
      // Si nos dio tiempo, nos sincroniza el tiempo real que tenemos más el adicional ganado.
      if (mejora.tipo === 'tiempo_extra') {
        this.timeRemaining.set(res.time_remaining);
      }
      this.purchasedUpgrades.set(res.mejoras_activas || []);
      
      // Feedback UI 
      this.mejoraFeedback.set(res.efecto);
    },
    error: (err) => {
      // Si intentó comprar con un hack desde el Request pero el backend no lo deja, da un 400
      this.mejoraFeedback.set(err.error?.message);
    }
  });
}
```
