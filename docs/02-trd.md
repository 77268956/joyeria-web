# TRD: Requisitos Técnicos de JoyeriaWeb

> **Documento de requisitos técnicos. Versión 1.0.**  
> *Complementa al [PRD](file:///c:/laragon/www/joyeria-web/docs/01-prd.md) y fija las decisiones técnicas de arquitectura, paquetes y reglas para el desarrollo.*

---

## 1. Propósito y cómo usar este documento

Este documento es el mapa técnico definitivo del proyecto JoyeriaWeb. Define el stack tecnológico, las herramientas, las integraciones, la arquitectura y las reglas de construcción para desarrolladores y agentes de IA.

> [!IMPORTANT]
> - Si algo no está especificado en este documento o en el PRD, se debe preguntar al usuario antes de decidir por cuenta propia.
> - Si el PRD y este documento difieren: **manda el PRD en lo funcional** y **este documento en lo técnico**.

---

## 2. Decisiones técnicas clave

| Tema | Decisión |
| :--- | :--- |
| **Arquitectura** | Monolito modular en Laravel, un solo repositorio. |
| **Lenguaje y framework** | PHP 8.3 o superior (PHP 8.5 instalado), Laravel en su última versión estable (Laravel 12). |
| **Base de datos** | MySQL 8, motor InnoDB, codificación `utf8mb4`, collation `utf8mb4_unicode_ci`. |
| **Servidor** | Ubuntu Server con Apache 2.4 y PHP-FPM, siempre bajo HTTPS. En local: Laragon / Herd. |
| **Interfaz del panel** | Blade con Livewire 3 y Alpine.js. |
| **Estilos** | Bootstrap 5 personalizado con variables SCSS propias. |
| **Colas y caché** | Redis, con Laravel Horizon y Supervisor. |
| **Dinero y pesos** | `DECIMAL` en base de datos y `brick/money` en código; **nunca `float` ni `double`**. |
| **Multisucursal** | Columna `store_id` en tablas operativas y filtro global por modelo; sin interfaz multi-sucursal en esta versión. |
| **Pruebas** | PHPUnit (incluido con Laravel). |

---

## 3. Arquitectura

La aplicación se compone de dos superficies principales:
1. **Sitio público:** Rutas públicas y abiertas, vistas Blade de solo lectura, diseño editorial de alta gama. Nunca expone datos confidenciales como precios de costo, márgenes o proveedores.
2. **Panel interno administrativo:** Rutas protegidas por autenticación, middleware y permisos, con componentes reactivos en Livewire 3 y Alpine.js.

### Principio de separación de responsabilidades:
- **Sin lógica de negocio en controladores ni componentes:** La lógica de negocio reside exclusivamente en clases de servicio dentro de `app/Services/`.
- **Transacciones de base de datos:** Todos los cambios en saldos de dinero, existencias de inventario y precios se ejecutan dentro de transacciones de base de datos (`DB::transaction`).

### Estructura de carpetas esperada:
- `app/Models/`: Modelos Eloquent con relaciones, scopes y filtro global por `store_id`.
- `app/Services/`: Lógica central de negocio:
  - `SaleService`: Procesamiento y anulación de ventas y pagos.
  - `MetalPurchaseService`: Compras de metales, valuación y asignación de destino.
  - `InventoryService`: Único punto autorizado para crear movimientos de inventario y actualizar saldos.
  - `CashSessionService`: Apertura, control de flujo, arqueo y cierre de turnos de caja.
  - `MetalPriceService`: Obtención, cálculo y persistencia del precio spot de metales.
  - `PricingCalculator`: Motor matemático unificado para valuación de metales según quilates/ley.
- `app/Livewire/`: Componentes reactivos del panel agrupados por módulo (`Pos`, `Inventory`, `Purchases`, `Cash`, `Settings`, etc.).
- `app/Http/Controllers/`: Exclusivo para el sitio público, exportaciones (Excel/PDF) y streaming de archivos protegidos.
- `app/Policies/`: Políticas de autorización Spatie por modelo.
- `app/Console/Commands/`: Comandos programados (actualización de precios, respaldos, verificaciones).
- `resources/views/public/`: Vistas Blade del sitio web público.
- `resources/views/admin/`: Vistas y layouts Blade del panel interno.
- `tests/Feature/` y `tests/Unit/`: Pruebas automatizadas.

---

## 4. Librerías y herramientas autorizadas

> [!WARNING]
> **Regla estricta:** No agregar librerías externas que no estén en esta tabla sin autorización previa. Todas las versiones deben fijarse en `composer.json` y `package.json`.

| Librería / Herramienta | Uso en el proyecto |
| :--- | :--- |
| **Laravel 12** | Framework principal, enrutamiento, Eloquent ORM, colas, validaciones y tareas programadas. |
| **Livewire 3 y Alpine.js** | Interfaz reactiva del punto de venta, módulo de compras y formularios interactivos. |
| **Bootstrap 5** | Base de estilos personalizada para panel y sitio público. |
| **spatie/laravel-permission** | Gestión de roles y permisos. |
| **spatie/laravel-activitylog** | Bitácora de auditoría para operaciones sensibles. |
| **rappasoft/laravel-livewire-tables** | Tablas dinámicas con filtros, ordenamiento, búsqueda y paginación del servidor. |
| **maatwebsite/excel** | Exportación de reportes e inventario a formato Excel. |
| **barryvdh/laravel-dompdf** | Generación de facturas, comprobantes de compraventa y reportes en PDF. |
| **picqer/php-barcode-generator** | Generación de códigos de barras (Code128) y QR para etiquetas térmicas. |
| **brick/money** | Cálculos monetarios de alta precisión sin imprecisiones de coma flotante. |
| **SweetAlert2** | Diálogos de confirmación modales y alertas estilizadas según el brief de diseño. |
| **Chart.js** | Gráficos estadísticos del dashboard y reportes. |
| **IMask** | Máscaras de entrada para importes monetarios, pesos y documentos de identidad. |
| **GSAP con ScrollTrigger** | Animaciones suaves del sitio web público. |
| **Swiper.js** | Carruseles responsivos del catálogo del sitio público. |
| **Web Serial API** | Comunicación directa con la balanza digital desde el navegador. |
| **GoldAPI.io o Metals.dev** | Consulta automatizada del precio internacional del oro y la plata. |
| **Redis y Laravel Horizon** | Motor de caché de alto rendimiento y supervisor de colas de procesamiento. |
| **spatie/laravel-backup** | Respaldos automáticos cifrados de base de datos y archivos. |

---

## 5. Base de datos y convenciones

- **Nombres:** Tablas en inglés, plural y `snake_case`. Columnas en `snake_case`.
- **Llave primaria:** `id` (`BIGINT UNSIGNED`, auto-incremental).
- **Llaves foráneas:** `tabla_singular_id` con índice explícito.
- **Campos numéricos:**
  - Pesos: `DECIMAL(10,3)` con sufijo `_g` (gramos).
  - Dinero / Precios: `DECIMAL(12,2)`.
  - Precio por gramo: `DECIMAL(12,4)`.
  - Porcentajes: `DECIMAL(5,2)`.
  - Pureza (Fineness): `DECIMAL(5,4)` (ej. 18K = 0.7500, Ley 925 = 0.9250).
  - **Prohibido el uso de `FLOAT` o `DOUBLE`**.
- **Tiempos y fechas:** Fechas/horas almacenadas en UTC (`TIMESTAMP`), mostradas en la zona horaria de la tienda.
- **Estados:** `VARCHAR(30)` validado mediante Enums de PHP (no `ENUM` de MySQL).
- **Multisucursal:** Todas las tablas operativas contienen `store_id` (FK a `stores`) y forman parte de los índices compuestos primarios.

---

## 6. Módulos y reglas técnicas

### 6.1 Cálculo de metales (`PricingCalculator`)
Toda fórmula matemática relacionada con el cálculo de metales se centraliza en `app/Services/PricingCalculator.php`:
- **Oro:**
  $$\text{Monto} = \text{Peso neto (g)} \times \text{Precio gramo 24k} \times \left(\frac{\text{Quilates}}{24}\right) \times (1 - \text{Descuento})$$
- **Plata:**
  $$\text{Monto} = \text{Peso neto (g)} \times \text{Precio gramo puro} \times \left(\frac{\text{Ley milésimas}}{1000}\right) \times (1 - \text{Descuento})$$
- El redondeo a 2 decimales se define de manera unificada. Debe contar con pruebas unitarias exhaustivas para 10k, 14k, 18k, 24k, 925 y 950.

### 6.2 Inventario
- Los saldos de productos o lotes **nunca se editan directamente**.
- Cada alteración de existencias se ejecuta mediante `InventoryService`, insertando un registro inmutable en `inventory_movements`.
- El estado del producto (`available`, `reserved`, `sold`, `melting`, `withdrawn`) se actualiza dentro de la misma transacción.

### 6.3 Punto de venta (POS)
- Toda venta se encapsula en una sola transacción (`SaleService`): inserción de `sales`, `sale_items`, `payments`, creación de `inventory_movements` y registro en el turno de caja abierto.
- Es requisito mandatorio tener una sesión de caja abierta (`cash_session`).
- El servidor siempre recalcula y valida importes, precios y existencias; nunca se confía en cálculos provenientes del cliente.
- Anulaciones requieren permiso `sales.void` y registran motivo, revirtiendo existencias y dinero.

### 6.4 Compra de metales
- Requiere cliente identificado con tipo/número de documento, fotografía del documento y fotografía de la pieza.
- Guarda instantáneas inmutables (`fineness_snapshot`, `price_per_gram_snapshot`, `discount_percent_snapshot`).
- Si el monto supera el umbral parametrizado en `settings`, pasa a estado `pending_approval` y requiere aprobación de un usuario con permiso `purchases.approve`.
- Al liquidarse, la pieza se destina a inventario usado o lote de fundición mediante movimiento.

### 6.5 Caja
- Un usuario no puede poseer más de un turno con estado `open` simultáneamente.
- Al cerrar el turno, se calcula el importe esperado según transacciones y se registra lo contado físicamente, almacenando cualquier faltante o sobrante con nota justificativa.
- Una vez cerrado el turno, queda bloqueado para modificaciones.

### 6.6 Dashboard y reportes
- Consultas agregadas pesadas se almacenan en caché de Redis con expiración corta.
- Reportes extensos se procesan mediante colas de Laravel con descarga diferida.

---

## 7. Integraciones

1. **Precio del metal:** Comando programado diario (`app/Console/Commands/FetchMetalPrices.php`) que consulta GoldAPI.io o Metals.dev a través de una interfaz abstracta (`MetalPriceProviderInterface`). Permite reintentos en cola, fallback al último precio y ajuste manual con auditoría (`metal_price.override`).
2. **Balanza digital:** Integración con Web Serial API de JavaScript para lectura en navegadores Chrome/Edge mediante protocolo serial sobre USB. Aislado en adaptador por modelo, con soporte de captura manual obligatoria claramente marcada en base de datos.
3. **Impresión térmica:** Generación de tickets de 80 mm mediante estilos CSS de medios `@media print` en navegador. Comprobantes legales y facturas en formato PDF mediante `barryvdh/laravel-dompdf`.

---

## 8. Seguridad y privacidad

- **Almacenamiento privado:** Documentos de identidad y fotografías de piezas de compra se guardan en el disco privado `storage/app/private/` (fuera del directorio público `public/`) y se sirven exclusivamente a través de rutas protegidas que validan permisos.
- **Protección de datos:** Protección activa contra CSRF, inyección SQL (consultas preparadas de Eloquent) y XSS (escapado de Blade).
- **Límite de intentos y sesión:** `throttle` en autenticación y cierre de sesión automático tras inactividad.
- **Permisos base:** `sales.create`, `sales.void`, `layaways.manage`, `purchases.create`, `purchases.approve`, `inventory.view`, `inventory.adjust`, `products.edit_price`, `metal_price.override`, `cash.open`, `cash.close`, `customers.manage`, `reports.view`, `users.manage`, `settings.manage`.

---

## 9. Calidad y pruebas

- Pruebas unitarias para `PricingCalculator`.
- Pruebas de funcionalidad (Feature) para: flujo completo de ventas, compras de metales, anulaciones, turnos de caja y ajustes de inventario.
- Verificación del scope global multi-sucursal (`store_id`).
- Todo el código modificado debe formatearse mediante Laravel Pint (`vendor/bin/pint --format agent`).
