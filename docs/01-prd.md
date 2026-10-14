# PRD: Sistema de Gestión y Venta para Joyería (JoyeriaWeb)

> **Documento de requisitos del producto. Versión 1.0.**  
> *El detalle técnico (librerías, infraestructura) se encuentra en el [TRD](file:///c:/laragon/www/joyeria-web/docs/02-trd.md).*

---

## 1. Resumen del producto

**JoyeriaWeb** es un sistema web para una joyería de una sola sucursal. Permite:
- Vender joyas en mostrador.
- Comprar oro y plata a clientes por peso y pureza.
- Controlar el inventario por metal y quilataje mediante movimientos inmutables.
- Manejar la caja por turnos con arqueo.
- Supervisar el negocio desde un panel administrativo.
- Incluir un sitio público de imagen de marca con catálogo y cotizador estimado para atraer clientes.

El sistema se construye de forma que pueda crecer a varias sucursales en el futuro (`store_id`), pero esa función multi-sucursal no forma parte de esta versión inicial.

---

## 2. Problema y oportunidad

Una joyería maneja productos de alto valor cuyo precio cambia cada día con el mercado del oro y la plata. Cuando el control se lleva en papel o en hojas de cálculo aparecen problemas conocidos:
- Precios mal calculados.
- Diferencias de caja inexplicadas.
- Piezas que no se encuentran en stock.
- Compras de oro sin respaldo legal ni identificación del cliente.
- Poca visibilidad en tiempo real para el dueño.
- Falta de presencia digital profesional que refleje la calidad de las joyas.

El producto resuelve estos puntos mediante un sistema único, simple y seguro para el mostrador, que deja rastro auditable de cada movimiento de dinero y de metal.

---

## 3. Objetivos y métricas de éxito

| Objetivo | Cómo se mide |
| :--- | :--- |
| **Atender más rápido en mostrador** | Una venta con pieza escaneada se cobra en menos de un minuto. |
| **Comprar oro con seguridad y respaldo legal** | El 100 % de las compras tiene vendedor identificado, foto de la pieza y comprobante legal. |
| **Eliminar errores de cálculo del metal** | Cero diferencias entre el monto calculado por el sistema y el pagado, salvo ajustes autorizados. |
| **Cuadrar la caja cada turno** | Diferencia de arqueo registrada y explicada en cada cierre. |
| **Conocer el inventario real** | Conteo físico que coincide con el sistema en al menos 98 % de las piezas. |
| **Dar al dueño visibilidad diaria** | Ventas, compras de metal, caja y gramos en inventario visibles en un solo panel. |
| **Mejorar la imagen de la marca** | Sitio público publicado, con catálogo y contacto, y visitas que llegan a la tienda. |

*Nota: Los valores de la tabla son metas iniciales y se ajustan con el dueño antes de comenzar.*

---

## 4. Usuarios

1. **Dueño o SuperAdministrador:**
   - Propietario del negocio. Requiere visibilidad completa sin estar presente físicamente.
   - Define precios, márgenes, permisos y aprueba operaciones sensibles.
   - Prioriza control, reportes analíticos y seguridad del inventario.
2. **Gerente de tienda:**
   - Administra la operación diaria: apertura y cierre de caja, aprobación de compras de metal de monto elevado y anulaciones de ventas, supervisión de inventario físico.
   - Prioriza que el día cuadre y el equipo opere sin errores.
3. **Vendedor o Cajero:**
   - Atiende clientes en el mostrador.
   - Requiere una pantalla rápida, clara, con botones grandes y difícil de equivocar.
   - Solo accede a lo necesario para vender y comprar metal.
4. **Cliente final:**
   - Visita el sitio público para explorar piezas destacadas, solicitar cotización estimada de su oro o ubicar la tienda.
   - En tienda física, recibe comprobantes impresos/digitales y un cálculo transparente por su metal.

---

## 5. Alcance

### Incluye:
- Venta directa con punto de venta (POS) y soporte para escáner de códigos de barras.
- Compra y recompra de metales con captura de documentos, fotos y cálculo automático por pureza.
- Inventario basado estrictamente en movimientos (`inventory_movements`).
- Gestión de caja por turno (apertura, movimientos de ingreso/retiro, arqueo y cierre).
- Ficha de clientes e historial comercial.
- Precio diario del metal sincronizado automáticamente con APIs y con ajuste manual auditado.
- Dashboard administrativo y reportes exportables a Excel y PDF.
- Roles y permisos granulares con Spatie Permission y bitácora de auditoría con Activitylog.
- Sitio público de alta gama con catálogo de piezas públicas y cotizador estimado de oro.

### No incluye en esta versión:
- Gestión activa de múltiples sucursales en interfaz.
- Tienda en línea con pasarela de pagos por internet (e-commerce checkout).
- Módulo de taller o reparaciones a medida.
- Aplicación móvil nativa.
- Módulo contable completo o nómina de empleados.

---

## 6. Requisitos funcionales

### 6.1 Punto de venta
- **Búsqueda y escaneo:** Búsqueda rápida por escaneo de código de barras, SKU o nombre. Al agregar la pieza al carrito se muestra metal, quilataje, peso y precio.
- **Pago mixto:** Cobro en efectivo, tarjeta, transferencia o combinación mixta, calculando cambio exacto.
- **Comprobantes:** Emisión inmediata de ticket térmico (80 mm) o factura.
- **Anulaciones:** Anulación de venta únicamente con permiso específico (`sales.void`) registrando motivo y revirtiendo existencias y caja.
- **Apartados (Layaways):** Separación de piezas con abono inicial, reserva física y registro de pagos parciales hasta liquidación y entrega.

### 6.2 Compra y recompra de metales
- **Identificación rigurosa:** Obligatorio registrar documento de identidad del vendedor y fotografía del documento y de la joya recibida.
- **Cálculo automático:** Cálculo instantáneo con base en peso neto, metal, quilataje/ley y precio del día.
- **Desglose al cliente:** Pantalla transparente con el desglose exacto antes de confirmar.
- **Destino del material:** Envío a inventario de piezas usadas/ocasión o a materia prima para fundición.
- **Aprobación de montos altos:** Compras superiores al límite parametrizado requieren aprobación de gerencia (`purchases.approve`).
- **Inmutabilidad:** Las compras no se editan; cualquier corrección genera un ajuste auditado (`purchase_adjustments`).

### 6.3 Inventario y catálogo
- Cada pieza posee SKU único, nombre, categoría, metal, quilataje, peso, costo (privado), precio de venta, ubicación física y fotos.
- Distinción entre piezas unitarias y lotes de materia prima controlados por peso (`material_lots`).
- Trazabilidad total: Cada entrada, venta, ajuste o fundición genera un registro inmutable en `inventory_movements`.
- Conteo físico para auditoría periódica de existencias con reporte de sobrantes y faltantes.
- Generación e impresión de etiquetas con código de barras.

### 6.4 Precio del metal
- Consulta automática diaria del precio spot internacional de oro y plata convertido a moneda local.
- Respaldo ante caídas del proveedor: uso del último precio válido con alerta a gerencia.
- Fijación manual permitida para usuarios autorizados (`metal_price.override`), registrando motivo y usuario en bitácora.
- Configuración de porcentajes de descuento de compra por metal y pureza (`purchase_settings`).

### 6.5 Caja
- Bloqueo operativo: no es posible vender ni comprar metales sin un turno de caja abierto por el usuario.
- Arqueo ciego/comparativo al cierre de turno por cada método de pago.
- Registro justificado de retiros e ingresos manuales de efectivo.

### 6.6 Clientes
- Registro de clientes con documento, nombre, teléfono, email, dirección y foto de documento.
- Historial completo de ventas, apartados y compras de metal asociadas.

### 6.7 Dashboard y reportes
- Visualización de métricas del día: ventas, compras de metal, flujo neto de caja, inventario en gramos por metal y cotización del oro.
- Reportes con filtros de fecha exportables a Excel y PDF.
- Reporte comparativo de valor de inventario al costo versus valor de mercado del metal.

### 6.8 Usuarios, permisos y auditoría
- Tres roles base: Dueño (acceso total), Gerente (operativo y autorizaciones) y Vendedor (mostrador).
- Registro en `activity_log` de cambios de precios, anulaciones, ajustes de inventario, cambios manuales de metal y compras aprobadas.
- Cierre automático de sesión por inactividad.

### 6.9 Sitio público
- Catálogo de piezas destacadas (sin revelar costo ni márgenes).
- Cotizador de oro estimado con aviso legal de verificación obligatoria en tienda.
- Ubicación, horarios, formulario de contacto y enlace directo a WhatsApp.
- Diseño sobrio, elegante y optimizado para dispositivos móviles.

---

## 7. Reglas de negocio

1. **Fórmula de compra de oro:**
   $$\text{Monto} = \text{Peso neto (g)} \times \text{Precio gramo 24k} \times \left(\frac{\text{Quilates}}{24}\right) \times (1 - \text{Descuento compra})$$
2. **Fórmula de compra de plata:**
   $$\text{Monto} = \text{Peso neto (g)} \times \text{Precio gramo plata pura} \times \left(\frac{\text{Ley milésimas}}{1000}\right) \times (1 - \text{Descuento compra})$$
   *(Ley 925 = 0.9250; Ley 950 = 0.9500)*.
3. **Descuento de impurezas:** El peso neto descuenta piedras y elementos no preciosos previo al cálculo.
4. **Precios fijos:** Acero y joyería de fantasía se venden a precio fijo; oro y plata combinan valor de metal, mano de obra y margen comercial.
5. **No borrado de transacciones financieras:** Ventas, compras y movimientos nunca se eliminan físicamente; se anulan o ajustan registrando motivo y usuario responsable.

---

## 8. Requisitos no funcionales

- **Rendimiento:** Operaciones de mostrador (cobro, búsqueda, pesaje) deben responder en menos de 1 segundo.
- **Usabilidad:** Curva de aprendizaje mínima para vendedores en mostrador.
- **Seguridad:** Datos sensibles y fotografías de clientes e identificaciones almacenados en disco privado, servidos bajo autorización.
- **Confiabilidad:** Disponibilidad total en horario de atención comercial.
- **Trazabilidad:** Cada centavo y cada miligramo de metal deben poder rastrearse hasta un usuario y comprobante.

---

## 9. Supuestos, dependencias y riesgos

- **Dependencias:** Conexión a internet, servicio de precios de metales (GoldAPI / Metals.dev), balanza digital con puerto serial/USB e impresora térmica de 80 mm.
- **Riesgos y mitigaciones:**
  - *Error en clasificación o peso del metal:* mitigado con balanza conectada vía Web Serial y revisión de gerencia.
  - *Falla de API de precios:* mitigado con fallback al último precio registrado y habilitación de fijación manual auditada.
  - *Pérdida de datos:* mitigado con respaldos diarios automáticos con Spatie Backup cifrados externamente.
