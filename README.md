# JoyeriaWeb

Sistema web monolítico en Laravel para la gestión integral de una joyería fina: punto de venta (POS), compra y valuación de metales preciosos por peso y pureza, control de inventario mediante movimientos inmutables, arqueo de caja por turnos, reportes gerenciales y sitio web público de imagen de marca.

---

## 📋 Documentación del Proyecto

El sistema cuenta con especificaciones completas para el equipo de desarrollo en el directorio [`docs/`](docs/README.md):

- **[01. PRD (Requisitos del Producto)](docs/01-prd.md):** Objetivos del negocio, roles de usuario, historias de usuario y reglas comerciales.
- **[02. TRD (Requisitos Técnicos)](docs/02-trd.md):** Arquitectura del sistema, stack tecnológico, librerías autorizadas, seguridad y patrones de servicio.
- **[03. Flujo de la Aplicación](docs/03-flujo-app.md):** Mapa de navegación, accesos por rol y comportamiento detallado de cada pantalla y botón.
- **[04. Brief de Diseño](docs/04-brief-diseno.md):** Sistema de diseño "Lujo Sobrio", paleta de colores, tipografías (`Cormorant Garamond` y `Jost`) y formato de documentos impresos.
- **[05. Esquema de Base de Datos](docs/05-esquema-bd.md):** Estructura completa de tablas en MySQL 8, tipos de datos, índices, llaves foráneas y reglas de integridad.
- **[06. Plan de Implementación](docs/06-plan-implementacion.md):** Hoja de ruta de desarrollo en fases cortas y verticales (Fase 0 a Fase 9).

---

## 🛠️ Requisitos del Sistema

- **PHP:** 8.3 o superior (recomendado PHP 8.5) con extensiones: `pdo_mysql`, `intl`, `bcmath`, `mbstring`, `curl`, `gd` o `imagick`.
- **Base de Datos:** MySQL 8.0+ (InnoDB, `utf8mb4_unicode_ci`).
- **Gestor de Paquetes PHP:** Composer 2.x.
- **Frontend / Assets:** Node.js 18+ y NPM.
- **Caché y Colas:** Redis (con Laravel Horizon en entornos de producción/staging).

---

## 🚀 Guía de Instalación Local

### 1. Clonar el repositorio
```bash
git clone https://github.com/77268956/joyeria-web.git
cd joyeria-web
```

### 2. Instalar dependencias
```bash
composer install
npm install
```

### 3. Configurar variables de entorno
Copia el archivo de ejemplo y genera la clave de la aplicación:
```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` con las credenciales de tu base de datos local en MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joyeria_web
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Ejecutar migraciones y datos iniciales
```bash
php artisan migrate --seed
```

### 5. Compilar assets y levantar el servidor
En terminales separadas (o mediante tu entorno local como Laragon / Herd):
```bash
# Compilación y recarga en vivo de Vite
npm run dev

# Servidor de desarrollo de Laravel
php artisan serve
```

---

## 🏛️ Arquitectura y Reglas de Desarrollo

Para mantener la solidez y consistencia del código, todo el equipo debe seguir las siguientes reglas arquitectónicas:

### 1. Capa de Servicios (`app/Services/`)
- La lógica de negocio **no se escribe en controladores ni en componentes Livewire**.
- Los controladores y componentes Livewire actúan únicamente como adaptadores de entrada y delegan el procesamiento a clases de servicio:
  - `PricingCalculator`: Única clase autorizada para ejecutar fórmulas de valuación de metales (oro por quilataje y plata por ley en milésimas).
  - `InventoryService`: Único punto autorizado para modificar existencias mediante inserción de registros inmutables en `inventory_movements`.
  - `SaleService`: Procesamiento transaccional de ventas, ítems, pagos y egreso de inventario.
  - `MetalPurchaseService`: Valuación, compras de metales usados y asignación de destino (inventario de ocasión o fundición).
  - `CashSessionService`: Control de turnos de caja, ingresos, retiros y arqueo.

### 2. Manejo Numérico y Monetario
- **Prohibido el uso de `float` o `double`** para representar dinero, porcentajes o pesos.
- En base de datos se utiliza estrictamente `DECIMAL`:
  - Pesos: `DECIMAL(10,3)` (sufijo `_g`).
  - Dinero / Precios: `DECIMAL(12,2)`.
  - Precio por gramo: `DECIMAL(12,4)`.
  - Pureza (*fineness*): `DECIMAL(5,4)`.
- En PHP se realizan los cálculos de alta precisión mediante la librería `brick/money`.

### 3. Atomicidad e Integridad de Datos
- Cualquier operación que modifique dinero, inventario o turnos de caja debe envolverse en una transacción de base de datos (`DB::transaction`).
- Las llaves foráneas de documentos financieros utilizan `ON DELETE RESTRICT` para impedir la eliminación accidental de registros con histórico contable.
- Las tablas de movimientos y bitácora (`inventory_movements`, `cash_movements`, `metal_prices`, `activity_log`) son **estrictamente inmutables** (solo `INSERT`, nunca `UPDATE` ni `DELETE`).

### 4. Idioma y Convenciones
- **Código:** Nombres de tablas, columnas, clases, métodos y variables en **inglés**.
- **Interfaz de Usuario:** Textos, etiquetas, validaciones y mensajes de error en **español**, centralizados en los archivos de traducción de Laravel (`lang/es/`).

---

## 🧪 Pruebas y Calidad de Código

### Ejecutar la suite de pruebas (PHPUnit)
```bash
php artisan test
```

### Formateador de código (Laravel Pint)
Antes de confirmar cualquier cambio, ejecuta el formateador estándar del proyecto:
```bash
vendor/bin/pint
```
