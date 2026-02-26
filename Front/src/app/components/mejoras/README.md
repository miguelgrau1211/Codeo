# Tienda de Mejoras (`mejoras.component.ts` y subcomponentes)

## Descripción General
La **Tienda de Mejoras** representa el punto de gasto económico pasivo del "Modo Historia". Provee multiplicadores y personalización o incrementos sistémicos base, permitiendo a los jugadores canjear sus `Coins` ganados arduamente. 

## Flujo de Funcionamiento
1. **Listado Comercial**: A través de HTTP se consigue una lista paginada de artículos disponibles (Potencia XP, Descuentos).
2. **Filtrado por Capacidad**: Cruza los artículos con el inventario del usuario para indicar qué "Puede Comprar" (Suficientes monedas) vs "Inasequible".
3. **Transacción Síncrona**: Al pulsar "Comprar", bloquea el componente local y transmite la intención al backend. El Backend, si lo aprueba, quita el saldo e implementa la mejora en su cuenta devolviendo el estado actual de monedas.

## Elementos Complejos Explicados

### 1. Card de Mejora (Emisión de Eventos / Output Signals)
El componente hijo (`upgrade-card`) es tonto. No tiene la capacidad de conectarse a HttpClient, ni puede restarle monedas directas al usuario porque reventaría el ecosistema. Su único trabajo es verse deslumbrante y "Despertar al Padre" avisando de que lo pulsaron.

```typescript
export class UpgradeCardComponent {
  // Entra data del articulo de Tienda (Padre -> Hijo)
  upgrade = input.required<Upgrade>();
  canAfford = input<boolean>(false);

  // Sale emisión (Mandar Mensaje al Padre desde -> Hijo) "¡Ey! alguien me clickeó en compra"
  buy = output<number>(); 

  onBuyClick() {
    // Si no puedo permitirlo, abortamos y no molestamos al padre.
    if (!this.canAfford()) return;
    
    // Si tengo dinero, emito la ID de mi articulo único y el Padre lo atrapará 
    // y lanzará HTTP.
    this.buy.emit(this.upgrade().id);
  }
}
```

### 2. Recálculo Económico Reactivo (Padre: Tienda Component)
El inventario y el cálculo del saldo en pantalla nunca es una resta simple por JavaScript (`miDinero - 50 = newDinero`), esto generaría inyecciones monetarias si fallase el Server.

```typescript
// Si el padre fue alertado por el evento (buy)
purchase(id: number) {
  this.tiendaService.comprar(id).subscribe({
    next: (respuesta) => {
      // 1. Nos fiamos SOLO de las monedas que nos devuelve el servidor exitosamente
      // y notificamos de mutación el User Data Service general sin recargar página:
      this.userDataService.forceSyncCoins(respuesta.monedasRestantes);
      
      // La variable global `canAfford` del componente, y todas las tarjetas, pierden
      // mágicamente sus colores verdes (si las monedas cayeron bajísimo) gracias
      // al binding de Angular Signals: 
      // (this.userDataService.coins() >= upgrade.price). MAGIA EN CASCADA.
    }
  });
}
```
