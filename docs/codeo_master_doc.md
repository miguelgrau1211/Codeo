# 📔 Codeo Master Documentation: Technical & Architectural Deep Dive

Este documento maestro actúa como la fuente única de verdad para el proyecto **Codeo**. Explica la arquitectura, los sistemas internos, la pila tecnológica y las decisiones de diseño que hacen que la plataforma sea un entorno de aprendizaje gamificado de alto rendimiento.

---

## 🚀 1. Visión General del Proyecto
**Codeo** es una plataforma educativa diseñada para enseñar programación (principalmente PHP y Python) mediante retos prácticos y mecánicas de videojuego. Utiliza un enfoque de **gamificación profunda** para fomentar el aprendizaje continuo y la retención del usuario.

### Modos de Juego Principales:
- **Modo Historia:** Una campaña narrativa estructurada por capítulos teóricos y prácticos.
- **Modo Roguelike:** Un modo de supervivencia infinita con dificultad escalable y muerte permanente (reinicio de run).

---

## 🛠️ 2. Stack Tecnológico

### Frontend (Arquitectura Moderna)
- **Framework:** Angular 19+ (Uso estricto de **Signals** para estado reactivo).
- **Estilos:** Tailwind CSS con un sistema de **Design Tokens** para personalización dinámica.
- **Rendimiento:** Cambio de detección `OnPush` global y `@defer` para carga diferida de componentes pesados como el editor de código.
- **Iconografía:** Lucide Angular para una interfaz limpia y profesional.

### Backend (Robustez y Escalabilidad)
- **Framework:** Laravel 11+ (PHP 8.3).
- **Patrones de Diseño:** 
    - **Actions:** Lógica de negocio encapsulada fuera de los controladores.
    - **DTOs (Data Transfer Objects):** Para transporte seguro de datos entre capas.
    - **Skinny Controllers:** Los controladores solo gestionan la entrada HTTP y la respuesta.
- **Base de Datos:** Relacional (MySQL) con uso extensivo de `Eloquent`.
- **Caché:** Redis/File para ráfagas de datos y sesiones de Roguelike.

### Infraestructura de Ejecución
- **Motor de Evaluación:** Los retos se ejecutan de forma aislada en **AWS Lambda**, enviando el código del usuario y los casos de prueba para obtener resultados en milisegundos sin riesgo para el servidor principal.

---

## 🏗️ 3. Arquitectura del Sistema

### 3.1. Sistema de Progresión y Niveles
El backend separa los niveles en dos entidades: `NivelesHistoria` y `NivelRoguelike`.
- Cada nivel define un campo JSON de `test_cases` que contiene pares de `input` y `output`.
- El sistema de validación normaliza las salidas (ej. convirtiendo `1.0` a `1`) para evitar fallos por formato en tipos de datos similares.

### 3.2. Motor de Gamificación
- **XP & Niveles:** Implementado vía `ProcessLevelUpAction`. La curva de experiencia es exponencial para mantener el reto.
- **Economía:** Monedas ganadas en retos que se gastan en la `Tienda de Temas` y mejoras de Roguelike.
- **Rachas (Streaks):** Un sistema de retención diaria que castiga la inactividad pero permite "congelar" rachas en niveles superiores.
- **Pase de Batalla:** Recompensas exclusivas vinculadas al nivel de experiencia del usuario.

### 3.3. Logros (Achievements)
Sistema dinámico basado en un `CheckAchievementsAction` que se dispara tras acciones clave. Utiliza un campo `operador` en la BD para validar condiciones (ej. "Niveles completados > 10").

---

## 🛡️ 4. Seguridad y Anti-Cheat

### 4.1. Roguelike Integrity
Para el modo infinito, el servidor valida:
- **Timestamping:** Se registra el inicio de cada nivel en caché. Si el envío llega después del tiempo asignado, se invalida.
- **Race Conditions:** Uso de `DB::transaction()` y `lockForUpdate()` al modificar XP y Monedas para evitar duplicaciones por clics rápidos.

### 4.2. Autenticación
- **Laravel Sanctum:** Para autenticación API robusta mediante tokens.
- **Google OAuth:** Integración nativa para un registro rápido y seguro.

---

## 🌐 5. Sistema de Traducción Dinámica (i18n)

Codeo implementa un **TranslationService** avanzado en el backend:
- **Circuit Breaker:** Si la API de traducción falla, el sistema se deshabilita temporalmente para no ralentizar al usuario con timeouts.
- **Traducción de Código:** El sistema es capaz de identificar y traducir comentarios específicos dentro del código (`codigo_inicial`) manteniendo la sintaxis intacta.
- **Bulk Translation:** Para listas largas (ej. lista de logros), el servicio agrupa los textos en una sola llamada a la API para optimizar el rendimiento.

---

## 🎨 6. Interfaz y Experiencia de Usuario (UI/UX)

- **Tematización Dinámica:** Los usuarios pueden comprar y activar "Temas" que cambian por completo las variables CSS de la aplicación (colores de acento, fondos, fuentes).
- **Retroalimentación Inmediata:** Uso de `AudioService` para sonidos de éxito/logro y `NotificationService` para toasts no intrusivos.
- **Editor Emulado:** El editor utiliza una capa de resaltado sintáctico mediante Regex en tiempo real sobre un `textarea` invisible, permitiendo ligereza técnica sin perder la apariencia de un IDE profesional.

---

## 📊 7. Panel de Administración
Un ecosistema completo para moderadores:
- **Editor de Niveles:** Interfaz visual para añadir retos y casos de prueba sin tocar código.
- **Gestión de Soporte:** Sistema de tickets clasificado (Bug, Sugerencia, Feedback).
- **Auditoría:** `AdminLog` rastrea cada acción crítica realizada por los administradores para mayor transparencia.

---

> [!TIP]
> **Codeo** está diseñado para ser extensible. La separación lógica mediante **Actions** permite añadir nuevos lenguajes de programación o modos de juego simplemente creando nuevas clases de acción, sin afectar al núcleo estable de la aplicación.
