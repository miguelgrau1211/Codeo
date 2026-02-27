# Memoria del Proyecto: Codeo - Plataforma Gamificada para el Aprendizaje de Python

**Título del Proyecto:** Codeo - Plataforma Gamificada para el Aprendizaje de Python
**Autores:** Miguel Grau Fernández y Haziel Évora Ausina
**Tutor:** Maria Ana Capilla Herrero
**Fecha:** 26 de febrero de 2026
**Centro Educativo:** IES Camp de Morvedre
**Titulación:** Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (DAW)

---

## Tabla de Contenido

1. [Introducción](#1-introducci%C3%B3n)
   1.1. Contexto y Justificación
   1.2. Estado del Arte
   1.3. Motivación del Proyecto
2. [Objetivos](#2-objetivos)
   2.1. Objetivo General
   2.2. Objetivos Específicos Técnicos
   2.3. Objetivos Específicos Funcionales y Pedagógicos
   2.4. Alcance del Proyecto
3. [Metodología de Trabajo: Scrum y Principios Ágiles](#3-metodolog%C3%ADa-de-trabajo-scrum-y-principios-%C3%A1giles)
   3.1. Justificación de la Metodología
   3.2. Roles del Equipo
   3.3. Artefactos de Scrum y Gestión de Requisitos
   3.4. Ceremonias y Flujo de Trabajo
4. [Análisis y Planificación](#4-an%C3%A1lisis-y-planificaci%C3%B3n)
   4.1. Estudio de Viabilidad
   4.2. Product Backlog y Priorización (MoSCoW)
   4.3. Sprint Planning y Ejecución
   4.4. Roadmap y Tiempos
5. [Desarrollo del Proyecto: Ingeniería de Software](#5-desarrollo-del-proyecto-ingenier%C3%ADa-de-software)
   5.1. Arquitectura General del Sistema
   5.2. Diseño de Base de Datos y Modelado de Datos
   5.3. Desarrollo Frontend: Angular y Arquitectura Reactiva
   5.4. Diseño UI/UX y Sistemas de Diseño
   5.5. Desarrollo Backend: Laravel, DDD y APIs RESTful
   5.6. Motor de Ejecución en Microservicios (AWS Lambda)
   5.7. Game Design, Gamificación y Economía (Octalysis)
6. [Pruebas y Aseguramiento de la Calidad (QA)](#6-pruebas-y-aseguramiento-de-la-calidad-qa)
   6.1. Pruebas Unitarias y de Integración en Backend
   6.2. Pruebas Automáticas en Frontend
   6.3. Auditorías de Rendimiento y Accesibilidad
7. [Resultados Obtenidos e Implementación](#7-resultados-obtenidos-e-implementaci%C3%B3n)
8. [Retrospectiva y Lecciones Aprendidas](#8-retrospectiva-y-lecciones-aprendidas)
9. [Conclusiones y Líneas de Trabajo Futuro](#9-conclusiones-y-l%C3%ADneas-de-trabajo-futuro)
10. [Bibliografía y Referencias](#10-bibliograf%C3%ADa)
11. [Anexos](#11-anexos)

---

<div style="page-break-after: always;"></div>

## 1. Introducción

### 1.1 Contexto y Justificación
En la era digital actual, la demanda de perfiles técnicos y programadores no deja de crecer exponencialmente. Sin embargo, el aprendizaje inicial de la lógica de programación y lenguajes como Python puede resultar una barrera de entrada frustrante e intimidante para muchas personas. La educación tradicional, basada en la visualización pasiva de tutoriales o en la lectura de densa documentación técnica, a menudo carece del dinamismo necesario para retener la atención del estudiante y mantener altos los niveles de motivación. La tasa de abandono en cursos introductorios de informática suele ser elevada debido a problemas como la "fatiga de pantalla" y la falta de "feedback" inmediato.

Ante esta situación, **Codeo** nace como una plataforma educativa innovadora que fusiona el mundo del desarrollo de software con mecánicas de videojuegos o *gamificación*. A través de métricas de recompensa inmediatas y trayectorias estructuradas de dificultad incremental, Codeo busca transformar el esfuerzo cognitivo que supone aprender a programar en una experiencia lúdica, gratificante y desafiante. 

### 1.2 Estado del Arte
El mercado actual cuenta con diversas soluciones como *Codecademy*, *LeetCode* o *Codewars*. Si bien son plataformas robustas, sus modelos varían significativamente:
- Plataformas orientadas fuertemente a profesionales (LeetCode), con poca o nula gamificación visual y una barrera de entrada elevada.
- Sistemas de aprendizaje estilo "wizard" (Codecademy), muy enfocados a la teoría con escasa libertad creativa.
- Minijuegos específicos (Screeps, CodeCombat), donde la programación se utiliza puramente como motor de un juego, alejándose de los patrones estándar que se encontrarían en entornos reales.

Codeo busca un equilibrio intermedio (Sweet Spot): ofrecer un entorno propio de desarrollo integrado, creado íntegramente de forma personalizada optimizando áreas de `<textarea>` enlazadas a analizadores sintácticos regex para inyectar CSS, lo cual se envuelve en una capa psicológica de retención a través de mecánicas RPG (Role-Playing Game). Así, el estudiante siente progreso tanto en sus habilidades técnicas reales como en los atributos virtuales de su perfil.

### 1.3 Motivación del Proyecto
La principal motivación técnica para abordar Codeo es la oportunidad de diseñar e implementar una arquitectura web de alta complejidad, abarcando desde sistemas reactivos en el lado del cliente (Frontend) hasta el encapsulamiento seguro y la lógica transaccional de alta concurrencia en el lado del servidor (Backend). 

A nivel personal, este proyecto de final de ciclo nos permite poner en práctica y consolidar absolutamente todas las competencias adquiridas en el ciclo de Grado Superior de Desarrollo de Aplicaciones Web, abordando desafíos de arquitectura avanzada, diseño de APIs, sistemas de ejecución segura de código y estándares modernos de Single Page Applications (SPA).

---

<div style="page-break-after: always;"></div>

## 2. Objetivos

### 2.1 Objetivo General
El objetivo central del proyecto es diseñar, desarrollar, probar y desplegar **Codeo**, una aplicación web integral y gamificada para el aprendizaje guiado y autónomo de la programación en Python, que incorpore validación de código en tiempo real, mecánicas de progresión económica y de experiencia, así como un entorno gráfico moderno, fluido y estrictamente accesible.

### 2.2 Objetivos Específicos Técnicos
Para lograr una plataforma de calidad empresarial, se establecen los siguientes objetivos a nivel de infraestructura y desarrollo:
1. **Desarrollo Frontend Reactivo y Moderno:** 
   - Implementar la interfaz gráfica utilizando **Angular 21**, empleando exclusivamente *Standalone Components* (sin dependencias de `NgModules`).
   - Lograr una latencia de interacción visual inferior a 100ms y mitigar el *Layout Shift* utilizando transiciones y estados de "Skeleton Loader" eficaces.
   - Orquestar el estado global y local completamente con el ecosistema de **Angular Signals** (`signal`, `computed`, `effect`), erradicando dependencias legacy como Zone.js.
2. **Despliegue de Backend Robusto y Seguro:** 
   - Desarrollar la API RESTful en **PHP 8.3 y Laravel 12**, aplicando una estricta arquitectura orientada al dominio (Domain-Driven Design).
   - Abstraer toda lógica de negocio en clases de tipo *Action* y asegurar que las transferencias de datos se hagan exclusivamente con DTOs (Data Transfer Objects).
   - Asegurar la resiliencia en base de datos previniendo problemas de N+1 queries y condiciones de carrera (Race Conditions) mediante transacciones con `lockForUpdate()`.
3. **Motor de Ejecución Serverless Segura:** 
   - Proveer un entorno seguro para evaluar código arbitrario mediante **microservicios en la Nube**, abstrayendo el motor de prueba a funciones *AWS Lambda* efímeras, con el fin de eliminar la vulnerabilidad y la carga sobre la API de Laravel.
4. **Sistema de Diseño (Design System):** 
   - Construir una biblioteca de componentes visuales apoyada en **Tailwind CSS**. 
   - Emplear "Design Tokens" a nivel de configuración para intercambiar dinámicamente el estilo visual y temas (Claro/Oscuro).

### 2.3 Objetivos Específicos Funcionales y Pedagógicos
1. **Modo Historia Guiado:** Proveer un flujo de niveles secuenciales en los que se introduzcan conceptos de Python basándose en explicaciones teóricas y una posterior validación en consola.
2. **Modo Roguelike (Desafíos Procedurales):** Implementar un modelo de "muerte permanente", eventos aleatorios generados en base a dificultad algorítmica y potenciadores ("power-ups").
3. **Validación de Casos de Prueba Ocultos:** Analizar el código de los estudiantes no solo por lo impreso, sino mapeando sus respuestas con un array severo de diccionarios de entrada/salida directamente procesados en el entorno aislado (AWS).
4. **Economía y Motivación:** Diseñar una curva matemática para los puntos de experiencia, monedas que actúen como "sumidero económico" y cálculo de rachas diarias para fomentar el enganche sin penalizar excesivamente al usuario inactivo (con sistemas de bloqueo de racha).

### 2.4 Alcance del Proyecto
El alcance del MVP (Minimum Viable Product) incluirá los módulos de autenticación, la estructura modular de las lecciones, el intérprete interactivo asistido por AWS Lambda, perfiles de usuario unificados y el ranking global. Las funcionalidades de carácter multijugador síncrono o foros comunitarios quedan categorizadas como un futurible, para evitar el llamado *Scope Creep* (expansión incontrolada del alcance).

---

<div style="page-break-after: always;"></div>

## 3. Metodología de Trabajo: Scrum y Principios Ágiles

### 3.1 Justificación de la Metodología
Para gestionar la complejidad inherente a un sistema que unifica múltiples servicios (Angular, API de Laravel, Ejecutor Serverless, Bases de Datos) decidimos optar por el marco de trabajo **Scrum** bajo una filosofía ágil. Esta elección obedece a la necesidad de entregar valor temprano, inspeccionar de manera constante si el enfoque lúdico conecta satisfactoriamente y adaptarnos rápidamente si ciertas mecánicas resultan demasiado complejas o costosas de implementar para el MVP.

### 3.2 Roles del Equipo
Los roles dentro de nuestro equipo se dividieron abarcando múltiples disciplinas, organizándonos como equipos auto-gestionables (Full-Stack Squads):
- **Product Owner & Game Designer:** Encargado de medir el alcance comercial, el equilibrio en la economía del juego (precios, recompensa de XP) y establecer el qué y por qué de las funcionalidades (las historias de usuario).
- **Scrum Master & Technical Project Manager:** Facilitador que remueve impedimentos técnicos, organiza los repositorios en GitHub, mantiene y vigila la integración continua y asegura la adherencia a la jerarquía "MoSCoW".
- **Development Team (Frontend Architects & Backend Masters):** Equipo enfocado en la entrega de componentes e infraestructura técnica exigente que respete los contratos (API Contracts) pre-acordados, previniendo la degradación técnica y de seguridad de la plataforma.

### 3.3 Artefactos de Scrum y Gestión de Requisitos
La documentación y recolección de requisitos se vertebraron a través de una serie de artefactos de desarrollo:
- **Product Backlog:** El repositorio centralizado y evolutivo de todo aquello que podría ser parte del producto. 
- **User Stories (Historias de Usuario):** Todo el desarrollo se estructuró bajo la semántica: *"Como [Perfil de Usuario], deseo [Funcionalidad o Mecánica] para [Propósito o Valor de Negocio]"*.
- **Definition of Done (DoD):** Para cada tarea se definió un conjunto estricto de criterios de aceptación. Para poder mover una tarea a "Hecho", el código debía estar subido en una rama separada (Pull Request), libre de "magic strings" y la cobertura de pruebas de backend (PHPUnit) para esa sección debía ser superior al 80%.

### 3.4 Ceremonias y Flujo de Trabajo
- **Sprint Planning (Planificación del Sprint):** Al iniciar un ciclo de 2 a 3 semanas, analizábamos la métrica de velocidad de desarrollo para comprometernos con un avance medible y realista. 
- **Daily Standup (Reuniones Diarias):** Breves puestas al día enfocadas en solventar bloqueos críticos, tales como problemas de inyección de dependencias cíclicas en Angular o fallos de CORS con la API.
- **Sprint Review (Revisión):** Demostración interactiva de la nueva funcionalidad desplegada en entornos de Staging. 
- **Sprint Retrospective:** Reuniones muy importantes donde valorábamos nuestra cadencia, las decisiones técnicas que nos frenaron (por ejemplo, el mal uso inicial de RxJS frente a las Signals) y el cómo mejorar nuestro *Developer Experience* para las siguientes repeticiones.

---

<div style="page-break-after: always;"></div>

## 4. Análisis y Planificación

### 4.1 Estudio de Viabilidad
Previo al desarrollo, analizamos diferentes ámbitos de viabilidad:
#### 4.1.1 Viabilidad Técnica
Se evaluaron tecnologías como React/Vue frente a Angular, resultando Angular la opción predilecta debido a las recientes actualizaciones estructurales con *Control Flow `@if`/`@for`*, y la reactividad síncrona tipo *Signals*, lo cual permitía modelar mecánicas de videojuegos con alto rendimiento. del lado del servidor se evaluó el riesgo de evaluar strings de Python. La mitigación y el éxito de esta viabilidad dependió de poder desplegar la delegación segura y efímera hacia arquitecturas Serveless como AWS Lambda, controlando severamente los tiempos de respuesta (Timeouts) y costes por llamada.

#### 4.1.2 Viabilidad Económica
Empleando tecnologías de código abierto (Angular, Laravel, MySQL) el coste en infraestructura es marginal durante el desarrollo. Mediante GitHub Actions y plataformas como Vercel y Railway o Laravel Forge, los costes operativos de la primera versión no superan los tiers gratuitos, garantizando una alta viabilidad económica.

#### 4.1.3 Viabilidad Legal
Se prestó especial atención a la normativa actual en lo referido al tratamiento de datos, requiriéndose el consentimiento explícito de cara al RGPD para almacenar correos de usuario y registrar fallos/errores en la analítica de uso del componente del juego. Para la analítica se descartaron cookies intrusivas.

### 4.2 Product Backlog y Priorización (MoSCoW)
La técnica de categorización elegida para evitar el Scope Creep fue la matriz de MoSCoW.
- **Must Have (Crítico para lanzamiento):** API de Registro/Login mediante JWT/Sanctum y Single Sign On (Socialite Google); Sistema de base de datos relacional de Usuarios e Historial Evolutivo; Integración de Editor de Código personalizado; Analítica y Corrección en la nube de un set de ejercicios.
- **Should Have (Importante pero secundario):** Mecánicas de Roguelike avanzado; Ranking y Paginación; Animaciones ricas y componentes modales dinámicos.
- **Could Have (Deseable):** Avatares personalizados, panel de administración interno complejo para gestionar niveles de forma dinámica.
- **Won't Have (Descartado para MVP):** Batallas multijugador síncronas en tiempo real, compras in-app con dinero real (Stripe payment gateways fueron pospuestos).

### 4.3 Sprint Planning y Ejecución
- **Sprint 1 (Cimientos Infraestructurales):** Inicialización de los repositorios. Configuración general de bases de datos, Migraciones iniciales en Laravel, Modelos Eloquent y rutas API de Autenticación. Estructura de componentes `Standalone` en Angular y variables de enrutamiento JWT.
- **Sprint 2 (Game Screen & UI Components):** Se diseña la pantalla base del juego mediante tokens de Tailwind. Integración inicial de pantallas complejas con el editor de código. Implementación de Guards de router para protección de carga.
- **Sprint 3 (Ejecutor Serverless & Integraciones API):** Foco masivo en el "Educador Técnico". Se construyeron los interceptores API, el empaquetado de las peticiones hacia AWS Lambda, y la validación final que cruza los resultados matemáticos de los test evaluados sin exponerlos al cliente.
- **Sprint 4 (Gamificación y Economía del Perfil):** Sistemas de DTOs en el backend para calcular la trayectoria de XP de los usuarios conforme pasaban los niveles. Se implementó un action service para transaccionar recompensas usando `DB::transaction()` evitando que llamadas duplicadas corrompieran la XP otorgada.
- **Sprint 5 (Modo Roguelike):** Implementación de la dificultad procedimental generada en el lado del servidor y gestión estado en el frontend de las "vidas" y "power-ups".
- **Sprint 6 (Ranking, Pulido Auditivo y Accesibilidad):** Auditorías completas con Lighthouse, implementaciones estrictas de diseño de accesibilidad (a11y - contrastes, navegación de tablaturas) y refactoring masivo del código a "OnPush".

---

<div style="page-break-after: always;"></div>

## 5. Desarrollo del Proyecto: Ingeniería de Software

### 5.1 Arquitectura General del Sistema
La arquitectura del proyecto Codeo se fundamenta en un modelo Cliente-Servidor fuertemente desacoplado. El cliente (SPAs) se compila y aloja predominantemente en plataformas de entrega rápida, comunicándose por petitorios XMLHttpRequest o `fetch` a una API blindada RESTful gestionada por Laravel.

El flujo es unidireccional para las actualizaciones de estado interactivas. El cliente emite las *intenciones* (Intentions: ej. el estudiante envía su resolución del problema al endpoint de evaluación), y la API de Laravel asume el rol de Árbitro Central (Authoritative Server). El servidor compila los datos, valida las restricciones, calcula *consecuencias* (daño recibido por fallos, experiencia obtenida, etc.) y reenvía al frontend la respuesta puramente como estructura de datos (JSON) para su presentación visual. 

### 5.2 Diseño de Base de Datos y Modelado de Datos
La persistencia de los datos se delegó en un motor MySQL hiper-optimizado utilizando diseño relacional, soportado en su totalidad por las migraciones de Laravel Eloquent. La estructura de la base de datos se ha diseñado en español para mantener la coherencia del dominio del proyecto:
- `usuarios`: Autenticación segura y almacenamiento unificado del perfil del jugador (Experiencia, Monedas, Nivel, Vidas del Roguelike, Rachas). No fragmentamos esto en "wallets" extras para optimizar las consultas a una sola tabla principal.
- `niveles_historia` y `niveles_roguelike`: Catálogos separados del contenido educativo. Definen títulos, el código base, y los `test_cases` vitales para verificar en el servidor la validez del código del alumno.
- `runs_roguelike` y `usuario_progreso_historia`: Guardan constancia inmutable de los retos superados para prevenir manipulaciones e inyecciones en la subida de nivel.
- `logros` y `mejoras`: Elementos interactivos de la economía y tienda del juego.

### 5.3 Desarrollo Frontend: Angular y Arquitectura Reactiva
El pilar maestro del área Visual está regido por exigencias de latencia cero y una Arquitectura Funcional en Angular:
- **Ausencia de NgModules y Zone.js:** El proyecto es enteramente basado en **Standalone Components**. La zona de detección tradicional fue omitida casi por completo o manejada mediante la nueva detección de estado (ChangeDetectionStrategy.OnPush) aplicada globalmente a sus componentes, re-renderizando partes del DOM única y exclusivamente cuando sus estados locales varían. 
- **Ecosistema Signals:** La revolución interna de las pantallas recae en el sistema Signals (`signal`, `computed`, `effect`). Cuando la API contesta que el jugador ganó "25XP", una Signal principal captura este valor. Con un `computed()` el UI de barra de experiencia se recalcula inmediatamente y desencadena efectos css automáticos con inmenso desempeño a nivel de cuadros por segundo del navegador.
- **Enfoque Híbrido de Comunicación:** Si bien el estado global asíncrono se ha migrado drásticamente al ecosistema reactivo de Angular Signals (en servicios como `UserDataService`), para la comunicación estricta y controlada entre componentes Padre e Hijo (presentacionales) se ha decidido mantener orgánicamente los decoradores tradicionales e infalibles `@Input()` y `@Output()`, logrando un esquema robusto y altamente escalable sin sobrediseñar en exceso.

**Evidencia de Implementación (Frontend):**
A continuación, un extracto real del componente `ModoInfinitoComponent` de nuestro proyecto, evidenciando el uso de `Standalone Components`, `OnPush` y `Signals`:

```typescript
@Component({
  selector: 'app-modo-infinito',
  standalone: true, // Arquitectura sin NgModules
  imports: [CommonModule, RouterLink, TranslatePipe],
  templateUrl: './modo-infinito.component.html',
  styleUrl: './modo-infinito.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush, // Rendimiento máximo
  encapsulation: ViewEncapsulation.None,
})
export class ModoInfinitoComponent implements OnInit, OnDestroy {
  // Estado puramente reactivo con Signals
  lives = signal(3);
  coins = signal(0);
  timeRemaining = signal(300);
  
  // Computed signals basados en servicios inyectados
  isAdmin = computed(() => this.authService.isAdminSignal());
}
```

### 5.4 Diseño UI/UX y Sistemas de Diseño
El equipo de UI/UX priorizó una política de *Content-First*, empaquetando todo bajo guías de diseño de muy alta estética e impacto:
- **Tailwind y CSS Nativo (Variables):** El aspecto visual se controla dinámicamente mapeando la configuración de `tailwind.config.js` (`primary-bg`, `accent-color`) hacia variables nativas de CSS (`var(--primary-bg)`). Este enfoque puntero de "Design Tokens" permite que el cambio estructural entre diversos temas visuales sea instantáneo, sin forzar reescrituras de clases en los templates HTML. Adicionalmente de añadieron micro-animaciones personalizadas en Tailwind (como *blob*, *zoom-pulse*).
- **Retroalimentación y Micro-Interacciones:** Todo componente posee estados Skeleton Loader para indicar asincronismos. Los botones poseen sombreados de estado `translate-y` para sentir profundidad mecánica simulando interfaces inmersivas.
- **Accesibilidad (a11y):** Aseguramiento y cumplimiento con WCAG AA. Contraste de texto validado algorítmicamente y lectura apta para asistentes de voz introduciendo etiquetas ARIA.

### 5.5 Desarrollo Backend: Laravel, DDD y APIs RESTful
El cerebro de salvaguardia ("El Árbitro") delega en Laravel con PHP 8.3:
- **Patrón Action / Single Responsibility:** El código fue alejado masivamente del Anti-Patrón de los Controladores Pesados. Los Archivos `Controller` únicamente procesan solicitudes, mandan los inputs de los clientes mediante *Laravel Requests Validation*, extraen los datos mediante DTOs (Data Transfer Objects nativos de PHP 8.3 con `readonly classes` y Promoción de Propiedades en Constructor), y se lo ceden a Clases específicas "Actions" (`ProcessLevelUpAction`, `CalculateLevelExperienceAction`).
- **Seguridad Roguelike en BBDD:** Empleando Bloqueos a nivel InnoDb (`DB::transaction` sumado al uso de `->lockForUpdate()`), evitamos de lleno cualquier intento por parte del usuario de realizar requests paralelos y múltiples sobre el consumo de pociones, monedas o subidas de rango.
- **API Resources:** La homogeneidad está garantizada. Ningún controlador escupe modelos u arrays aleatorios. Todos pasan por la lente formativa del sistema de API JSON Resources para filtrar y evitar exponer las interrelaciones innecesarias del servidor al cliente web.
- **Tipado estricto PHP 8.3:** Enumerados (Enums) para la definición de dificultad, tipos de lenguaje y roles de usuario, impidiendo ambigüedades derivadas de las tradicionales *Magic Strings*.

**Evidencia de Implementación (Backend):**
A continuación, un extracto real de nuestra clase `ProcessPurchaseAction`, demostrando el uso de transacciones atómicas y bloqueos por fila (`lockForUpdate`) para evitar desincronizaciones en pagos concurrentes:

```php
// ✅ Pago confirmado -> Activar Premium de forma segura
return DB::transaction(function () use ($usuario, $paymentIntent) {
    // Bloqueo de fila para evitar condiciones de carrera en alta concurrencia
    $usuario = Usuario::lockForUpdate()->find($usuario->id);
    
    $usuario->es_premium = true;
    $usuario->premium_since = now();
    $usuario->save();

    Log::channel('stderr')->info('🔵 [BATTLEPASS] Usuario actualizado a Premium', ['id' => $usuario->id]);

    // Otorgar recompensas mediante otra Action de responsabilidad única
    $battlePassRewards = (new GrantBattlePassRewardsAction())->execute($usuario);

    return [
        'success' => true,
        'message' => '¡Pase de Batalla activado con éxito!',
        'rewards_granted' => $battlePassRewards,
    ];
});
```

Adicionalmente, el uso de DTOs en PHP 8.3 con clases de solo lectura (`readonly class`):

```php
namespace App\DTOs\User;

readonly class UserSummaryData
{
    public function __construct(
        public string $nickname,
        public string $email,
        public int $level,
        public int $experience,
        public int $coins
    ) {}
}
```

### 5.6 Motor de Ejecución en Microservicios (AWS Lambda)
Como eje educativo pionero en nuestro ecosistema, integramos un modelo de evaluación en la Nube robusto y descentralizado:
- **AWS Lambda (Serverless Execution):** En lugar de recargar al servidor principal de Laravel procesando *strings* o levantando contenedores Docker pesados, la ejecución del código Python introducido por el estudiante es delegada a un servicio Serverless (AWS Lambda). 
- **Flujo de Evaluación:** 
  1. El usuario teclea su código.
  2. Angular lo recoge vía API y Laravel (`EjecutarCodigo.php`) empaqueta un *Payload* con dicho código junto a los casos de test ocultos (`test_cases` de la BD).
  3. AWS Lambda recibe la petición, levanta un Sandbox efímero que dura fracciones de segundo, simula las salidas y coteja.
  4. La respuesta es regresada al backend para procesar las recompensas atómicas de XP y Monedas.  
Este enfoque Serverless ofrece escalabilidad infinita y nulo peligro de RCE (Remote Code Execution) para el centro de datos principal de API web.

### 5.7 Diseño de Gamificación y Economía (Framework Octalysis)
La psicología retenedora de usuarios bebe del conocimiento experto en *Gamification*:
- **Puntos (XP) y Escalado Lineal:** Siguiendo las recomendaciones de retención sostenida, no acudimos a una escala exponencial frustrante en el paso de niveles, en su lugar en nuestro `ProcessLevelUpAction` el progreso es claro y predecible: `Nuevo Nivel = floor(Experiencia_Total / 500) + 1`. Otorgando al jugador de recompensas de Pase de Batalla a cada iteración de nivel conseguida.
- **Core Loop Satisfactorio:** Aprender Teoría -> Desafío Código -> Validación Visual Exitosa -> Pantalla Recompensa Sonora -> Compra Mejoras Estadísticas -> Nuevo Desafío.
- **Dinámicas de Rachas de Login:** Modelos compensatorios de "Freezes" al igual que en la famosa aplicación Duolingo. Premia la constancia diaria para consolidar hábitos neuronales del código en el alumno, penalizando el abandono pasivo del mismo.

---

<div style="page-break-after: always;"></div>

## 6. Pruebas y Aseguramiento de la Calidad (QA)

Implementar un ecosistema con dinero ficticio virtual, puntajes competitivos y lógica delicada demandó un conjunto de pruebas implacables.

### 6.1 Pruebas Unitarias y de Integración en Backend
El código backend de negocio tiene exigencias absolutas de cobertura. Para ello, optamos por **PHPUnit**, aprovechando su fiabilidad nativa absoluta y la estructuración en casos de test paralelizables. Mediante PHPUnit testificamos las rutas y la coherencia de los Action Services. Verificamos explícitamente las transacciones financieras en base de datos.
- Pruebas exhaustivas al motor de *Validaciones FormRequest*.
- Validación unitaria de las clases de mapeo de DTOs.
- Verificación del comportamiento erróneo (Testing the Unhappy Paths): Confirmar que el usuario sí reciba rechazo HTTP-401 o 403 al intentar enviar resoluciones a escenarios a los que todavía no posee un rango adecuado.

### 6.2 Pruebas Automáticas en Frontend
Utilizando el veloz motor **Vitest** en el Frontend, acoplado intrínsecamente con las compilaciones modernas y verificadores estáticos en Typescript. Al estar Angular organizado en componentes pequeños "Dumb", la inyección de tests se limitó a comprobar atributos de Signal y validar si los botones de control de navegación se auto-desactivaban si el estatus estaba en *Loading*. Además, la rigidez estricta del `tsconfig.json` (con `noImplicitAny` y `strictNullChecks` forzados a bloqueos absolutos de compilación) sirvió como una fortísima barrera de QA inicial.

### 6.3 Auditorías de Rendimiento y Accesibilidad
Validadas en su estado de Compilación `Production Build`, garantizando la miniaturización total (Uglify / Tree Shaking) y evaluando contra la herramienta Google Lighthouse del lado del navegador y Blackfire localmente en Laravel, eliminando librerías innecesarias globales y dejando un bundle ridículamente medular y rápido. Carga completa inferior a 300ms a costa de no acoplar librerías pesadas (ej, prohibimos la inclusión directa del paquete grande de "lodash", importando subnodos únicamente).

---

<div style="page-break-after: always;"></div>

## 7. Resultados Obtenidos e Implementación

Tras completar las rondas de desarrollo delimitadas en el roadmap general, Codeo cumple firmemente con la totalidad de exigencias delineadas. El sistema de inicio de sesión central funciona inyectando tokens de acceso vía persistencia intermitente, permitiendo navegación estricta controlada por guardias Angular inter-pares. El entorno del Modo Historia ejecuta satisfactoriamente secuencias de Markdown transformado sobre el componente educativo; mientras el motor Serverless en AWS corre paralelamente el input textual escrito resolviendo con veracidad los retos de programación para devolver logs precisos y retroalimentación interactiva y animada de las subidas de XP y Monedas.

Se validó exitosamente que las transacciones y ataques simulados de *Denegación de Servicio sobre Compra de Objetos o validación de Nivel* son correctamente filtrados, rebotando en un 100% las operaciones corruptas de intentos de piratear sumas de monedas falsas hacia la Base de Datos, constatando que el Modo Roguelike mantendrá intacta su filosofía competitiva basada en la confianza ciega sobre el BackEnd (`Zero Trust Client`).

---

<div style="page-break-after: always;"></div>

## 8. Retrospectiva y Lecciones Aprendidas

Esta inmersiva experiencia permitió al equipo desarrollar un panorama profundo acerca de metodologías ágiles en entornos reales de integración y desarrollo.
- **Aciertos Notables (What Went Well):**
  - La migración radical hacia `Signals` en Angular fue un éxito absoluto: la legibilidad y la fluidez cognitiva para comprender como la UI reacciona fue de 10x respecto a los complejos flujos subyacentes pasados regidos por un exceso de programación en RxJS tradicional y suscripciones "zombis" que fugaban memoria.
  - El uso de la Promoción de Propiedades en DTOs y enumerados tipados en el código de Laravel impidió toneladas de fallos técnicos de interoperabilidad (HTTP type cast errors). 

- **Dificultades Afrontadas y Superadas (What Did Not Go So Well initially):**
  - El planteamiento inicial del validador y compilador pretendía basarse en contenedores Docker interactivos. Esto suponía un cuello de botella latente por los tiempos de inicialización (Cold Starts). Pivotar drásticamente hacia los adaptadores y API Gateway de funciones AWS Lambda requirió días intensivos de investigación fuera de la zona de confort, problemas de formato en el retorno de las promesas de la lambda y lidiar con los IAM Roles de Amazon, pero finalmente se erigió como la solución definitiva.
  - La sincronización asíncrona (Async Management) en transiciones de interfaz complejas causaron picos ocasionales de "Layout Shift" que fueron dolorosos de pulir bajo las capas estrictas del Control Flow moderno del Framework. 

---

<div style="page-break-after: always;"></div>

## 9. Conclusiones y Líneas de Trabajo Futuro

A través del cumplimiento incesante de las pautas de ingeniería técnica expuestas y la asimilación del rigor impuesto para arquitecturas robustas, **Codeo** es capaz de postularse no sólo funcional, sino empresarialmente maduro para expandirse como plataforma Saas de enseñanza E-Learning y EdTech innovadora.

Al tratar esta memoria como un punto y coma a un proyecto vivo, se contemplan grandes campos para el trabajo a futuro, focalizando el foco hacia características sociales multijugador asíncrono y en tiempo real, expansión procedural algorítmica y dotar a la arquitectura de AWS Lambda Serverless la oportunidad de brindar soporte multiplataforma inyectando el procesamiento de lenguajes compilados y pesados como C++, Rust o Java bajo el mismo paraguas reactivo de la UI ya construido.

Abrazar este proyecto de final de ciclo no ha implicado únicamente teclear ficheros de código; ha significado conceptualizar desde la nada psicológica hasta la cima técnica cómo fabricar tracción, resolver rompecabezas arquitectónicos para la escalabilidad e implicó pulirse activamente bajo roles de diseño UI, especialistas backend y pedagogos educacionales unidos en una misma entidad.

---

<div style="page-break-after: always;"></div>

## 10. Bibliografía

A continuación, se referencian las documentaciones oficiales y de carácter técnico consumidas indispensablemente:

- **Ecosistema y Motor Reactivo (Frontend):**
  - Documentación Oficial de Angular. (2025). _Reactivity with Signals and Standalone Component APIs_. Recuperado de: [https://angular.dev](https://angular.dev)
  - Documentación Tailwind CSS V3+. (2025). _Utility-First Fundamentals & Configuration Tokens_. Recuperado de: [https://tailwindcss.com/docs](https://tailwindcss.com/docs)
- **Ecosistema Arquitectura de Servicios y BBDD (Backend):**
  - Documentación Oficial de Laravel 12. (2025). _Eloquent ORM, Security, Routing and Sanctum_. Recuperado de: [https://laravel.com/docs/12.x](https://laravel.com/docs/12.x)
  - PHP 8.3 Manual Oficial (2024). _Readonly Classes, Enums, Null Coalescing, Match Expressions_. [https://www.php.net/manual/es/](https://www.php.net/manual/es/)
  - Documentación oficial de PHPUnit. Recuperado de: [https://phpunit.de/](https://phpunit.de/)
- **Educación Técnica (Gamificación y Arquitectura de Ejecución):**
  - Chou, Yu-kai. (2015). _Actionable Gamification: Beyond Points, Badges, and Leaderboards (Octalysis Framework)_.
  - Documentación de AWS Lambda y Serverless Architectures. Recuperado de la Biblioteca de Amazon Web Services - [https://aws.amazon.com/es/lambda/](https://aws.amazon.com/es/lambda/)

---

<div style="page-break-after: always;"></div>

## 11. Anexos

En esta sección se añadirán posteriormente, conforme exigencias académicas, resúmenes contables u hojas en alta definición abarcando:
- *Anexo I:* Modelos de Relación de Base de Datos.
- *Anexo II:* Wireframes de las pantallas base de la Interfaz antes de integrarlas con código.
- *Anexo III:* Gráfico explicativo del Ciclo de Vida del Sandbox de Ejecución del Código (Diagrama UML).
- *Anexo IV:* Tabla detallada de Balance Económico de Puntos de Experiencia (Desglose lineal de 500 XP y las recompensas del Battle Pass).
