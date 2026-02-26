# Guía de Preparación del Entorno de Desarrollo

Bienvenido al proyecto Codeo. Esta guía te indicará paso a paso cómo preparar tu entorno local y ejecutar tanto el Frontend (Angular) como el Backend (Laravel) para que todo funcione correctamente.

## 📋 Requisitos Previos (Dependencias del Sistema)

Asegúrate de tener instalado el siguiente software en tu sistema (Windows, macOS o Linux):
- **PHP** (v8.2 o superior)
- **Composer** (Gestor de dependencias de PHP)
- **Node.js** (v18.19+ o versión LTS recomendada para Angular 21)
- **npm** (Viene integrado con Node.js)
- **MySQL** (v8.0+) o alternativamente tener **Docker Desktop** instalado si prefieres levantar la base de datos y servicios con Laravel Sail.
- **Git**

---

## 🛠️ 1. Configuración del Backend (Carpeta `Back/`)

El backend está construido con **Laravel 12**.

### Opción A (Recomendada con Docker/Sail):
Si tienes Docker Desktop, puedes levantar todo sin necesidad de tener PHP ni MySQL instalados localmente en tu sistema.
1. Abre tu terminal y ve a la carpeta del backend.
   ```bash
   cd Back
   ```
2. Instala las dependencias (necesitas PHP local o puedes usar una imagen de composer temporal si no lo tienes).
   ```bash
   composer install
   ```
3. Crea y configura tu archivo de entorno. (Copia el de ejemplo).
   ```bash
   cp .env.example .env
   ```
   **Nota**: Por defecto, en el `.env` el bloque de base de datos (`DB_HOST=mysql`, `DB_USERNAME=sail`, `DB_PASSWORD=password`) ya está preparado para funcionar directamente con Sail.
4. Inicia los contenedores (esto descargará las imágenes de PHP y MySQL si es la primera vez).
   ```bash
   ./vendor/bin/sail up -d
   ```
5. Genera la clave de encriptación de Laravel:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```
6. Ejecuta las migraciones y rellena (seed) la base de datos con los datos maestros (Temas, Mejoras, Niveles, Logros, etc.):
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

### Opción B (Desarrollo Local sin Docker):
Si prefieres usar XAMPP, Laragon, o tener PHP y MySQL instalados directamente:
1. Asegúrate de que tu servicio MySQL (por ejemplo vía XAMPP) esté iniciado.
2. Navega a la carpeta del framework:
   ```bash
   cd Back
   ```
3. Instala las dependencias de Composer:
   ```bash
   composer install
   ```
4. Copia el archivo de entorno:
   ```bash
   cp .env.example .env
   ```
5. **Paso importante:** Abre `.env` y configura las variables de conexión a tu base de datos local:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=codeo_db  # ¡Debes crear esta base de datos manualmente en phpMyAdmin/MySQL!
   DB_USERNAME=root      # O tu usuario local
   DB_PASSWORD=          # Y tu contraseña local
   ```
6. Genera la clave de aplicación:
   ```bash
   php artisan key:generate
   ```
7. Migra las tablas y rellénalas con datos maestros:
   ```bash
   php artisan migrate --seed
   ```
8. Inicia el servidor de desarrollo de Laravel:
   ```bash
   php artisan serve
   ```
   *El servidor quedará a la escucha en `http://localhost:8000` o `http://localhost:80` (según configuración).*

---

## 🎨 2. Configuración del Frontend (Carpeta `Front/`)

El frontend está desarrollado sobre **Angular 21** haciendo uso de Tailwind CSS.

1. Abre una **nueva instancia** de la terminal (para no cerrar o interrumpir el servicio del backend) y navega a la carpeta Front:
   ```bash
   cd Front
   ```
2. Instala las dependencias de Node/Angular. Este paso descargará Angular, Tailwind CSS, etc.
   ```bash
   npm install
   ```
3. Inicia el servidor de desarrollo de Angular:
   ```bash
   npm start
   # o alternativamente: ng serve
   ```
4. El proceso tardará unos momentos en compilar, y estará accesible en:
   **`http://localhost:4200`**

Todo el código fuente y vistas se encontrarán sincronizadas. Si haces cambios en los archivos (`.ts`, `.html` o `.scss`), Angular recargará el navegador automáticamente.

---

## ⚙️ 3. Configuraciones Adicionales y Credenciales (`.env`)

En el Backend (`Back/.env`), para que ciertas funcionalidades avanzadas trabajen de forma correcta debes agregar las siguientes APIs:

*   **Autenticación con Google (OAuth):**
    Asegúrate de que estas variables en `.env` coincidan con tus credenciales de Google Cloud Platform. (Normalmente la redirección es `http://localhost:8000/api/auth/google/callback`).
    `GOOGLE_CLIENT_ID`
    `GOOGLE_CLIENT_SECRET`

*   **Pasarela de Pagos Stripe:**
    Para que el sistema de mejoras funcione, incluye las claves en tu entorno (en modo test):
    `STRIPE_PUBLISHABLE_KEY=pk_test_...`
    `STRIPE_SECRET_KEY=sk_test_...`

*   **Permitir acceso Frontend (CORS):**
    Por defecto a través de Laravel, si no te deja iniciar sesión debido a un problema de "Blocked by CORS", puedes configurar tu URL en el archivo de entorno (o desde `config/cors.php`). Si ves un error así, revisa de asignar en el `.env` (si es el caso) variables como `FRONTEND_URL=http://localhost:4200` e incluir tu dominio en `SANCTUM_STATEFUL_DOMAINS` si decides usar Laravel Sanctum para cookies y estado.

## 🎉 ¡Listo para Programar!

Asegúrate siempre de tener las dos terminales activas trabajando al unísono:
1. `php artisan serve` o `./vendor/bin/sail up` corriendo en la carpeta **Back**.
2. `ng serve` o `npm start` corriendo en la carpeta **Front**.
