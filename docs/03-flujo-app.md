# Flujo de la App: JoyeriaWeb

> **Recorrido del usuario, navegación y comportamiento de cada botón. Versión 1.0.**  
> *Se apoya en el [PRD](file:///c:/laragon/www/joyeria-web/docs/01-prd.md) (qué se hace) y en el [TRD](file:///c:/laragon/www/joyeria-web/docs/02-trd.md) (cómo se construye).*

---

## 1. Propósito

Este documento describe con exactitud la experiencia interactiva dentro de JoyeriaWeb: por dónde ingresa cada persona, qué pantallas visualiza según su rol asignado, en qué orden se ejecutan las operaciones del día y qué acción precisa ocurre al pulsar cada botón del sistema.

> [!NOTE]
> Cuando una acción o interacción no esté descrita en este documento, se debe consultar con el usuario antes de asumir un comportamiento.

---

## 2. Fases del uso diario

El sistema reproduce el ritmo de una jornada comercial habitual en una joyería:

1. **Fase 1 (Acceso):** La persona inicia sesión y el sistema la redirige al inicio correspondiente según su rol.
2. **Fase 2 (Apertura de caja):** Antes de cobrar o comprar metales, el usuario debe abrir su turno de caja registrando el fondo inicial en efectivo. Sin caja abierta, el POS y la compra de metales quedan deshabilitados.
3. **Fase 3 (Operación diaria):** Se realizan las transacciones del día: venta por POS, apartados, recompra de metales, consultas de stock y atención de clientes.
4. **Fase 4 (Cierre de caja):** Al finalizar la jornada o turno, se realiza el arqueo ingresando los montos contados por método de pago y se asienta el cierre con justificación de diferencias.
5. **Fase 5 (Control y supervisión):** El gerente y el dueño examinan reportes, inventario valorizado, fluctuación del precio del oro y bitácoras de auditoría.

---

## 3. Mapa de navegación

### 3.1 Sitio público (Acceso libre)
- **Inicio:** Portada de marca, pieza destacada a pantalla completa, banner de compra de metales.
- **Catálogo:** Galería de piezas destacadas con filtros por metal y categoría (sin precios de costo ni márgenes).
- **Detalle de pieza:** Galería fotográfica con zoom, especificaciones técnicas y enlace a WhatsApp o visita a tienda.
- **Vender mi oro:** Cotizador estimado interactivo según peso y quilataje del día.
- **Sucursal:** Ubicación en mapa interactivo y horarios de atención.
- **Contacto:** Formulario web protegido y enlace flotante permanente a WhatsApp.

### 3.2 Panel interno (Acceso por rol)

| Opción de Menú | Dueño | Gerente | Vendedor |
| :--- | :--- | :--- | :--- |
| **Inicio (Dashboard)** | Completo (análisis global) | Operativo del día | Resumen de su turno |
| **Caja** | Sí (todos los turnos) | Sí (turnos y arqueos) | Sí (únicamente su propio turno) |
| **Punto de venta (POS)**| Sí | Sí | Sí |
| **Apartados** | Sí | Sí | Sí |
| **Compra de metales** | Sí | Sí (incluye aprobaciones) | Sí (hasta el límite permitido) |
| **Inventario** | Completo (edición/costos) | Completo (edición/ajustes) | Solo consulta de stock disponible |
| **Clientes** | Sí | Sí | Sí |
| **Precio del metal** | Sí (fijación manual y márgenes)| Consulta | Consulta |
| **Reportes** | Sí (exportación y rentabilidad) | Sí | No |
| **Auditoría** | Sí | Consulta | No |
| **Usuarios y permisos** | Sí | No | No |
| **Configuración** | Sí | No | No |

*Barra superior fija:* Muestra siempre el nombre del usuario, su rol, el estado de su caja (abierta en verde / cerrada en gris), el precio spot del gramo de oro 24k del día y el botón de cierre de sesión.

---

## 4. Reglas generales de la interfaz

1. **Confirmación obligatoria:** Toda operación que modifique dinero, inventario o precios requiere confirmación explícita mediante diálogo modal (SweetAlert2).
2. **Prevención de doble click:** Los botones de guardado se bloquean y muestran indicador de carga (`loading`) mientras se procesa la transacción en el servidor.
3. **Notificaciones estándar:**
   - *Éxito:* Notificaciones flotantes tipo toast en la esquina superior derecha que desaparecen automáticamente.
   - *Error:* Permanecen fijas hasta que el usuario las descarte y explican claramente el error y cómo solucionarlo.
4. **Preservación de datos:** Los formularios conservan los datos digitados si ocurre un error de validación.
5. **Formato uniforme:** Fechas, monedas (`$ 0.00`), pesos (`0.000 g`) y porcentajes se muestran con formato homogéneo en todo el sistema.

---

## 5. Flujos y botones por pantalla

### 5.1 Inicio de sesión
- **Recorrido:** Ingresar correo y contraseña $\rightarrow$ Click en **Ingresar**.
- **Comportamiento:** Si las credenciales son válidas, abre sesión. Tras varios intentos fallidos se bloquea temporalmente por `throttle`. Si el usuario no tiene caja abierta y su rol requiere vender, muestra alerta para abrir turno.

### 5.2 Apertura de caja
- **Recorrido:** Menú Caja $\rightarrow$ **Abrir turno** $\rightarrow$ Ingresar efectivo inicial $\rightarrow$ **Confirmar apertura**.
- **Comportamiento:** Crea la sesión con fecha, hora, usuario y saldo inicial. Si el usuario ya tiene un turno abierto, se rechaza la solicitud. Saldo negativo o vacío es rechazado.
- **Ingreso / Retiro manual:** Permite registrar movimientos de caja (`cash_movements`) especificando importe y motivo obligatorio.

### 5.3 Venta directa (Punto de Venta)
- **Recorrido:**
  1. Escanear código de barras o buscar por SKU/nombre.
  2. La pieza se añade al carrito con foto miniatura, metal, quilataje, peso y precio.
  3. Opcional: seleccionar o crear cliente, aplicar descuento con permiso y motivo justificado.
     - **Descuentos $\le 10\%$:** El vendedor los aplica directamente con motivo justificado.
     - **Descuentos $> 10\%$:** Requieren autorización de supervisor (Gerente o Dueño). El sistema despliega un banner de advertencia y un modal híbrido con dos vías de autorización:
       - *Opción A (PIN presencial):* El supervisor teclea su PIN numérico de 4 dígitos directamente en el POS para autorizar al instante sin cambiar de sesión de caja.
       - *Opción B (Solicitud remota):* El vendedor envía la solicitud con motivo. El supervisor recibe una notificación en tiempo real (campana en cabecera) y puede aprobarla o rechazarla con motivo. El POS sondea en segundo plano cada 2.5s y, al ser aprobada, procesa el cobro automáticamente.
  4. Click en **Cobrar**, definir métodos de pago (pago mixto disponible).
  5. Click en **Confirmar venta**: el sistema crea en una sola transacción la venta, los ítems con snapshots, los pagos, descuenta el stock mediante movimientos de inventario y asienta el ingreso en caja.
  6. Imprime ticket térmico de 80 mm o genera factura en PDF.

### 5.4 Apartados (Layaways)
- **Recorrido:** En POS, click en **Apartar** $\rightarrow$ Seleccionar cliente (obligatorio) $\rightarrow$ Definir abono inicial y fecha límite $\rightarrow$ La pieza pasa al estado `reserved`.
- **Abonos:** Desde la vista de apartados se registran abonos sucesivos que ingresan a la caja activa.
- **Liquidación y entrega:** Una vez que el saldo pendiente llega a cero, se habilita el botón **Entregar**, cambiando el estado de la pieza a `sold`.

### 5.5 Compra de metales
- **Recorrido:**
  1. **Paso 1 (Vendedor):** Búsqueda o creación de cliente con documento de identidad y captura obligatoria de fotografía del documento. Sin cliente identificado no se avanza.
  2. **Paso 2 (Pieza):** Registro del metal, quilataje/ley, captura de peso (directo de balanza o manual justificado), peso neto, observaciones y foto obligatoria de la joya.
  3. **Paso 3 (Valuación):** El sistema calcula automáticamente el valor a pagar usando el precio spot del día y el descuento configurado. Se muestra el desglose transparente al cliente.
  4. **Confirmación y Aprobación:** Si el monto supera el límite del vendedor, la compra queda en estado `pending_approval` para autorización de gerencia. Al completarse, se genera el comprobante de compraventa legal imprimible con espacio para firma, se asienta la salida de efectivo en caja y se traslada la pieza a inventario usado o fundición.

### 5.6 Cierre de caja
- **Recorrido:** Menú Caja $\rightarrow$ **Cerrar turno** $\rightarrow$ Contar efectivo y otros métodos $\rightarrow$ Ingresar valores contados $\rightarrow$ **Confirmar cierre**.
- **Comportamiento:** Compara los montos registrados contra el sistema. Si existe diferencia (faltante o sobrante), solicita obligatoriamente una nota explicativa antes de bloquear el turno.

### 5.7 Inventario
- Registro de piezas nuevas con SKU único, peso, categoría, metal, quilates, costo, precio de venta, fotos y ubicación física.
- El saldo no se modifica de forma manual: cualquier ajuste de existencias se realiza con **Ajustar existencias**, especificando motivo y generando un movimiento auditable.

### 5.8 Precio del metal
- Pantalla que expone el precio del gramo de oro 24k y plata pura del día, historial de variaciones y origen (API o manual).
- Botón **Actualizar ahora** (reintento inmediato contra el proveedor de datos).
- Botón **Fijar precio manual** (reservado para Dueño con registro obligatorio de motivo en bitácora).

---

## 6. Estados principales de entidades

- **Piezas / Productos:** `available`, `reserved`, `sold`, `melting`, `withdrawn`.
- **Ventas:** `completed`, `voided`.
- **Compras de metal:** `pending_approval`, `paid`, `rejected`, `adjusted`.
- **Apartados:** `active`, `completed`, `cancelled`.
- **Turnos de caja:** `open`, `closed`.
