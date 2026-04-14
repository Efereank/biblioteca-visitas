
# 📚 Sistema de Control de Visitas - Biblioteca Pública del Zulia "María Calcaño"

Sistema web para la gestión integral de visitantes en la biblioteca. Permite registrar entradas y salidas, consultar historial, administrar visitantes, visualizar estadísticas en tiempo real y generar reportes gráficos.

## ✨ Funcionalidades principales

- **Dashboard** con KPIs en tiempo real y gráficos de visitas por tipo de visitante.
- **Registro de visitas** mediante un wizard de 4 pasos con búsqueda de cédula en tiempo real.
- **Validación de cédula** y control de visitas activas.
- **Historial de visitas** con filtros avanzados (fecha, propósito, estado, cédula) y registro de salida.
- **Gestión de visitantes** con tarjetas informativas, indicador de visitante frecuente y eliminación segura.
- **Reportes gráficos** (radar, barras, flujo horario) usando Chart.js.
- **Interfaz responsive** y moderna con Tailwind CSS y Alpine.js.
- **Alertas elegantes** con SweetAlert2.

## 🛠️ Tecnologías utilizadas

- **Backend:** Laravel 13.x (PHP 8.3)
- **Frontend:** HTML5, CSS3 (Tailwind CSS), JavaScript (Alpine.js, Chart.js, SweetAlert2)
- **Base de datos:** MySQL / MariaDB
- **Build tool:** Vite

## 📋 Requisitos del sistema

- **PHP** >= 8.1
- **Composer** (gestor de dependencias de PHP)
- **Node.js** >= 16.x y **npm**
- **MySQL** o **MariaDB**
- **Git** (opcional, para clonar el repositorio)

## 🚀 Instalación paso a paso

**1. Clonar el repositorio:** `git clone https://github.com/tu-usuario/biblioteca-visitas.git` y luego `cd biblioteca-visitas`

**2. Instalar dependencias de PHP:** `composer install`

**3. Instalar dependencias de Node.js:** `npm install`

**4. Configurar el archivo de entorno:** Copia el archivo `.env.example` y renómbralo como `.env` con `cp .env.example .env`. Luego edita el archivo `.env` con los datos de tu base de datos: `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=tu_bd`, `DB_USERNAME=tu_usuario`, `DB_PASSWORD=tu_contraseña`.

**5. Generar la clave de la aplicación(Solo si no se crea):** `php artisan key:generate`

**6. Ejecutar migraciones y seeders:** `php artisan migrate --seed`. Esto creará todas las tablas y poblará los datos iniciales (tipos de visitante, propósitos, actividades).

**7. Compilar los assets (CSS y JavaScript):** Para entorno de desarrollo `npm run dev`. Para producción `npm run build`.

**8. Agregar la imagen de la biblioteca:** Coloca una imagen llamada `biblioteca.jpg` dentro de la carpeta `public/images/`. Esta imagen se usará como banner principal en la aplicación.

**9. Iniciar el servidor local:** `php artisan serve`. Ahora puedes acceder a la aplicación en: **http://127.0.0.1:8000**


## 🧪 Usuarios de prueba

No se requiere autenticación por ahora. El sistema está diseñado para uso interno del personal de la biblioteca. En nuevas actualizaciones se estará agregando


## 📄 Licencia

Este proyecto fue desarrollado para la Biblioteca Pública del Zulia "María Calcaño". Código abierto bajo licencia MIT.

## 👤 Autor

UPTMA

---
