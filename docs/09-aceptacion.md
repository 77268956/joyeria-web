# Pruebas de Aceptación: JoyeriaWeb (Fase 9.5)

> **Guion de validación con el Dueño y el personal antes de la puesta en marcha. Versión 1.0.**
> Se ejecuta sobre un entorno de **staging** (nunca sobre producción) con datos de prueba. Al final, cada responsable firma la conformidad.

---

## 1. Objetivo

Confirmar, con el sistema en funcionamiento, que la operación diaria de la joyería (caja, ventas, apartados, compra de metales, inventario, reportes y seguridad) cumple lo esperado **antes** de usarlo con clientes reales.

## 2. Preparación

| Punto | Detalle |
| :--- | :--- |
| **Entorno** | Staging con HTTPS, base de datos de prueba y catálogo cargado. |
| **Navegador** | Chrome o Edge actualizados (para la balanza). |
| **Hardware** | Balanza e impresora de tickets conectadas, si se van a probar. |
| **Datos de prueba** | Inventario de muestra y al menos un cliente de prueba. |
| **Cuentas** | Una por rol (Dueño, Gerente, Vendedor). |
| **Pruebas automáticas** | Deben pasar antes de empezar: `php artisan test --compact` (sin fallos). |

> [!IMPORTANT]
> Al terminar la aceptación, **elimina los datos de prueba** y **cambia las contraseñas** de demostración antes de pasar a producción.

### Cómo leer cada prueba
Cada prueba indica: **Precondición → Pasos → Resultado esperado**. Marca **OK** o **Falla** y anota observaciones.

---

## 3. Pruebas transversales (todas las cuentas)

### T-01 · Inicio y cierre de sesión
- **Precondición:** usuario activo.
- **Pasos:** entrar en `/login` con credenciales correctas; usar el botón **Salir**.
- **Esperado:** el sistema entra al panel y, al salir, vuelve al login. Intentos con contraseña incorrecta se bloquean temporalmente.

### T-02 · Cierre por inactividad
- **Pasos:** iniciar sesión y dejar la pestaña inactiva más de 30 minutos, luego intentar operar.
- **Esperado:** la sesión se cierra y se solicita volver a iniciar sesión.

### T-03 · Menú por rol
- **Pasos:** entrar con cada rol y revisar el menú lateral.
- **Esperado:** Vendedor no ve **Reportes**, **Auditoría**, **Usuarios** ni **Configuración**; Gerente no ve **Usuarios** ni **Configuración**; Dueño ve todo.

### T-04 · Cabeceras de seguridad
- **Pasos:** abrir el sitio y revisar las cabeceras de respuesta (herramientas de desarrollo → Network).
- **Esperado:** se envían `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY` y `Referrer-Policy`; en producción también HSTS/CSP.

---

## 4. Caja (Vendedor)

### C-01 · Abrir turno
- **Precondición:** sin turno abierto.
- **Pasos:** **Caja → Abrir turno**, ingresar fondo inicial y confirmar.
- **Esperado:** el turno queda **abierto** (barra superior en verde) con el saldo inicial. Un segundo intento de apertura es rechazado.

### C-02 · Ingreso y retiro
- **Pasos:** registrar un **Ingreso** y un **Retiro** con motivo.
- **Esperado:** ambos aparecen en los movimientos del turno con usuario y hora.

### C-03 · POS bloqueado sin caja
- **Precondición:** cerrar el turno.
- **Pasos:** intentar entrar a **Punto de Venta** o **Compra de Metales**.
- **Esperado:** el sistema impide operar hasta abrir turno.

### C-04 · Arqueo y cierre
- **Pasos:** contar el efectivo, capturar montos por método de pago y **Confirmar cierre**. Forzar una diferencia a propósito.
- **Esperado:** con diferencia, exige **nota obligatoria** y registra el cierre con la diferencia.

---

## 5. Punto de venta (Vendedor)

### V-01 · Búsqueda y carrito
- **Precondición:** turno abierto e inventario disponible.
- **Pasos:** buscar una pieza por **código de barras** y por **SKU/nombre**; agregarla al carrito.
- **Esperado:** la pieza aparece con foto, metal, quilataje, peso y precio.

### V-02 · Pago mixto y venta
- **Pasos:** **Cobrar**, definir pago **mixto** (efectivo + otro método) y **Confirmar venta**.
- **Esperado:** la venta se crea, la pieza pasa a **Vendido**, se **descuenta el stock** y el ingreso en efectivo entra a la caja.

### V-03 · Comprobantes
- **Pasos:** imprimir **ticket 80 mm** y generar **factura PDF**.
- **Esperado:** ambos se generan y se pueden **reimprimir** desde el detalle de la venta.

### V-03b · Anulación (Gerente)
- **Pasos:** con Gerente, abrir la venta anterior y **Anular** con motivo.
- **Esperado:** la venta queda **Anulada**, se revierte el dinero en caja y la pieza **vuelve a estar disponible** (movimiento de reverso).

---

## 6. Apartados (Vendedor)

### A-01 · Crear apartado
- **Pasos:** **Apartados → Crear**, elegir cliente (obligatorio), pieza, abono inicial y fecha límite.
- **Esperado:** la pieza queda en estado **Apartado** y no puede venderse por POS.

### A-02 · Abonos y entrega
- **Pasos:** registrar abonos hasta saldo **cero**; pulsar **Entregar**.
- **Esperado:** cada abono entra a la caja; al llegar a cero, la pieza pasa a **Vendido** y el apartado se cierra.

---

## 7. Compra de metales (Vendedor → aprobación Gerente)

### M-01 · Flujo completo
- **Precondición:** turno abierto; cliente con documento y fotos.
- **Pasos:** completar los **3 pasos** (cliente + foto de identificación; pieza + peso + foto; valuación) y registrar.
- **Esperado:** se calcula el valor con el **precio del día** y el descuento configurado; se genera el **comprobante legal**, sale el efectivo de caja y la pieza va a inventario de ocasión o fundición.

### M-02 · Aprobación por límite
- **Pasos:** registrar una compra **por encima del límite** del vendedor.
- **Esperado:** queda **Pendiente de aprobación**; el Gerente puede **Aprobar** o **Rechazar**.

### M-03 · Balanza en la compra
- **Pasos:** conectar la balanza y capturar el peso; desconectarla y capturar manual.
- **Esperado:** el peso de balanza se marca como **Balanza** y el manual como **Manual**.

---

## 8. Inventario y conteos (Gerente)

### I-01 · Alta de pieza y etiqueta
- **Pasos:** **Inventario → Crear**, guardar y **imprimir etiqueta**.
- **Esperado:** la pieza aparece con SKU y código de barras, y se crea su **movimiento de entrada**.

### I-02 · Integridad del stock
- **Pasos:** revisar que no exista forma de editar el stock a mano; hacer un **Ajuste** con motivo.
- **Esperado:** el stock solo cambia por **movimientos**; el ajuste queda en el historial.

### I-03 · Conteo físico
- **Pasos:** crear un conteo, registrar diferencias y **Aplicar**.
- **Esperado:** al aplicar, las diferencias se convierten en **ajustes auditados** y el inventario queda conciliado.

### I-04 · Lotes de materia prima
- **Pasos:** crear un lote, ajustar peso y enviar a **fundición**.
- **Esperado:** cada operación genera su movimiento por peso.

---

## 9. Precio del metal (Dueño / Gerente)

### P-01 · Precio del día
- **Pasos:** revisar la barra superior y **Precio del Metal → Actualizar ahora**.
- **Esperado:** el precio por gramo (Oro 24k / Plata) se muestra y se actualiza; queda en el historial.

### P-02 · Ajuste manual auditado
- **Pasos:** fijar un **precio manual** con motivo.
- **Esperado:** el precio cambia y la acción queda registrada en **Auditoría**.

### P-03 · Descuentos de compra (Dueño)
- **Pasos:** ajustar el porcentaje de descuento de compra y el límite de aprobación.
- **Esperado:** el nuevo valor se aplica en las siguientes **compras de metal** (validar con una cotización).

---

## 10. Dashboard, reportes y auditoría (Dueño / Gerente)

### R-01 · Dashboard
- **Pasos:** abrir el Dashboard.
- **Esperado:** muestra ventas y compras del día, flujo de caja, gráfico de 7 días, inventario por metal, alertas de stock bajo y últimas acciones.

### R-02 · Reportes y exportación
- **Pasos:** abrir cada reporte (Ventas, Compras, Inventario, Caja) y exportar a **CSV** y **PDF**.
- **Esperado:** los datos coinciden con lo operado y las exportaciones descargan correctamente.

### R-03 · Auditoría
- **Pasos:** abrir **Auditoría** y localizar una acción sensible reciente (ajuste, anulación, precio manual, cierre).
- **Esperado:** aparece con usuario, acción y fecha.

---

## 11. Administración y mantenimiento (Dueño / TI)

### S-01 · Alta de usuario y permisos
- **Pasos:** crear un usuario Vendedor; iniciar sesión con él y verificar el menú; desactivarlo y volver a intentar entrar.
- **Esperado:** el menú respeta el rol y el usuario inactivo **no puede** iniciar sesión.

### S-02 · Respaldo y restauración
- **Pasos:** `php artisan backup:database`; verificar el archivo; en un entorno de prueba, `php artisan backup:restore ruta`.
- **Esperado:** el respaldo se genera en el disco privado (con retención) y la restauración devuelve los datos.

### S-03 · Importación de inventario inicial
- **Pasos:** preparar un `.xlsx` con el formato acordado y correr `php artisan inventory:import ... --dry-run`; revisar errores; ejecutar sin `--dry-run`.
- **Esperado:** en simulación no guarda nada; al ejecutar, crea las piezas con su **movimiento de entrada**.

### S-04 · Cuadre integral del día (prueba clave)
- **Pasos:** durante medio día real de prueba, ejecutar apertura, ventas (efectivo/mixto), un apartado, una compra de metal y el cierre.
- **Esperado:** el **efectivo esperado = efectivo contado** (diferencia **cero**) y el inventario final coincide con los movimientos.

---

## 12. Sitio público (Dueño / Gerencia)

### W-01 · Catálogo y destacadas
- **Pasos:** marcar una pieza como **pública/destacada** y revisar el sitio público.
- **Esperado:** la pieza aparece en el catálogo/portada; **nunca** se muestran costo ni margen.

### W-02 · Cotizador y contacto
- **Pasos:** usar el cotizador "Vender mi oro" y enviar el formulario de contacto.
- **Esperado:** el cotizador estima según peso/kilataje; el contacto se registra con protección anti-spam.

---

## 13. Registro de incidencias

| # | Prueba | Descripción del problema | Severidad | Responsable | Estado |
| :-: | :--- | :--- | :--- | :--- | :--- |
| 1 | | | | | |
| 2 | | | | | |
| 3 | | | | | |

*Severidad: Bloqueante / Grave / Menor.*

---

## 14. Firma de aceptación

Al completar todas las pruebas clave (C-03, V-01, V-02, V-03b, A-02, M-01, I-02, S-04) sin incidencias **bloqueantes**, se considera la operación **aceptada**.

| Rol | Nombre | Firma | Fecha |
| :--- | :--- | :--- | :--- |
| Dueño | | | |
| Gerente | | | |
| Vendedor | | | |
| Responsable técnico | | | |
