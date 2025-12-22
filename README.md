# QuickRestaurant OMS

> Sistema profesional de gestión de órdenes para restaurantes, con KDS (Kitchen Display System) y POS (Punto de Venta) básico, desarrollado en Laravel y Filament.

---

## Descripción

QuickRestaurant OMS es una solución integral para la administración eficiente de restaurantes. Permite gestionar órdenes, platillos, categorías y usuarios, integrando un sistema de visualización en cocina (KDS) y un punto de venta (POS) intuitivo y moderno.

El sistema está diseñado para ser fácil de usar, robusto y escalable, facilitando la operación diaria y mejorando la experiencia tanto del personal como de los clientes.

## Características principales

- Gestión de órdenes en tiempo real (OMS)
- Visualización de órdenes en cocina (KDS)
- Punto de venta (POS) web amigable
- Administración de platillos y categorías
- Control de usuarios y roles
- Panel administrativo con estadísticas y reportes
- Interfaz moderna y responsiva

## Estructura de rutas principales

- `/` — Página de bienvenida
- `/login` — Autenticación de usuarios
- `/admin` — Panel administrativo (Filament)
- `/admin/orders` — Gestión de órdenes
- `/admin/dishes` — Gestión de platillos
- `/admin/categories` — Gestión de categorías
- `/admin/users` — Gestión de usuarios
- `/kitchen` — Pantalla de cocina (KDS)
- `/pos` — Punto de venta (POS)

## Módulos y componentes

- **OMS:** Motor central de gestión de órdenes, con seguimiento de estado y asignación a cocina.
- **KDS:** Visualización clara y en tiempo real de las órdenes para el personal de cocina.
- **POS:** Interfaz para toma de pedidos y cobro, con carrito y cálculo automático de totales.
- **Panel de administración:** Basado en Filament, permite gestionar todos los recursos del restaurante.

## Instalación y uso

1. Clona el repositorio:
   ```bash
   git clone https://github.com/romantrapero/QuickRestaurant.git
   cd QuickRestaurant
   ```
2. Instala dependencias:
   ```bash
   composer install
   npm install && npm run build
   ```
3. Copia el archivo de entorno y configura tus variables:
   ```bash
   cp .env.example .env
   # Edita .env según tu entorno
   ```
4. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```
5. Ejecuta migraciones y seeders:
   ```bash
   php artisan migrate --seed
   ```
6. Inicia el servidor:
   ```bash
   php artisan serve
   ```
7. Accede a la aplicación en tu navegador:
   - [http://localhost:8000](http://localhost:8000)

## Tecnologías utilizadas

- Laravel 12+
- Filament v4
- Livewire
- Alpine.js
- Tailwind CSS
- MySQL

## Contribuciones

¡Las contribuciones son bienvenidas! Por favor, abre un issue o pull request para sugerencias, mejoras o reportes de bugs.

## Licencia

Este proyecto está bajo la licencia MIT.
