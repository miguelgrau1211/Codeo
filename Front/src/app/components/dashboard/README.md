# Dashboard Component (`dashboard.component.ts`)

## Descripción General
El **Dashboard** actúa como el panel principal y centro logístico. A diferencia de componentes puramente visuales, este orquesta llamadas a la pasarela de pagos, estado del usuario, actividad en tiempo real y el panel de progreso en el modo historia.

## Flujo de Funcionamiento
- **Renderizado Adaptativo**: Usando Signals condicionales (`isReady` y `isAdmin`), el Dashboard puede mostrar paneles extra para staff, ocultar los banners de progreso o revelar el icono de la corona premium del usuario.
- **Micro-Animaciones**: Implementa la hoja de estilos global sobre Tailwind usando `@apply` para aplicar transiciones sedosas en cartas, sin sacrificar rendimiento.

## Elementos Complejos Explicados

### 1. Integración con Stripe (Pasarela de pagos en dos pasos)
Comprar el **Pase de Batalla** es un proceso asíncrono vital. Está estructurado de forma híper segura: el HTML de la tarjeta de crédito JAMÁS pasa por Codeo, lo captura un iFrame que proporciona *Stripe.js Elements*.

**Fase 1: Intención de Pago y Mount Seguro de iFrame**
El backend negocia con Stripe un *"Payment Intent"* y nos devuelve una llave pública (Para iniciar el script visual) y un secreto de cliente (Para rastrear la sesión del cobro). 

```typescript
ngAfterViewChecked() {
  // Solo enganchamos la API de stripe al DOM cuando angular haya pintado el Modal.
  if (this.showPaymentModal() && this.paymentStep() === 'form' && !this.stripeElementMounted) {
     this.cardElement.mount('#stripe-card-element');
     this.stripeElementMounted = true;
  }
}
```

**Fase 2: Ejecución Asíncrona Seguro-Concurrente**
Una vez damos a Pagar... Enviaremos los datos de tarjeta *A Stripe*, Stripe nos devolverá mágicamente un objeto `"Succeeded"`, y solo entonces contactaremos con NUESTRO backend para decirle que ascienda a nuestra cuenta.

```typescript
async submitPayment() {
  this.paymentStep.set('processing');

  try {
    // 1. Comunicación Externa con Stripe 
    const { error, paymentIntent } = await this.stripe.confirmCardPayment(this.clientSecret, {
      payment_method: { card: this.cardElement }
    });

    if (paymentIntent.status === 'succeeded') {
      // 2. Comunicación Interna de validación Codeo
      this.http.post('api/battle-pass/confirm', { payment_intent_id: paymentIntent.id }).subscribe((res) => {
          this.paymentStep.set('success');
          // Actualización muda global, todos los Signals del Frontend se refrescan "Mágicamente".
          this.userDataService.getUserData(true).subscribe(); 
      });
    }
  } catch (err) {
     this.paymentError.set('Error en transaccion de nivel 0');
  }
}
```

### 2. Signals Calculados (Progress Math)
El progreso del Pase de Batalla depende de una fórmula que calcula los logros y niveles vs los hitos por superar, pero esto no se recalcula pesadamente por el motor, utiliza el patrón de memoización embebido por Angular `computed()`:

```typescript
battlePassProgress = computed(() => {
  const curLevel = this.level(); // Esto vigila la signal
  const rewards = this.battlePassRewards();
  
  // Extraemos la cima máxima y sacamos estadística capada al 100
  const lastRewardLevel = rewards[rewards.length - 1].level;
  return Math.min(100, (curLevel / lastRewardLevel) * 100);
});
```
