# Manual del Usuario: JoyeriaWeb — Personal de Mostrador

> **Guía operativa para Vendedores y personal de atención al cliente. Versión 1.0.**
> Cubre el uso diario del sistema: ingreso, caja, punto de venta, apartados, compra de metales, clientes e inventario (consulta), incluida la balanza digital.
> Para tareas de administración (usuarios, precios, reportes, respaldos) consulta el [Manual del Administrador](file:///c:/laragon/www/joyeria-web/docs/08-manual-administrador.md).

---

## 1. Antes de empezar

- **Navegador recomendado:** Google Chrome o Microsoft Edge actualizados (necesarios para la balanza digital).
- **Dirección del sistema:** la que te indique tu administrador (por ejemplo, `https://tusucursal.com`). El panel interno vive bajo la ruta `/admin`.
- **Tu acceso:** tu administrador crea tu cuenta con tu correo electrónico y una contraseña temporal. La primera vez, cámbiala por una tuya.
- **Regla de oro:** para vender, apartar o comprar metal **debes tener un turno de caja abierto**. Si la caja está cerrada, el sistema bloquea esas operaciones.

---

## 2. Iniciar y cerrar sesión

### 2.1 Iniciar sesión
1. Abre el navegador en la dirección del sistema y entra a `/login`.
2. Escribe tu **correo** y **contraseña**.
3. Pulsa **Iniciar sesión**.

El sistema te lleva al panel. La **barra superior** muestra siempre:

| Elemento | Significado |
| :--- | :--- |
| **Oro 24k / Plata (USD $ / g)** | Precio vigente del día por gramo de metal puro. Es informativo. |
| **Turno Abierto / Caja Cerrada** | Estado de tu caja. Verde = puedes vender; gris = debes abrir turno. |
| **Tu nombre y rol** | Confirma que entraste con la cuenta correcta. |
| **Salir** | Cierra tu sesión de forma segura. |

### 2.2 Seguridad de la sesión
- **Cierre por inactividad:** si dejas de usar el sistema ~30 minutos, la sesión se cierra sola. Vuelve a iniciar sesión.
- **Intentos fallidos:** tras varios intentos incorrectos, el acceso se bloquea temporalmente por seguridad. Espera un minuto y reintenta.
- **Cierra sesión** con el botón **Salir** al dejar el mostrador. **Nunca compartas** tu contraseña ni tu turno de caja.

---

## 3. Turnos de caja

### 3.1 Abrir turno
1. Menú **Caja** → **Abrir turno**.
2. Escribe el **fondo inicial en efectivo** con el que empiezas (el efectivo físico que hay en la caja).
3. Pulsa **Abrir turno** y confirma.

Reglas:
- Solo puede haber **un turno abierto por usuario**. Si ya tienes uno abierto, el sistema rechaza el nuevo.
- El fondo debe ser mayor o igual a cero; no se aceptan montos vacíos ni negativos.

### 3.2 Ingresos y retiros de efectivo
Durante el turno puedes mover efectivo, siempre con **motivo obligatorio**:

- **Ingreso** (entra dinero a la caja; por ejemplo, fondo adicional o reposición):
  1. Elige **Ingreso**, escribe el importe y el **motivo**.
  2. Confirma.
- **Retiro** (sale dinero de la caja, por ejemplo pago a proveedor o retiro a bóveda):
  1. Elige **Retiro**, escribe el importe y el **motivo**.
  2. Confirma.

Todo movimiento queda registrado con tu usuario y la hora.

### 3.3 Cerrar turno (arqueo)
1. Ve a **Caja** → **Cerrar turno**.
2. Cuenta físicamente el dinero y captura los montos **contados por método de pago** (efectivo y demás métodos).
3. Pulsa **Confirmar cierre**.

El sistema compara lo contado con lo esperado:
- **Si coincide**, el turno se cierra normalmente.
- **Si hay diferencia (faltante o sobrante)**, te pedirá una **nota explicativa obligatoria** antes de cerrar. Escríbela con claridad; quedará registrada.

> [!IMPORTANT]
> No cierres el turno hasta haber contado bien el efectivo. La nota de diferencia queda en la bitácora y la revisa la gerencia.

---

## 4. Punto de venta (POS)

Antes de vender, verifica que la barra superior diga **Turno Abierto**. El POS está en **Punto de Venta**.

### 4.1 Armar el carrito
1. En el buscador, **escanea el código de barras** de la pieza o escribe **SKU, nombre o código**. Pulsa **Buscar**.
2. En los resultados, haz clic para **agregar la pieza al carrito**. Verás su foto, metal, quilataje, peso y precio.
3. Repite para agregar más piezas. Puedes quitar una línea del carrito con su botón de eliminar.

### 4.2 Cliente (opcional)
- Puedes asociar la venta a un **cliente existente** (búscalo por nombre o documento) o registrar uno nuevo.
- Si la venta es de mostrador sin ficha, puedes continuar sin cliente.

### 4.3 Descuento
- Si cuentas con permiso, puedes aplicar un **descuento** indicando **motivo justificado**. Sin permiso, el campo no está disponible.

### 4.4 Cobrar
1. Pulsa **Cobrar**.
2. Define los **métodos de pago**. Se admite **pago mixto** (por ejemplo, parte en efectivo y parte con transferencia).
3. Revisa el total y los montos.
4. Pulsa **Confirmar venta**.

En ese momento, en una sola operación, el sistema:
- Crea la venta y sus ítems (con los datos exactos de cada pieza al momento de vender).
- Registra los pagos.
- **Descuenta el stock** mediante movimientos de inventario.
- Asienta el ingreso en tu caja.

### 4.5 Comprobantes
- **Ticket térmico 80 mm:** para impresión en la ticketera.
- **Factura en PDF:** documento imprimible o descargable.
- Puedes **reimprimir** estos comprobantes desde el detalle de la venta en **Ventas**.

### 4.6 Anular una venta
> Solo si cuentas con permiso de anulación (habitualmente Gerente o Dueño).

En **Ventas**, abre la venta y usa **Anular** indicando el **motivo**. El sistema revierte el dinero en caja y devuelve la pieza al inventario como movimiento de reverso.

---

## 5. Balanza digital

El sistema puede leer el peso directamente de la balanza conectada por cable USB.

### 5.1 Conectar
1. Conecta la balanza por USB.
2. Usa **Chrome o Edge** (otros navegadores no soportan la lectura).
3. En la pantalla donde se captura el peso (POS o Compra de metales), pulsa **Conectar balanza**.
4. Autoriza el puerto cuando el navegador lo solicite.

Con la balanza activa verás el indicador en **verde** y la lectura en vivo.

### 5.2 Si no conecta
- Si no puedes conectar, **captura el peso manualmente**; el sistema lo marca como origen **Manual** (identificado para auditoría).

---

## 6. Apartados

Sirve para reservar una pieza con un abono inicial y pagos parciales posteriores.

### 5.1 Crear un apartado
1. Menú **Apartados** → **Crear apartado** (requiere turno de caja abierto).
2. Selecciona **cliente** (obligatorio) y la **pieza** a apartar.
3. Define el **abono inicial** y la **fecha límite**.
4. Confirma. La pieza pasa al estado **Apartado** (`reserved`) y deja de estar disponible para la venta.

### 6.2 Registrar abonos
1. Abre el apartado en **Apartados**.
2. Pulsa **Abonar** (requiere caja abierta), captura el monto y el método de pago.
3. El abono ingresa a tu caja activa y baja el saldo pendiente.

### 6.3 Entregar la pieza
- Cuando el saldo pendiente llega a **cero**, se habilita **Entregar**. Al pulsarlo, la pieza cambia a **Vendido** (`sold`) y se cierra el apartado.

> [!NOTE]
> Un apartado puede **cancelarse** con motivo. Revisa las condiciones con la gerencia antes de cancelar, porque puede implicar devoluciones.

---

## 7. Compra de metales (recompra a clientes)

Permite comprar oro/plata a un cliente. Se hace en **tres pasos** y **requiere turno de caja abierto**.

### Paso 1 — Cliente
1. Menú **Compra Metales** → **Nueva compra**.
2. Busca o **crea el cliente**. Es obligatorio **capturar la fotografía del documento de identidad**. Sin identificación, no se avanza.

### Paso 2 — Pieza
1. Registra **metal** y **quilataje / ley**.
2. Captura el **peso**: directo de la balanza o **manual justificado**.
3. Completa **peso neto**, **observaciones** y toma la **fotografía obligatoria** de la joya.

### Paso 3 — Valuación y confirmación
1. El sistema calcula el **valor a pagar** con el precio del día y el **descuento configurado**, mostrando el desglose al cliente.
2. Pulsa **Cotizar** para ver el desglose y luego **Registrar compra**.
3. Si el monto **supera tu límite**, la compra queda **Pendiente de aprobación** y la debe autorizar gerencia.
4. Al completarse se genera el **comprobante de compraventa legal** (imprimible, con espacio para firma), se registra la **salida de efectivo** de la caja y la pieza pasa a **inventario de ocasión** o a **fundición**, según se haya elegido.

> [!IMPORTANT]
> El comprobante de compraventa es un documento legal. Imprímelo y recaba la firma del cliente en el momento.

---

## 8. Clientes

En **Clientes** puedes:
- **Registrar** un cliente nuevo (nombre, documento, teléfono, correo, etc.).
- **Buscar** por nombre o documento.
- **Ver la ficha** con su historial comercial (compras y apartados).
- **Editar** sus datos.

Mantener las fichas al día agiliza el POS y las compras de metal (evita capturar de nuevo la identificación).

---

## 9. Inventario (consulta) y etiquetas

Si tu rol es de solo consulta, puedes:
- Ver la **lista de piezas** con filtros y búsqueda, y **exportar** el listado.
- Abrir el **detalle** de una pieza (fotos, metal, quilataje, peso, ubicación, estado, historial de movimientos).
- **Imprimir la etiqueta** con código de barras desde el detalle de la pieza.

> [!NOTE]
> El **stock no se modifica a mano**. Cualquier ajuste, cambio de ubicación o fundición lo realiza personal con permiso de inventario y queda registrado como un movimiento. Si notas un descuadre, avisa a la gerencia.

---

## 10. Conteos de inventario

Si tienes acceso, en **Conteos** puedes **consultar** los conteos físicos realizados. Crear, registrar y aplicar un conteo requiere permiso de inventario y normalmente lo hace la gerencia.

---

## 11. Preguntas frecuentes

**No puedo vender / no aparece el POS.**
Tu turno de caja está cerrado. Abre turno en **Caja** (ver sección 2).

**La balanza no marca.**
Usa Chrome/Edge; si no conecta, captura el peso manualmente (queda marcado como *Manual*).

**Me cerró la sesión.**
Inactividad (~30 min). Vuelve a entrar; tu turno de caja sigue abierto hasta que lo cierres.

**El sistema no encuentra una pieza al escanear.**
Verifica el código o busca por SKU/nombre. Si la pieza acaba de ingresar, pídele a la gerencia confirmar su alta en inventario.

**Aparece "diferencia de caja".**
Cuenta de nuevo. Si la diferencia es real, escribe la nota explicativa; quedará en la bitácora.

---

## 12. Buenas prácticas

- Abre y **cierra tu turno** de caja todos los días.
- Captura el **motivo** en todo ajuste, retiro o descuento: protege tu trabajo y a la tienda.
- Guarda **fotos de documentos** e identificaciones de forma legible en las compras.
- **Cierra sesión** al dejar el mostrador.
- Ante cualquier duda, pregunta antes de confirmar: cobros, anulaciones y compras mueven dinero real.
