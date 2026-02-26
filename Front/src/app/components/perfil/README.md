# Perfil de Usuario (`perfil.component.ts`)

## Descripción General
El **Componente de Perfil** presenta una visualización completa de los logros, nivel y aspecto del jugador dentro de la plataforma. Ha sido diseñado para ser estadístico e interactivo, permitiendo al usuario no solo ver sus hitos (Modo Historia y Roguelike), sino también la edición básica de sus credenciales públicas.

## Flujo de Funcionamiento
1. **Inicialización Eficiente**: En lugar de requerir una nueva llamada HTTP pesada con cada visita, el perfil se alimenta de `UserDataService`, una caché del estado mantenida en el front.
2. **Cálculos Matemáticos Inyectados**: Angular extrae mediante `computed()` el progreso "relativo" de la barra de experiencia para lograr animaciones UI fluidas con CSS sin impactar el servidor.
3. **Editor de Avatar en Tiempo Real**: Si el jugador decide cambiar de aspecto, una librería visual de **DiceBear** creará SVG's autogenerados según semillas algorítmicas, en lugar de manejar la subida de archivos pesados de imagen.
4. **Sincronización Silenciosa**: Al validar, los datos cambian instantáneamente enviando al servidor un solo paquete.

## Elementos Complejos Explicados

### 1. Sistema Dinámico del Cálculo de Experiencia Relativa
Codeo no usa un nivel estático, cada nivel requiere más XP que el anterior (una fórmula RPG escalable). Para renderizar el ancho de la barra verde, en vez de almacenar variables de clase, computamos reactivamente:

```typescript
// Si tiene 1200 xp (Nivel 3 con 200 de extra):
experiencePercentage = computed(() => {
  const userData = this.userData(); // Se refresca si el jugador sube de nivel remotamente.
  
  if (userData) {
    // Calculamos cuánto xp base es el nivel actual (Nivel * Multiplicador) -> Ej: Nvl 3 * 500 = 1000 base.
    const levelExperience = (userData.level - 1) * 500;
    
    // Su experiencia restante (200 de de 500 necesarios para saltar) -> Regla de 3 = 40%
    const percentage = (userData.experience - levelExperience) / 500 * 100;
    return Math.min(percentage, 100); 
  }
  return 0;
});
```

### 2. Generación Aleatoria de Avatares (DiceBear API)
Prevenimos inyección CSRF y almacenamiento al procesar rostros algorítmicos. En vez de alojar bases de datos fotográficas:

```typescript
generateRandomAvatar() {
  // Las semillas alteran sustancialmente los rasgos del SVG resultante
  const seeds = ['Felix', 'Zoe', 'Simba', 'Charlie', 'Milo'];
  const randomSeed = seeds[Math.floor(Math.random() * seeds.length)] + Math.floor(Math.random() * 1000);
  
  // Le pedimos a los servidores de DiceBear un estilo visual (avataaars) en base a un string Random.
  // Por ejemplo: /seed=Felix994 genera SVG idénticos si la seed es idéntica.
  const newUrl = `https://api.dicebear.com/7.x/avataaars/svg?seed=${randomSeed}`;
  this.editAvatarUrl.set(newUrl); // Actualiza la vista previa reactivamente.
}
```

### 3. Modales de Edición y Cierre Transicional
La actualización del estado local espera confirmación del backend antes de cerrar abruptamente:

```typescript
saveProfile() {
  if (this.isSaving()) return; // Evita envíos duplicados 

  // Guardamos e informamos la carga para deshabilitar los inputs...
  this.isSaving.set(true); 
  this.userDataService.updateUser({ ... }).subscribe({
    next: () => {
      // 1. Ocultar carga y dar mensaje de éxito (Toaster de confirmación interno).
      this.isSaving.set(false);
      this.saveFeedback.set('Modificado con Éxito');
      
      // 2. Transición antes de cerrar el div flotante tras 1.5s visuales
      setTimeout(() => this.closeEditModal(), 1500);
    }
  });
} 
```
