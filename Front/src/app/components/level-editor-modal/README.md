# Modal del Editor de Niveles (`level-editor-modal.component.ts`)

## Descripción General
El **Level Editor Modal** es el sub-componente blindado que da vida al Crud de moderación de los Administradores de *Codeo*. Es capaz de actuar en modo dual: `[Creación Ciega]` o `[Edición sobre Existencia]`, poblando los ReactiveForms según la respuesta de los inyectores que lo invoquen.

## Flujo de Funcionamiento
1. **Inicialización Vía Inputs Parametrizados**: El Padre (`Panel-Admin`) convoca al hijo Modal con un `@if` temporal. Le pasa un ID nulo o un objeto poblado de Datos. 
2. **Construcción de Formulario Dinámico**: El Formulario mapea si es Historia o Infinito, porque los requisitos cambian (Historia requiere pistas, Infinito solo validaciones rápidas).
3. **Edición Arrays Internos (Test Cases)**: Para los "Exámenes", los niveles necesitan múltiples Test Cases dinámicos (Un conjunto de entradas paramétricas y respuestas evaluadas). El código debe controlar una lista viva dentro del formulario.

## Elementos Complejos Explicados

### 1. Crecimiento Dinámico de Test Cases (FormArray)
Los niveles exigen entradas y salidas ilimitadas. En Angular esto se resuelve usando una matriz de formularios (`FormArray`), donde cada elemento posee sus propios sub-inputs reactivos validados:

```typescript
// En TS, al pedir "Añadir un Test Case", empujamos un Micro-Grupo a la colección principal.
addTestCase() {
  const testGroup = this.fb.group({
    input: ['', Validators.required],  // Ejemplo: "[1, 2, 3]"
    output: ['', Validators.required], // Ejemplo: "6"
    is_hidden: [false] // Para la dificultad: Un caso de prueba que el alumno no puede ver.
  });
  
  // Lo anillamos en el Array Maestro
  this.testCasesArray.push(testGroup);
}

removeTestCase(index: number) {
  // Limpiamos memoria del array.
  this.testCasesArray.removeAt(index);
}
```

```html
<!-- En HTML iteramos el formulario sin liarnos: -->
<div formArrayName="test_cases">
  <div *ngFor="let test of testCasesArray.controls; let i=index" [formGroupName]="i">
      <input formControlName="input" placeholder="Dato de entrada" />
      <input formControlName="output" placeholder="Resultado Esperado" />
      <button (click)="removeTestCase(i)">Eliminar caso</button>
  </div>
</div>
```

### 2. Discriminación Dual en Guardado (POST API o PUT API)
Es la misma pantalla para crear y actualizar, reduciendo código duplicado, redefiniendo las llamadas de los `RxJS Streams`:

```typescript
saveLevel() {
  const levelData = this.levelForm.value; // El objeto gigantesco preparado

  // Si tenemos un Nivel_ID pasado desde mi Padre (Ej: 14), actualizamos:
  if (this.currentLevelId) {
     this.adminService.updateLevel(this.currentLevelId, levelData).subscribe();
  } 
  // ¡OJO! Fue nulo, es uno nuevo fabricándose desde 0.
  else {
     this.adminService.createLevel(levelData).subscribe();
  }
}
```
