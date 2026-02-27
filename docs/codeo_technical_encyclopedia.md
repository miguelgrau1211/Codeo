# 📗 Enciclopedia Técnica Maestra: Codeo (Deep Dive)

Este documento es una guía exhaustiva y detallada diseñada para proporcionar un dominio total sobre el código fuente del proyecto **Codeo**. No solo explica *qué* hace el proyecto, sino *cómo* lo hace a nivel de líneas de código, lógica algorítmica y patrones arquitectónicos.

---

## 🏛️ 1. Arquitectura de Alto Nivel

Codeo sigue un modelo de **Arquitectura Desacoplada** con un Backend en Laravel que actúa como una API REST robusta y un Frontend en Angular que gestiona una Single Page Application (SPA) altamente interactiva.

### 🔄 El Flujo de una Petición:
1.  **Frontend:** Un componente emite un cambio de estado mediante un **Signal**.
2.  **Service:** El servicio de Angular intercepta este cambio y realiza una petición HTTP.
3.  **Interceptor:** Un interceptor añade el token **Sanctum** al header `Authorization`.
4.  **Backend Routes:** El sistema de rutas identifica la petición y aplica los middlewares (`auth:sanctum`, `admin`, `throttle`).
5.  **Controller:** El controlador (Skinny Controller) recibe la petición, valida los datos básicos y delega la lógica pesada a una **Action**.
6.  **Action:** Contiene la lógica pura de negocio, interactúa con **Models**, **Caching** y **External APIs**.
7.  **Response:** Se devuelve un recurso JSON tipado al Frontend.

---

## 💾 2. Persistencia de Datos (Backend Models & Migrations)

La base de datos es el corazón de la persistencia de Codeo. Está diseñada para ser escalable y soportar operaciones complejas de gamificación.

### 🧬 Modelos Clave:

-   **Usuario (`Usuario.php`):**
    -   **Campos Críticos:** `exp_total`, `monedas`, `es_admin`, `es_premium`, `streak`, `last_activity_date`.
    -   **Relaciones:** `temas()` (Many-to-Many via `usuario_tema`), `logros()` (Many-to-Many), `progresoHistoria()` (One-to-Many).
    -   **Lógica:** Gestiona el sistema de rachas y la progresión de niveles mediante métodos de acceso.

-   **Niveles (`NivelesHistoria.php` y `NivelRoguelike.php`):**
    -   **Estructura:** Ambos contienen `test_cases` (cast de JSON a Array).
    -   **Diferencia:** Los niveles de historia tienen un `orden` secuencial, mientras que los de Roguelike tienen una `dificultad` para selección aleatoria.

-   **Logros (`Logros.php`):**
    -   Utiliza un sistema de **Metadatos Condicionales**. El campo `operador` permite definir si el logro se cumple con `>=` o `==` a ciertos valores internos del sistema.

### 📅 Migraciones Destacadas:
-   `2026_02_24_075405_add_streak_fields_to_usuarios_table.php`: Añade la lógica de rachas (días consecutivos, último día activo, etc.).
-   `2026_02_04_111419_update_levels_tables_add_test_cases.php`: Integra la capacidad de validación mediante inputs/outputs esperados.

---

## ⚙️ 3. Lógica de Negocio (The Action Pattern)

Hemos abandonado los controladores obesos en favor de **Actions**. Cada acción tiene una sola responsabilidad.

### 🛡️ Módulo de Usuarios (`app/Actions/User`)
-   **`LoginUserAction.php`:** Gestiona el login tradicional mediante `Bcrypt` y emite tokens de sesión.
-   **`HandleGoogleLoginAction.php`:** Integra **OAuth2**. Si el usuario no existe, lo crea automáticamente; si existe, actualiza su avatar y token de Google.
-   **`UpdateUserStreakAction.php`:** La lógica más crítica de retención. 
    -   Si la última actividad fue *ayer*: `streak++`.
    -   Si fue *hoy*: `nada`.
    -   Si fue hace *más de 2 días*: `streak = 1`.

### 🎮 Módulo Roguelike (`app/Actions/Roguelike`)
-   **`StartRoguelikeSessionAction.php`:** Inicializa una run en **Redis/Cache**. No persiste en base de datos hasta que el usuario muere o gana para evitar sobrecarga de I/O.
-   **`CheckRoguelikeTimerAction.php`:** Compara el `started_at` de la caché con el tiempo actual. Si el usuario intenta enviar código tras 5 minutos sin permiso, se le resta una vida automáticamente.
-   **`ProcessRoguelikeSuccessAction.php`:** Calcula la recompensa basada en la dificultad (`EASY=10`, `EXTREME=50`). Además, incrementa el contador de niveles completados en la run actual.

### 📖 Módulo de Historia (`app/Actions/Story`)
-   **`SaveStoryProgressAction.php`:** Bloquea la fila del usuario (`lockForUpdate`), añade la XP del nivel y marca el nivel como `completado: true`. También dispara el chequeo de logros.

---

## ⚡ 4. El Motor de Ejecución (AWS Lambda Bridge)

Ubicado en `EjecutarCodigo.php`, es la pieza que permite a los usuarios programar sin comprometer el servidor.

1.  **Aislamiento:** El código se envía a una AWS Lambda que tiene un runtime de Python/PHP restringido.
2.  **Payload:** Se envía: `{ code: "user_code", tests: [ {input, output}, ... ] }`.
3.  **Normalización de Salida:** 
    ```php
    // Fragmento de EjecutarCodigo.php
    if (is_numeric($val) && floatval($val) == intval($val)) {
        $res['output'] = (string) intval($val); // 1.0 -> 1 para evitar fallos de tipos
    }
    ```
4.  **Anti-Cheat Integrado:** El controlador verifica que la petición venga de una sesión de Roguelike válida antes de procesar el éxito del nivel.

---

## 🌐 5. i18n y TranslationService (Carga Inteligente)

Codeo no usa traducciones estáticas simples. Tiene un cerebro de traducción dinámico.

-   **`TranslationService.php`:**
    -   **Caché Agresiva:** Cada texto traducido por Google se guarda por 7 días bajo una clave `trans_language_md5(text)`.
    -   **Traducción en Lote (Bulk):** Para evitar hacer 50 llamadas a Google al cargar el ranking, el servicio agrupa los textos en una sola llamada a la API para optimizar el rendimiento.
    -   **Circuit Breaker:** Si la conexión con Google falla o devuelve un timeout de 5 segundos, la variable `$translationAvailable` pasa a `false` para el resto de la ejecución, devolviendo el texto original rápidamente.

---

## 🎨 6. Frontend Angular: Estado Reactivo con Signals

En Angular 21, hemos eliminado `Observables` para el estado local en favor de **Signals**, reduciendo el consumo de memoria y mejorando la velocidad de renderizado.

### 🧠 El "Smart Component": `ModoHistoriaComponent`
-   **Signals Usados:** 
    -   `codeContent = signal('')`: Almacena el código del usuario.
    -   `highlightedCode = signal<SafeHtml>('')`: Almacena el HTML coloreado que ve el usuario.
-   **Effect de Carga:** 
    ```typescript
    effect(() => {
        const data = this.progresoHistoriaService.progresoSignal();
        // Busca automáticamente el primer nivel no completado y lo carga en el editor
    });
    ```

### 🖍️ El Editor Hacker (Visual Syntax Highlighter)
Como Monaco Editor es pesado para dispositivos móviles, Codeo usa un **Regex-based Highlighter** propio (`updateCode(code: string)`):
1.  **Escapado:** Convierte `<` a `&lt;` para seguridad.
2.  **Inyección de Tokens:** Mediante Regex, identifica `def`, `if`, strings o números y los envuelve en `<span class="token-keyword">`.
3.  **Superposición:** Un `textarea` invisible sobre un `pre` con el código coloreado, creando la ilusión de un IDE avanzado con el coste de un input de texto simple.

### 🛠️ Servicios e Interceptores
-   **`ThemeService`:** Inyecta variables CSS directamente en el `:root` del documento cuando el usuario cambia su tema. 
-   **`LanguageService`:** Gestiona el cambio de idioma y la carga asíncrona de los archivos JSON de `public/i18n`.

---

## 🛡️ 7. Administración y Auditoría

-   **`AdminLogController.php`:** Cada vez que un admin banea a un usuario o borra un nivel de historia, se guarda un registro con: `admin_id`, `accion`, `ip` y `metadatos`.
-   **Gestión de Reportes:** Los moderadores pueden marcar los reportes como `SOLVED` o `SPAM`. Si se marca como `SPAM`, el sistema automáticamente resta XP al usuario que lo envió mediante la acción `ProcessSpamPenaltyAction`.

---

## 🚀 8. Conclusión Técnica

**Codeo** no es solo un CRUD. Es un sistema integral que combina:
-   **Seguridad** (Sandboxing de código).
-   **Escalabilidad** (Actions, Redis, Lambda).
-   **Engagement** (Octalysis gamification).
-   **Accesibilidad** (i18n dinámico).

Cada línea de código está optimizada para ser extensible. Por ejemplo, añadir un nuevo lenguaje al editor solo requiere añadir un array de palabras clave en el frontend y configurar el runtime en la Lambda del backend.

---

> [!IMPORTANT]
> **Dominio del Código:** Si se pregunta por la lógica de subida de nivel, el archivo es `ProcessLevelUpAction.php`. Si se pregunta por el anti-cheat del tiempo, es `CheckRoguelikeTimerAction.php`. Para la traducción de comentarios, es `TranslationService.php`.
