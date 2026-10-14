# Plan de Implementación: JoyeriaWeb

> **Estrategia de desarrollo paso a paso y hoja de ruta de despliegue. Versión 1.0.**  
> *Se apoya en los otros cinco documentos: [PRD](file:///c:/laragon/www/joyeria-web/docs/01-prd.md), [TRD](file:///c:/laragon/www/joyeria-web/docs/02-trd.md), [Flujo de la App](file:///c:/laragon/www/joyeria-web/docs/03-flujo-app.md), [Brief de Diseño](file:///c:/laragon/www/joyeria-web/docs/04-brief-diseno.md) y [Esquema de Base de Datos](file:///c:/laragon/www/joyeria-web/docs/05-esquema-bd.md).*

---

## 1. Propósito y cómo usar este plan

Este plan ordena la construcción del sistema desde la primera línea de código hasta su puesta en marcha en la tienda. Está pensado para que lo siga el agente de IA que programa, supervisado por una persona que revisa y aprueba cada fase.

> [!IMPORTANT]
> Si una tarea contradice alguno de los documentos de referencia, se detiene y se pregunta al usuario antes de continuar.

---

## 2. Estrategia general

Se construye por **fases cortas y verticales**: cada fase entrega algo que ya funciona de punta a punta (pantalla, lógica de negocio, base de datos y pruebas automatizadas), en lugar de construir horizontalmente toda la base de datos y luego todas las pantallas.

### Principios rectores:
1. **Lo más riesgoso va primero:** El cálculo del metal, el inventario por movimientos inmutables y la caja son el núcleo crítico del negocio y se construyen y prueban antes del sitio público.
2. **Desarrollo guiado por pruebas:** Ninguna tarea se da por concluida sin pruebas automatizadas (PHPUnit) y verificación de estilo con Laravel Pint.
3. **No adelantar funciones de fases futuras:** Se respeta estrictamente el orden de construcción.
4. **Plantilla de trabajo para el agente en cada tarea:**
   - Leer los documentos relacionados.
   - Resumir lo que va a hacer y qué archivos tocará.
   - Escribir primero las pruebas del comportamiento esperado (TDD).
   - Programar la solución.
   - Ejecutar pruebas y formateador (`vendor/bin/pint --format agent`).
   - Revisar contra los criterios de aceptación y dejar un resumen claro de lo realizado.

---

## 3. Preparación previa

Antes de iniciar la Fase 0 deben resolverse estos puntos:

| Tema | Por qué bloquea | Necesario antes de |
| :--- | :--- | :--- |
| **País y moneda de operación** | Define impuestos, facturación y formatos de documentos de identidad | Fase 1 |
| **Marca, nombre y logotipo** | Se usan en el diseño base y en comprobantes impresos | Fase 1 |
| **Proveedor del precio del metal y plan contratado** | Se requiere la clave de la API y sus límites de llamadas | Fase 3 |
| **Balanza, impresora de tickets e impresora de etiquetas** | Determinan protocolos de comunicación y formatos de papel | Fases 4 y 7 |
| **Porcentajes de descuento de compra y límite de aprobación** | Son parámetros de cálculo y umbrales de autorización | Fase 5 |
| **Asesoría legal sobre compra de metales y facturación** | Condiciona datos obligatorios en comprobantes | Fase 5 |
| **Dominio y servidor (proveedor, plan)** | Necesario para staging y producción | Fase 0 para staging |
| **Fotografías de las piezas y del local** | Contenido para catálogo y sitio público | Fase 8 |
| **Inventario actual en hoja de cálculo** | Para la carga inicial de datos en el sistema | Fase 9 |

---

## 4. Entornos y herramientas

- **Local:** Entorno de desarrollo (Laragon / Herd en Windows, PHP 8.5, MySQL 8).
- **Staging:** Servidor de pruebas idéntico a producción (Ubuntu Server, Apache, PHP-FPM, MySQL 8, Redis, Supervisor).
- **Producción:** Tienda real bajo HTTPS estricto.
- **Ramas Git:** `main` para producción, `develop` para staging, ramas cortas por tarea o fase.

---

## 5. Fases de desarrollo

### Fase 0: Arranque del proyecto
| Paso | Tarea | Resultado |
| :--- | :--- | :--- |
| **0.1** | Crear repositorio e instalar Laravel con PHP 8.3+ | Proyecto que arranca en local. |
| **0.2** | Configurar MySQL, Redis, Vite y entorno de pruebas | Entorno reproducible documentado en README. |
| **0.3** | Instalar librerías base de la tabla del TRD | `composer.json` y `package.json` con versiones fijas. |
| **0.4** | Configurar Laravel Pint, PHPUnit e integración continua | Pruebas y formato corren en cada cambio. |
| **0.5** | Preparar servidor de staging con Apache, HTTPS y script | Página de bienvenida visible en staging. |

*Criterio de cierre:* Cualquier persona puede clonar el repositorio, seguir el README y tenerlo funcionando localmente.

---

### Fase 1: Base del sistema
| Paso | Tarea | Resultado |
| :--- | :--- | :--- |
| **1.1** | Migraciones y modelos de `stores`, `users`, `customers`, `metals`, `metal_purities`, `categories` y `locations` | Tablas según Esquema de BD. |
| **1.2** | Modelo base con filtro global por `store_id` | Modelos operativos aplican filtro multi-sucursal. |
| **1.3** | Autenticación, límite de intentos, recuperación y cierre por inactividad | Pantalla de inicio de sesión funcional. |
| **1.4** | Roles y permisos con Spatie (Dueño, Gerente, Vendedor) | Menú y acciones filtradas por rol. |
| **1.5** | Bitácora de auditoría con Activitylog | Acciones sensibles registradas. |
| **1.6** | Sistema de diseño: SCSS Bootstrap con paleta y tipografía del Brief | Layout del panel con menú lateral, navbar, tarjetas y tablas. |
| **1.7** | Seeders de catálogos, roles, permisos y usuario dueño inicial | Sistema utilizable tras instalación limpia. |
| **1.8** | Gestión de usuarios | Alta, edición y desactivación de usuarios. |

*Criterio de cierre:* El dueño inicia sesión en staging, crea un gerente y un vendedor; cada uno ve solo su menú correspondiente; las pruebas de permisos y filtro por `store_id` pasan exitosamente.

---

### Fase 2: Inventario y catálogo
| Paso | Tarea | Resultado |
| :--- | :--- | :--- |
| **2.1** | Migraciones de `products`, `product_photos`, `material_lots` e `inventory_movements` | Estructura de inventario en BD. |
| **2.2** | `InventoryService`: único punto para crear movimientos y actualizar saldos | Ningún saldo se modifica a mano. |
| **2.3** | Pantallas de alta, edición y detalle de piezas con fotos | Catálogo interno completo. |
| **2.4** | Tabla de inventario con filtros, búsqueda y exportación a Excel | Livewire Tables con exportación. |
| **2.5** | Etiquetas con código de barras | Generación e impresión de etiquetas. |
| **2.6** | Lotes de materia prima y envío a fundición | Movimientos de lote por peso. |
| **2.7** | Ajustes de existencias con motivo | Ajustes auditados y justificados. |
| **2.8** | Comando de verificación de saldos contra movimientos | Detección automática de inconsistencias. |

*Criterio de cierre:* Se registra una pieza, se imprime su etiqueta, se traslada de ubicación, se ajusta y se envía a fundición; el historial de movimientos explica con precisión su estado.

---

### Fase 3: Precio del metal y cálculo
| Paso | Tarea | Resultado |
| :--- | :--- | :--- |
| **3.1** | Migraciones de `metal_prices` y `purchase_settings` | Tablas de precios y márgenes de compra. |
| **3.2** | Interfaz del proveedor de precios (`GoldAPI` o `Metals.dev`) | Código desacoplado de la API externa. |
| **3.3** | Comando programado diario con reintentos y alertas | Persistencia automática de precios cada día. |
| **3.4** | Pantalla de precio del metal, historial y ajuste manual con motivo | Consulta y fijación manual auditada. |
| **3.5** | `PricingCalculator` con `brick/money` | Clase única para cálculos de metal exactos. |
| **3.6** | Pruebas unitarias de cálculo para 10k, 14k, 18k, 24k, 925 y 950 con redondeo | Cobertura total de fórmulas matemáticas. |
| **3.7** | Pantalla de porcentajes de descuento de compra | Configuración exclusiva para el Dueño. |

*Criterio de cierre:* El precio del día se expone en la barra superior del panel, un precio manual queda auditado en bitácora y las pruebas unitarias coinciden al centavo con cálculos manuales.

---

### Fase 4: Caja, punto de venta y clientes básicos
| Paso | Tarea | Resultado |
| :--- | :--- | :--- |
| **4.1** | Migraciones de `cash_sessions`, `cash_movements`, `sales`, `sale_items`, `payments` y `document_sequences` | Estructura de ventas y caja en BD. |
| **4.2** | Apertura de turno, ingresos y retiros de efectivo | Control de flujo de dinero en caja. |
| **4.3** | Punto de venta: búsqueda por escáner/SKU, carrito, descuentos y pago mixto | Flujo POS completo y reactivo. |
| **4.4** | `SaleService`: una sola transacción atómica por venta | Consistencia entre venta, inventario y caja. |
| **4.5** | Tickets térmicos de 80 mm y facturas en PDF | Impresión y reimpresión de comprobantes. |
| **4.6** | Anulación de ventas con motivo justificado | Movimientos reversos en dinero e inventario. |
| **4.7** | Cierre de caja con arqueo y registro de diferencias | Arqueo comparativo por método de pago. |
| **4.8** | Fichas de clientes con historial comercial | Alta, búsqueda y visualización de compras. |

*Criterio de cierre:* En staging se ejecuta un turno completo (abrir, vender, anular, retirar efectivo, cerrar) y el efectivo esperado coincide exactamente con el conteo manual.

---

### Fase 5: Compra de metales
| Paso | Tarea | Resultado |
| :--- | :--- | :--- |
| **5.1** | Migraciones de `metal_purchases`, `metal_purchase_items` y `purchase_adjustments` | Estructura de compras en BD. |
| **5.2** | Flujo de compra en tres pasos con identificación y fotos obligatorias | Registro seguro con respaldo legal. |
| **5.3** | `MetalPurchaseService` con snapshots de precio, pureza y descuento | Compra reproducible a través del tiempo. |
| **5.4** | Aprobación de gerencia para montos altos | Estado `pending_approval` para montos mayores al límite. |
| **5.5** | Destino de pieza: inventario de ocasión o fundición | Movimientos automáticos de existencias. |
| **5.6** | Comprobante legal de compraventa en PDF | Documento imprimible con espacio para firma. |
| **5.7** | Ajustes de compras con motivo justificado | Corrección auditada sin editar el original. |
| **5.8** | Integración con arqueo de caja | Egresos en efectivo restan del saldo de caja. |

*Criterio de cierre:* Se compra una joya en staging, se valúa con el precio del día, se aprueba si corresponde, se emite el comprobante y el material queda asentado en su destino.

---

### Fase 6: Apartados, conteos, dashboard y reportes
- **6.1:** Apartados y abonos parciales.
- **6.2:** Conteo físico de inventario con conciliación de diferencias.
- **6.3:** Dashboard administrativo con gráficos Chart.js y alertas de stock bajo.
- **6.4:** Reportes de ventas, compras, inventario y caja exportables a Excel y PDF.
- **6.5:** Almacenamiento en caché con Redis y reportes pesados en cola con Horizon.
- **6.6:** Pantalla de consulta de auditoría (`activity_log`).

---

### Fase 7: Balanza digital
- **7.1:** Módulo de lectura mediante Web Serial API de JavaScript.
- **7.2:** Indicador visual de conexión de balanza y captura manual identificada.
- **7.3:** Pruebas con hardware real en tienda física.

---

### Fase 8: Sitio público
- **8.1:** Vistas Blade con estética del Brief de Diseño.
- **8.2:** Catálogo público de piezas destacadas (sin datos de costos internos).
- **8.3:** Carruseles Swiper y animaciones suaves con GSAP.
- **8.4:** Cotizador de oro estimado para visitantes.
- **8.5:** Formulario de contacto con protección anti-spam y enlace WhatsApp.
- **8.6:** Optimización para dispositivos móviles y formato WebP.

---

### Fase 9: Endurecimiento y puesta en marcha
- **9.1:** Auditoría de seguridad (permisos, archivos privados, throttling).
- **9.2:** Pruebas de rendimiento en mostrador ($< 1$ segundo por operación).
- **9.3:** Configuración y prueba de restauración de copias de seguridad.
- **9.4:** Importación del inventario inicial desde archivo Excel.
- **9.5:** Pruebas de aceptación con dueño y personal.
- **9.6:** Entrega de manuales de usuario y administrador.
