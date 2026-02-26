# Soporte y Contacto (`soporte.component.ts`)

## Descripción General
La página de **Soporte** es el canal de comunicación formal entre los Programadores en Entrenamiento y los Administradores de Plataforma de *Codeo*. Funciona como un sistema básico de Tickets.

## Flujo de Funcionamiento
1. **Detección Automática**: Al carecer de contexto del usuario, podría ser un Guest o el usuario nº1. El Componente en su inicialización trata de autocompletar el Formulario (Input de Correo) leyendo la caché si estuviera logueado, sino, dejará que el propio usuario escriba.
2. **Formulario Cauteloso**: La queja o petición usa ReactiveForms extensos que restringen su longitud.
3. **Envío y Mensaje Emocional**: Un spinner bloquea todo el viewport y lanza el Ticket al Backend, regresando una notificación de sistema en Verde indicando un final feliz de la queja.

## Elementos Complejos Explicados

### 1. Reactive Forms con Autocompletado Precedente
Llenamos el formulario dinámicamente según un Signal Externo, no como variable Hardcodeada, adaptándonos al estado:

```typescript
export class SoporteComponent implements OnInit {
  
  // Declaramos nuestra plantilla inactiva y vacía
  contactForm = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    mensaje: ['', [Validators.required, Validators.minLength(20)]]
  });

  ngOnInit() {
    // Escuchamos... ¿Hay alguien logueado?
    const user = this.userDataService.userDataSignal();
    
    if (user && user.email) {
      // Sí lo hay, le quitamos el peso de rellenarlo e inyectamos sobre el form
      this.contactForm.patchValue({ email: user.email });
    }
  }

  submitTicket() {
    if (this.contactForm.invalid) return;
    
    // Y luego lo disparamos.
    this.http.post('/api/support', this.contactForm.value);
  }
}
```
