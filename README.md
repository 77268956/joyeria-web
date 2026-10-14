# JoyeriaWeb

Sistema de gestión, punto de venta (POS), valuación y compra de metales preciosos, control de inventario y sitio público para joyería fina.

---

## 📚 Manual y Documentación del Proyecto

El proyecto está completamente especificado en 6 documentos rectores ubicados en el directorio [`docs/`](docs/README.md):

1. **[01. PRD (Product Requirements Document)](docs/01-prd.md):** Visión del producto, objetivos, actores, alcance y requerimientos funcionales y no funcionales.
2. **[02. TRD (Technical Requirements Document)](docs/02-trd.md):** Arquitectura técnica, decisiones de stack, librerías autorizadas, seguridad y patrones de diseño.
3. **[03. Flujo de la App](docs/03-flujo-app.md):** Recorridos de usuario, mapa de navegación por roles y matriz de interacción de botones.
4. **[04. Brief de Diseño](docs/04-brief-diseno.md):** Sistema de diseño "Lujo Sobrio", paleta de colores, tipografías (`Cormorant Garamond` y `Jost`) y componentes visuales.
5. **[05. Esquema de Base de Datos](docs/05-esquema-bd.md):** Estructura completa de tablas en MySQL 8, tipos exactos (`DECIMAL`), integridad referencial, índices y seeders.
6. **[06. Plan de Implementación](docs/06-plan-implementacion.md):** Hoja de ruta por fases verticales (Fase 0 a Fase 9) y criterios de calidad.

---

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 12 / PHP 8.5
- **Base de Datos:** MySQL 8 (InnoDB, `utf8mb4_unicode_ci`)
- **Panel Administrativo:** Laravel Blade + Livewire 3 + Alpine.js
- **Estilos:** Bootstrap 5 personalizado mediante SCSS
- **Caché y Colas:** Redis con Laravel Horizon
- **Cálculo Matemático:** `brick/money`
- **Generación de Documentos:** `barryvdh/laravel-dompdf` (Facturas y comprobantes) y `@media print` (Tickets 80 mm)
- **Etiquetas y Códigos de Barra:** `picqer/php-barcode-generator`

---

## 🚀 Instalación y Puesta en Marcha (Local)

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/77268956/joyeria-web.git
   cd joyeria-web
   ```

2. **Instalar dependencias de PHP y Node:**
   ```bash
   composer install
   npm install
   ```

3. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Configurar los datos de conexión a MySQL en `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=joyeria_web
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Compilar recursos frontend y ejecutar servidor:**
   ```bash
   npm run dev
   php artisan serve
   ```

---

## 🤖 Directrices para Agentes de IA

Los agentes de IA (Antigravity, Claude Code, Cursor, Copilot, etc.) deben guiarse por las directrices especificadas en [`AGENTS.md`](AGENTS.md) y [`CLAUDE.md`](CLAUDE.md), respetando estrictamente los 6 documentos normativos de la carpeta `docs/`.
