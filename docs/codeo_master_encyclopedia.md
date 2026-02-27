# 📔 Catálogo Maestro de Código: Codeo (Deep Analysis)

Este documento proporciona una explicación detallada **archivo por archivo** de la lógica y el propósito de los componentes principales del proyecto Codeo.

---

## 🐘 PARTE 1: BACKEND (Laravel / PHP)

### 1.1. Acciones de Negocio (`app/Actions`)

#### 🏆 Logros (`Achievements/`)
-   **`AssignAchievementManualAction.php`**: Permite a un administrador otorgar un logro específico a un usuario. Útil para eventos especiales o compensaciones.
-   **`CheckAchievementsAction.php`**: El motor reactivo. Tras cada victoria, itera sobre los logros bloqueados y comprueba si el usuario cumple las métricas (XP, monedas, niveles).
-   **`GetAchievementsStatusAction.php`**: Transforma los datos crudos de la BD en un objeto amigable para el frontend, incluyendo el cálculo del porcentaje de completitud.

#### 🛡️ Administración (`Admin/`)
-   **`GetAdminStatsAction.php`**: Recopila métricas globales del sistema (usuarios activos, runs totales, ingresos simulados) para el dashboard de administración.

#### 🎮 Roguelike (`Roguelike/`)
-   **`ApplyRoguelikeUpgradeAction.php`**: Gestiona la compra de buffos (vida extra, más daño, etc.). Descuenta el coste de la sesión en caché y aplica el multiplicador correspondiente.
-   **`CheckRoguelikeTimerAction.php`**: Validador de seguridad. Si el tiempo entre el inicio del nivel y el envío supera el límite configurado (`300s`), marca el nivel como fallido por timeout.
-   **`ProcessRoguelikeFailureAction.php`**: Maneja la pérdida de vidas. Si las vidas llegan a 0, persiste la "Run" en la base de datos como finalizada.
-   **`ProcessRoguelikeSuccessAction.php`**: Registra una victoria parcial. Calcula recompensas y actualiza la racha si es el primer éxito del día.
-   **`StartRoguelikeSessionAction.php`**: Crea el objeto inicial en Redis con 3 vidas y genera la lista de niveles aleatorios que el usuario enfrentará.

#### 🗺️ Niveles Roguelike (`RoguelikeLevels/`)
-   **`GetNextRoguelikeLevelAction.php`**: Selecciona el siguiente reto basado en la dificultad incremental de la run actual.
-   **`ToggleRoguelikeLevelAction.php`**: Permite a los administradores activar o desactivar niveles específicos para mantenimiento sin borrarlos.

#### 📖 Modo Historia (`Story/`)
-   **`DisableStoryLevelAction.php`**: Oculta un nivel de la campaña principal.
-   **`EnableStoryLevelAction.php`**: Publica un nivel nuevo en la secuencia lógica.
-   **`GetStoryProgressSummaryAction.php`**: Genera el mapa de niveles para el usuario, indicando cuáles están bloqueados, completados o disponibles.
-   **`SaveStoryProgressAction.php`**: La acción que se llama al pulsar "Siguiente Nivel". Guarda el código del usuario y actualiza su progreso.

#### 👤 Usuario (`User/`)
-   **`CreateUserAction.php`**: Lógica de registro. Crea el usuario y le otorga el tema "Default" automáticamente.
-   **`DeactivateUserAction.php`**: Soft-delete de cuenta. Mueve al usuario a la tabla de desactivados para posible recuperación.
-   **`GetRankingAction.php`**: Ejecuta una consulta pesada con caché para obtener el Top 100 de usuarios por XP.
-   **`GetUserRecentActivityAction.php`**: Combina datos de múltiples tablas (Historia, Roguelike, Logros) para mostrar la línea de tiempo en el perfil.
-   **`GetUserSummaryAction.php`**: Centraliza datos de perfil, racha, y posición en ranking en una sola petición.
-   **`HandleGoogleLoginAction.php`**: Gestiona el flujo de tokens de Google OAuth.
-   **`LoginUserAction.php`**: Comprueba credenciales y genera el Personal Access Token de Sanctum.
-   **`SearchUsersAction.php`**: Motor de búsqueda para el panel de administración (filtra por email, nombre o ID).

---

### 1.2. Controladores de la API (`app/Http/Controllers`)

-   **`AdminDashboardController.php`**: Expone los datos de salud del sistema solo para administradores.
-   **`EjecutarCodigo.php`**: El núcleo de ejecución. Filtra el código del usuario (evitando comandos peligrosos si no pasaran por Lambda) y gestiona la comunicación con AWS.
-   **`LogrosController.php`**: CRUD de logros y consulta de insignias obtenidas.
-   **`NivelesHistoriaController.php`**: Gestiona la creación de niveles educativos (título, descripción, casos de prueba).
-   **`ProgresoHistoriaController.php`**: El puente entre el frontal y la base de datos para guardar cada pequeño paso del usuario.
-   **`RoguelikeSessionController.php`**: El controlador de estado. Mantiene la run viva mediante peticiones rápidas a la caché.
-   **`UserController.php`**: El controlador más grande. Gestiona perfiles, avatares, cambio de contraseña y preferencias de idioma.

---

## ⚛️ PARTE 2: FRONTEND (Angular / TS)

### 2.1. Componentes Base (`src/app/components`)

-   **`modo-historia.component.ts`**: 
    -   Implementa un editor de código ligero. 
    -   Usa un sistema de **Scroll sincronizado** entre el textarea de entrada y el div de resaltado sintáctico.
-   **`modo-infinito.component.ts`**:
    -   Gestiona el bucle de juego del Roguelike. 
    -   Interactúa con el `AudioService` para dar feedback cuando el cronómetro llega a los 10 segundos finales.
-   **`panel-admin.component.ts`**:
    -   Dashboard centralizado. Usa componentes "Dumb" como `admin-stat-card` para mostrar la información de forma modular.
-   **`tienda-temas.component.ts`**:
    -   Muestra la galería de estilos disponibles. Implementa la lógica de "Compra" y "Equipación" inmediata.

### 2.2. Servicios (`src/app/services`)

-   **`audio.service.ts`**: Gestiona una pool de archivos de sonido para evitar latencia al reproducir alertas o éxitos.
-   **`auth.service.ts`**: Guarda el token JWT en el `sessionStorage` y protege las rutas mediante Guards.
-   **`ejecutar-codigo.service.ts`**: El único punto de contacto con el endpoint de ejecución del backend.
-   **`language.service.ts`**: El cerebro de i18n. Suscribe a la app al cambio de idioma y actualiza todas las etiquetas reactivamente.
-   **`notification.service.ts`**: Un wrapper sobre el sistema de avisos de la UI (Toasts).
-   **`theme.service.ts`**: Aplica las clases de Tailwind al `body` o inyecta variables ROOT para cambiar colores de forma global.

---

## 📉 PARTE 3: MODELOS Y DTOS

-   **`UserSummaryData.php`**: Un DTO que asegura que el frontend solo recibe lo necesario, ocultando campos sensibles de la base de datos.
-   **`level.model.ts`**: Interfaz de TypeScript que define estrictamente cómo debe llegar un nivel (id, titulo, orden, etc.) para evitar errores de `undefined`.

---

> [!TIP]
> **Dominio del código:** Para entender la seguridad, revisa `EjecutarCodigo.php`. Para entender la gamificación, revisa `ProcessLevelUpAction.php`. Para entender la estética, revisa `theme.service.ts`.
