# Esquema de Base de Datos: JoyeriaWeb

> **Arquitectura y estructura de los datos. Versión 1.0.**  
> *Se basa en el [PRD](file:///c:/laragon/www/joyeria-web/docs/01-prd.md), el [TRD](file:///c:/laragon/www/joyeria-web/docs/02-trd.md) y el [Flujo de la App](file:///c:/laragon/www/joyeria-web/docs/03-flujo-app.md).*

---

## 1. Propósito y alcance

Este documento define cómo se organizan y guardan los datos del sistema en MySQL 8: tablas, columnas, tipos, relaciones, índices y reglas de integridad. Es la referencia para escribir las migraciones de Laravel y los modelos Eloquent. Si el código y este documento difieren, se corrige el que esté equivocado y se deja constancia aquí.

---

## 2. Convenciones generales

- **Motor y codificación:** Motor InnoDB, codificación `utf8mb4` y collation `utf8mb4_unicode_ci`.
- **Nomenclatura:** Nombres de tablas en inglés, plural y `snake_case`; columnas en `snake_case`.
- **Llaves primarias y foráneas:** Toda tabla tiene como llave primaria `id` (`BIGINT UNSIGNED`, autoincremental). Las llaves foráneas se nombran `tabla_singular_id` y siempre tienen índice.
- **Tipos de datos numéricos:**
  - Pesos en `DECIMAL(10,3)` con sufijo `_g` (gramos).
  - Dinero en `DECIMAL(12,2)`.
  - Precios por gramo en `DECIMAL(12,4)`.
  - Porcentajes en `DECIMAL(5,2)`.
  - Pureza (*fineness*) en `DECIMAL(5,4)`, por ejemplo `0.7500` (18K).
  - **Nunca se usa `FLOAT` ni `DOUBLE`**.
- **Fechas y horas:** Se guardan en UTC en columnas `TIMESTAMP` y se muestran en la zona horaria de la tienda.
- **Estados:** Se guardan como `VARCHAR(30)` validado por la aplicación (Enums de PHP), no como `ENUM` de MySQL, para facilitar cambios.
- **Columnas comunes:** Las tablas operativas llevan `store_id` (FK a `stores`), `created_at` y `updated_at`. Se indica `deleted_at` donde se usa borrado lógico (*soft delete*). Las tablas de historial inmutable (movimientos, bitácora) solo tienen `created_at` y no se actualizan ni se borran.
- **Multisucursal:** Hoy existe una sola fila en `stores`, pero todas las tablas operativas incluyen `store_id` y un índice que lo contiene, para poder crecer sin cambiar el modelo. Las tablas de catálogo general (`metals`, `metal_purities`, `categories`) son comunes a todas las sucursales.
- **Snapshots:** Los registros de ventas y compras guardan una copia de los datos que pueden cambiar después (nombre de la pieza, precio, precio del gramo, porcentaje de descuento, pureza), para que el histórico nunca se altere si se edita el catálogo.

---

## 3. Resumen de tablas por área

| Área | Tablas |
| :--- | :--- |
| **Sucursal y acceso** | `stores`, `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` |
| **Clientes** | `customers` |
| **Catálogos** | `metals`, `metal_purities`, `categories`, `locations` |
| **Inventario** | `products`, `product_photos`, `material_lots`, `inventory_movements`, `stock_counts`, `stock_count_items` |
| **Precios** | `metal_prices`, `purchase_settings` |
| **Ventas** | `sales`, `sale_items`, `payments`, `layaways`, `layaway_payments` |
| **Compra de metales** | `metal_purchases`, `metal_purchase_items`, `purchase_adjustments` |
| **Caja** | `cash_sessions`, `cash_movements` |
| **Sistema** | `settings`, `document_sequences`, `contact_messages`, `activity_log`, `jobs`, `failed_jobs`, `cache`, `sessions` |

*Las tablas de roles y permisos las crea `spatie/laravel-permission` y la tabla `activity_log` la crea `spatie/laravel-activitylog`, con su estructura estándar. Las demás de la última fila son las estándar de Laravel.*

---

## 4. Sucursal, usuarios y clientes

### `stores`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `name` | `VARCHAR(120)` | Nombre de la joyería |
| `code` | `VARCHAR(10)` | Único |
| `address` | `VARCHAR(255)` | Para comprobantes y sitio público |
| `phone` | `VARCHAR(50)` | Para comprobantes y sitio público |
| `currency` | `CHAR(3)` | Código de moneda local (ej. USD) |
| `timezone` | `VARCHAR(50)` | Zona horaria de la tienda |
| `is_active` | `BOOLEAN` | Estado de la sucursal |
| `created_at`, `updated_at` | `TIMESTAMP` | Auditoría de tiempo |

### `users`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `name` | `VARCHAR(120)` | Nombre del colaborador |
| `email` | `VARCHAR(150)` | Único |
| `password` | `VARCHAR(255)` | Hash de Laravel |
| `is_active` | `BOOLEAN` | Desactivar en lugar de borrar |
| `last_login_at` | `TIMESTAMP NULL` | Último inicio de sesión |
| `remember_token` | `VARCHAR(100) NULL` | Recordar sesión |
| `deleted_at` | `TIMESTAMP NULL` | Borrado lógico (*soft delete*) |
| `created_at`, `updated_at` | `TIMESTAMP` | Marcas de tiempo |

### `customers`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `full_name` | `VARCHAR(150)` | Nombre completo |
| `document_type` | `VARCHAR(20)` | Por ejemplo cédula, DNI o pasaporte |
| `document_number` | `VARCHAR(40)` | Índice único junto con `store_id` y `document_type` |
| `phone` | `VARCHAR(50) NULL` | Teléfono de contacto |
| `email` | `VARCHAR(150) NULL` | Correo electrónico |
| `address` | `VARCHAR(255) NULL` | Dirección física |
| `birth_date` | `DATE NULL` | Fecha de nacimiento |
| `id_photo_path` | `VARCHAR(255) NULL` | Ruta en disco privado (`storage/app/private/`) |
| `notes` | `TEXT NULL` | Observaciones |
| `deleted_at` | `TIMESTAMP NULL` | Borrado lógico |
| `created_at`, `updated_at` | `TIMESTAMP` | Marcas de tiempo |

---

## 5. Catálogos

### `metals`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `code` | `VARCHAR(20)` | Único: `GOLD`, `SILVER`, `STEEL`, `FANTASY` |
| `name` | `VARCHAR(50)` | Nombre descriptivo |
| `is_precious` | `BOOLEAN` | Solo oro y plata tienen precio por gramo |

### `metal_purities`
*El campo `fineness` es el que usa `PricingCalculator`. Para quilates se calcula como quilates $\div$ 24 y se guarda ya resuelto para no recalcularlo.*

| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `metal_id` | `BIGINT UNSIGNED` | FK `metals` |
| `code` | `VARCHAR(10)` | 10K, 14K, 18K, 24K, 925, 950 |
| `label` | `VARCHAR(30)` | Texto mostrado, por ejemplo "Oro 18k" |
| `fineness` | `DECIMAL(5,4)` | 18K = 0.7500, 925 = 0.9250 |
| `is_active` | `BOOLEAN` | Estado activo |

### `categories` y `locations`
- `categories`: `id`, `name`, `slug` (único), `is_active`. Ejemplos: anillos, collares, aretes, pulseras, relojes.
- `locations`: `id`, `store_id` (FK `stores`), `name`, `type` (`vitrina`, `caja_fuerte`, `taller`, `bodega`), `is_active`.

---

## 6. Inventario

### `products`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `sku` | `VARCHAR(40)` | Único junto con `store_id` |
| `barcode` | `VARCHAR(60)` | Único junto con `store_id` |
| `name` | `VARCHAR(150)` | Nombre de la pieza |
| `description` | `TEXT NULL` | Texto del sitio público |
| `category_id` | `BIGINT UNSIGNED` | FK `categories` |
| `metal_id` | `BIGINT UNSIGNED` | FK `metals` |
| `purity_id` | `BIGINT UNSIGNED NULL` | FK `metal_purities` (Nulo en acero y fantasía) |
| `weight_g` | `DECIMAL(10,3)` | Peso total de la pieza en gramos |
| `cost` | `DECIMAL(12,2)` | Nunca se muestra en el sitio público |
| `price` | `DECIMAL(12,2)` | Precio de venta |
| `location_id` | `BIGINT UNSIGNED NULL`| FK `locations` |
| `status` | `VARCHAR(30)` | `available`, `reserved`, `sold`, `melting`, `withdrawn` |
| `origin` | `VARCHAR(30)` | `new` o `used_purchase` |
| `is_featured` | `BOOLEAN` | Destacado en sitio público |
| `is_public` | `BOOLEAN` | Controla si aparece en el sitio público |
| `deleted_at` | `TIMESTAMP NULL` | Borrado lógico |
| `created_at`, `updated_at` | `TIMESTAMP` | Marcas de tiempo |

*Índices:* `(store_id, status)`, `(store_id, metal_id, purity_id)`, `(store_id, category_id)`, índice FULLTEXT en `(name, sku)`.

### `product_photos`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `product_id` | `BIGINT UNSIGNED` | FK `products` con borrado en cascada |
| `path` | `VARCHAR(255)` | Fotos públicas en disco público; internas en disco privado |
| `sort_order` | `SMALLINT` | Orden de visualización |
| `is_primary` | `BOOLEAN` | Foto principal |
| `is_public` | `BOOLEAN` | Si es visible en web |

### `material_lots`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `code` | `VARCHAR(40)` | Único junto con `store_id` |
| `metal_id`, `purity_id` | `BIGINT UNSIGNED` | FK `metals`, FK `metal_purities` |
| `initial_weight_g` | `DECIMAL(10,3)` | Peso inicial en gramos |
| `current_weight_g` | `DECIMAL(10,3)` | Se actualiza solo desde `InventoryService` |
| `location_id` | `BIGINT UNSIGNED NULL`| FK `locations` |
| `status` | `VARCHAR(30)` | `open`, `sent_to_melting`, `closed` |
| `notes` | `TEXT NULL` | Observaciones |

### `inventory_movements`
*Tabla inmutable: solo se insertan filas. Es la fuente de verdad del inventario.*

| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `product_id` | `BIGINT UNSIGNED NULL`| FK `products` (Pieza única) |
| `material_lot_id` | `BIGINT UNSIGNED NULL`| FK `material_lots` (Lote; debe haber uno de los dos) |
| `type` | `VARCHAR(30)` | `entry`, `purchase_entry`, `sale`, `sale_void`, `layaway_reserve`, `layaway_release`, `adjustment`, `to_melting` |
| `quantity_delta` | `SMALLINT` | `+1` o `-1` para piezas |
| `weight_delta_g` | `DECIMAL(10,3)` | Cambio de peso, positivo o negativo |
| `reference_type` | `VARCHAR(60) NULL` | Documento de origen (venta, compra, conteo) |
| `reference_id` | `BIGINT UNSIGNED NULL`| ID del documento de origen |
| `reason` | `VARCHAR(255) NULL` | Obligatorio en ajustes manuales |
| `user_id` | `BIGINT UNSIGNED` | FK `users` |
| `created_at` | `TIMESTAMP` | Momento exacto del movimiento |

*Índices:* `(store_id, product_id)`, `(store_id, material_lot_id)`, `(reference_type, reference_id)`, `(store_id, created_at)`.

### `stock_counts` y `stock_count_items`
- `stock_counts`: `id`, `store_id`, `status` (`open`, `finished`, `applied`), `started_by`, `started_at`, `finished_at`, `applied_by`, `applied_at`, `notes`.
- `stock_count_items`: `id`, `stock_count_id`, `product_id`, `expected` (`BOOLEAN`), `found` (`BOOLEAN NULL`), `note`.

---

## 7. Precios

### `metal_prices`
*Regla: el precio vigente de un metal es la fila más reciente de ese metal. Las filas no se editan; un cambio crea una fila nueva.*

| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `metal_id` | `BIGINT UNSIGNED` | FK `metals` (Oro o plata) |
| `price_per_gram` | `DECIMAL(12,4)` | Precio del gramo puro (24k o 1000) en moneda local |
| `spot_per_ounce` | `DECIMAL(12,4) NULL`| Precio internacional por onza troy (solo informativo) |
| `exchange_rate` | `DECIMAL(12,6) NULL`| Tipo de cambio usado |
| `source` | `VARCHAR(20)` | `api` o `manual` |
| `provider` | `VARCHAR(40) NULL` | Nombre del proveedor (ej. GoldAPI) |
| `price_date` | `DATE` | Fecha de referencia |
| `set_by` | `BIGINT UNSIGNED NULL`| FK `users` (Quien lo fijó si fue manual) |
| `reason` | `VARCHAR(255) NULL` | Obligatorio si es manual |
| `created_at` | `TIMESTAMP` | Marca temporal |

*Índice:* `(store_id, metal_id, created_at)`.

### `purchase_settings`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `metal_id` | `BIGINT UNSIGNED` | FK `metals` |
| `purity_id` | `BIGINT UNSIGNED NULL`| FK `metal_purities` (Nulo = porcentaje por defecto del metal) |
| `discount_percent`| `DECIMAL(5,2)` | Margen de la tienda en la compra |
| `updated_by` | `BIGINT UNSIGNED` | FK `users` |

*Índice único:* `(store_id, metal_id, purity_id)`.

---

## 8. Ventas y apartados

### `sales`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `number` | `VARCHAR(30)` | Consecutivo, único junto con `store_id` |
| `customer_id` | `BIGINT UNSIGNED NULL`| FK `customers` |
| `user_id` | `BIGINT UNSIGNED` | FK `users` (Vendedor) |
| `cash_session_id` | `BIGINT UNSIGNED` | FK `cash_sessions` (Turno en que se registró) |
| `subtotal` | `DECIMAL(12,2)` | Subtotal antes de descuentos |
| `discount_total` | `DECIMAL(12,2)` | Total descuentos aplicados |
| `total` | `DECIMAL(12,2)` | Total neto cobrado |
| `status` | `VARCHAR(30)` | `completed` o `voided` |
| `voided_at` | `TIMESTAMP NULL` | Fecha de anulación |
| `voided_by` | `BIGINT UNSIGNED NULL`| FK `users` |
| `void_reason` | `VARCHAR(255) NULL` | Motivo de la anulación |
| `notes` | `TEXT NULL` | Notas de venta |

### `sale_items`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `sale_id` | `BIGINT UNSIGNED` | FK `sales` |
| `product_id` | `BIGINT UNSIGNED` | FK `products` |
| `name_snapshot` | `VARCHAR(150)` | Nombre de la pieza al momento de vender |
| `metal_snapshot` | `VARCHAR(30)` | Metal al momento de vender |
| `purity_snapshot`| `VARCHAR(30)` | Pureza al momento de vender |
| `weight_g` | `DECIMAL(10,3)` | Peso en gramos |
| `unit_price` | `DECIMAL(12,2)` | Precio unitario |
| `discount` | `DECIMAL(12,2)` | Descuento aplicado al ítem |
| `line_total` | `DECIMAL(12,2)` | Total línea |

### `payments`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `sale_id` | `BIGINT UNSIGNED` | FK `sales` |
| `cash_session_id` | `BIGINT UNSIGNED` | FK `cash_sessions` |
| `method` | `VARCHAR(20)` | `cash`, `card`, `transfer` |
| `amount` | `DECIMAL(12,2)` | Monto aplicado a la venta |
| `received` | `DECIMAL(12,2) NULL`| Efectivo entregado, para calcular el cambio |
| `reference` | `VARCHAR(80) NULL` | Número de autorización o transferencia |

### `layaways` y `layaway_payments`
- `layaways`: `id`, `store_id`, `number`, `customer_id`, `product_id`, `user_id`, `total_price`, `paid_total`, `balance`, `due_date`, `status` (`active`, `completed`, `cancelled`), `cancel_reason`, `completed_sale_id` (FK `sales NULL`), `created_at`, `updated_at`.
- `layaway_payments`: `id`, `layaway_id`, `cash_session_id`, `method`, `amount`, `reference`, `user_id`, `created_at`.

---

## 9. Compra de metales

### `metal_purchases`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `number` | `VARCHAR(30)` | Consecutivo, único junto con `store_id` |
| `customer_id` | `BIGINT UNSIGNED` | FK `customers` (Obligatorio) |
| `user_id` | `BIGINT UNSIGNED` | FK `users` (Quien registró) |
| `cash_session_id` | `BIGINT UNSIGNED` | FK `cash_sessions` |
| `status` | `VARCHAR(30)` | `pending_approval`, `paid`, `rejected`, `adjusted` |
| `total_amount` | `DECIMAL(12,2)` | Suma de las piezas compradas |
| `payout_method` | `VARCHAR(20)` | `cash` o `transfer` |
| `approved_by` | `BIGINT UNSIGNED NULL`| FK `users` |
| `approved_at` | `TIMESTAMP NULL` | Fecha de aprobación |
| `approval_note` | `VARCHAR(255) NULL` | Nota de aprobación |
| `notes` | `TEXT NULL` | Notas |

### `metal_purchase_items`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `metal_purchase_id` | `BIGINT UNSIGNED` | FK `metal_purchases` |
| `metal_id` | `BIGINT UNSIGNED` | FK `metals` |
| `purity_id` | `BIGINT UNSIGNED` | FK `metal_purities` |
| `description` | `VARCHAR(150)` | Descripción de la pieza |
| `gross_weight_g` | `DECIMAL(10,3)` | Peso bruto |
| `net_weight_g` | `DECIMAL(10,3)` | Base del cálculo |
| `weight_source` | `VARCHAR(10)` | `scale` o `manual` |
| `fineness_snapshot` | `DECIMAL(5,4)` | Pureza usada |
| `price_per_gram_snapshot` | `DECIMAL(12,4)` | Precio del gramo usado |
| `discount_percent_snapshot`| `DECIMAL(5,2)` | Porcentaje usado |
| `amount` | `DECIMAL(12,2)` | Monto liquidado de la pieza |
| `photo_path` | `VARCHAR(255)` | Foto obligatoria, disco privado |
| `destination` | `VARCHAR(20)` | `used_inventory` o `melting` |
| `product_id` | `BIGINT UNSIGNED NULL`| FK `products` (Si pasó a inventario de ocasión) |
| `material_lot_id` | `BIGINT UNSIGNED NULL`| FK `material_lots` (Si pasó a materia prima fundición) |

### `purchase_adjustments`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `metal_purchase_id` | `BIGINT UNSIGNED` | FK `metal_purchases` |
| `user_id` | `BIGINT UNSIGNED` | FK `users` |
| `reason` | `VARCHAR(255)` | Obligatorio |
| `old_values` | `JSON` | Valores antes del ajuste |
| `new_values` | `JSON` | Valores después del ajuste |
| `amount_difference`| `DECIMAL(12,2)` | Diferencia económica |
| `created_at` | `TIMESTAMP` | Marca temporal |

---

## 10. Caja

### `cash_sessions`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `user_id` | `BIGINT UNSIGNED` | Quien abre el turno |
| `status` | `VARCHAR(20)` | `open` o `closed` |
| `opened_at` | `TIMESTAMP` | Fecha/hora apertura |
| `opening_amount` | `DECIMAL(12,2)` | Efectivo inicial en caja |
| `closed_at` | `TIMESTAMP NULL` | Fecha/hora cierre |
| `closed_by` | `BIGINT UNSIGNED NULL`| FK `users` |
| `expected_cash`, `expected_card`, `expected_transfer` | `DECIMAL(12,2) NULL` | Calculado por el sistema al cerrar |
| `counted_cash`, `counted_card`, `counted_transfer` | `DECIMAL(12,2) NULL` | Lo contado físicamente por el cajero |
| `difference_total` | `DECIMAL(12,2) NULL` | Contado menos esperado |
| `close_note` | `VARCHAR(255) NULL` | Obligatoria si existe diferencia |

> [!NOTE]
> **Cálculo de efectivo esperado:**
> $$\text{Efectivo Esperado} = \text{Inicial} + \text{Ventas efectivo} + \text{Abonos efectivo} + \text{Ingresos manuales} - \text{Retiros manuales} - \text{Compras metal en efectivo}$$

### `cash_movements`
| Columna | Tipo | Notas |
| :--- | :--- | :--- |
| `store_id` | `BIGINT UNSIGNED` | FK `stores` |
| `cash_session_id` | `BIGINT UNSIGNED` | FK `cash_sessions` |
| `type` | `VARCHAR(10)` | `in` o `out` |
| `amount` | `DECIMAL(12,2)` | Monto del movimiento |
| `reason` | `VARCHAR(255)` | Motivo obligatorio |
| `user_id` | `BIGINT UNSIGNED` | FK `users` |
| `created_at` | `TIMESTAMP` | Marca temporal inmutable |

---

## 11. Sistema

- `settings`: `id`, `store_id`, `key`, `value` (`JSON`), índice único `(store_id, key)`. Ejemplos: `purchase_approval_limit`, `max_discount_percent`, `layaway_days`, `ticket_footer`.
- `document_sequences`: `id`, `store_id`, `type` (`sale`, `purchase`, `layaway`), `prefix`, `next_number`. Se incrementa dentro de una transacción con bloqueo pesimista de fila (`lockForUpdate()`) para garantizar consecutivos estrictos sin duplicados.
- `contact_messages`: `id`, `store_id`, `name`, `email`, `phone`, `message`, `status` (`new`, `read`, `answered`), `ip`, `created_at`.
- `activity_log`: Estructura estándar Spatie: `log_name`, `description`, `subject`, `causer`, `properties` (`JSON`), `created_at`.

---

## 12. Relaciones principales

| Relación | Tipo |
| :--- | :--- |
| `stores` a `users`, `customers`, `products`, `sales`, `cash_sessions` | Uno a muchos |
| `metals` a `metal_purities` | Uno a muchos |
| `products` a `product_photos` | Uno a muchos (borrado en cascada) |
| `products` a `inventory_movements` | Uno a muchos |
| `sales` a `sale_items` y `payments` | Uno a muchos |
| `sale_items` a `products` | Muchos a uno |
| `customers` a `sales`, `layaways`, `metal_purchases` | Uno a muchos |
| `layaways` a `layaway_payments` | Uno a muchos |
| `metal_purchases` a `metal_purchase_items` | Uno a muchos |
| `metal_purchase_items` a `products` o `material_lots` | Muchos a uno (uno de los dos) |
| `cash_sessions` a `sales`, `payments`, `layaway_payments`, `metal_purchases`, `cash_movements` | Uno a muchos |
| `users` a `roles` y `permissions` | Muchos a muchos (Spatie) |

---

## 13. Integridad y reglas de datos

1. **Restricción de borrado (`ON DELETE RESTRICT`):** No se permite borrar un cliente, usuario, producto o turno que tenga transacciones, ventas, compras o movimientos asociados. Solo `product_photos` se elimina en cascada junto con su producto.
2. **Tablas inmutables:** `inventory_movements`, `metal_prices`, `purchase_adjustments`, `cash_movements` y `activity_log` nunca se actualizan ni se borran. Las ventas, compras y turnos cerrados se anulan o ajustan con motivo justificado.
3. **Restricciones de valor:** Pesos $> 0$, montos y precios $\ge 0$, `discount_percent` entre $0$ y $100$, `fineness` entre $0$ y $1$, `quantity_delta` $\neq 0$.
4. **Saldo de inventario derivado:** El estado de un producto (`products.status`) y el peso de un lote (`material_lots.current_weight_g`) se actualizan en la misma transacción que inserta el movimiento, con un comando de verificación de saldos programado.
5. **Transaccionalidad:** Venta, anulación, compra de metal, apartado, abono, cierre de caja y conteo físico se ejecutan en transacciones atómicas individuales.

---

## 14. Índices y rendimiento

- Todas las consultas diarias filtran por `store_id` como primera columna de índices compuestos.
- Índices adicionales:
  - `sales (store_id, created_at)`
  - `sales (store_id, status)`
  - `metal_purchases (store_id, created_at)`
  - `metal_purchases (store_id, status)`
  - `payments (cash_session_id, method)`
  - `customers (store_id, full_name)`
  - FULLTEXT en `products (name, sku)` para búsqueda ágil en mostrador.

---

## 15. Datos iniciales (Seeders)

| Seeder | Contenido |
| :--- | :--- |
| `StoreSeeder` | Sucursal principal inicial. |
| `MetalSeeder` | Catálogo base: Oro, plata, acero, fantasía. |
| `PuritySeeder` | Oro 10k (0.4167), 14k (0.5833), 18k (0.7500), 24k (1.0000); Plata 925 (0.9250) y 950 (0.9500). |
| `RolePermissionSeeder`| Roles Dueño, Gerente y Vendedor con matriz completa de permisos. |
| `UserSeeder` | Usuario Dueño inicial con cambio obligatorio de contraseña en primer ingreso. |
| `PurchaseSettingSeeder`| Porcentajes de descuento iniciales por metal/pureza. |
| `SettingSeeder` | Parámetros globales iniciales del sistema. |
| `DemoSeeder` | Piezas, clientes y ventas de demostración (únicamente en entornos locales/pruebas). |

---

## 16. Migraciones, copias y privacidad

- **Orden de ejecución:** `stores` $\rightarrow$ `users` y tablas Spatie $\rightarrow$ catálogos $\rightarrow$ `customers` $\rightarrow$ `products` y fotos $\rightarrow$ `material_lots` $\rightarrow$ `cash_sessions` $\rightarrow$ `metal_prices` y `purchase_settings` $\rightarrow$ `sales` $\rightarrow$ `payments` $\rightarrow$ `layaways` $\rightarrow$ `metal_purchases` $\rightarrow$ `inventory_movements` $\rightarrow$ `stock_counts` $\rightarrow$ `settings` y sistema.
- Migraciones estrictamente reversibles (`down()`).
- Respaldos diarios automáticos con `spatie/laravel-backup`.
- Datos de clientes protegidos en disco privado.
