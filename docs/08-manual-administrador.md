# Manual del Administrador: JoyeriaWeb — Dueño y Gerencia

> **Guía de administración, operación y mantenimiento del sistema. Versión 1.0.**
> Dirigida al **Dueño** y a la **Gerencia**. Cubre usuarios y permisos, inventario y catálogo, precio del metal, reportes, auditoría, respaldos, importación de inventario inicial, sitio público y seguridad.
> Para el uso diario del mostrador consulta el [Manual del Usuario](file:///c:/laragon/www/joyeria-web/docs/07-manual-usuario.md).

---

## 1. Roles y alcance

El sistema define tres roles:

| Rol | Alcance |
| :--- | :--- |
| **Dueño** | Control total: usuarios, precios, descuentos de compra, reportes, auditoría y configuración. |
| **Gerente** | Operación y supervisión: ajustes de inventario, anulaciones, aprobación de compras, precios y reportes. Sin acceso a usuarios ni configuración. |
| **Vendedor** | Operación de mostrador: POS, apartados, compra de metales, clientes, caja y consulta de inventario. |

### 1.1 Permisos por función

| Función | Dueño | Gerente | Vendedor |
| :--- | :---: | :---: | :---: |
| Vender / anular venta | Sí | Sí | Vender (anular: solo Dueño/Gerente) |
| Apartados | Sí | Sí | Sí |
| Compra de metales | Sí | Sí (incluye aprobación) | Sí (hasta el límite) |
| Inventario (edición / ajustes) | Sí | Sí | Solo consulta |
| Fijar precio del metal | Sí | Sí | No |
| Descuentos de compra | Sí | No | No |
| Caja (abrir / cerrar) | Sí | Sí | Sí |
| Clientes | Sí | Sí | Sí |
| Reportes | Sí | Sí | No |
| Auditoría | Sí | Sí | No |
| Usuarios | Sí | No | No |
| Configuración | Sí | No | No |

---

## 2. Acceso seguro

- El panel interno vive bajo `/admin` y **requiere inicio de sesión**.
- **Cierre por inactividad:** la sesión se cierra automáticamente tras el periodo configurado (por defecto 30 minutos).
- **Límite de intentos:** el acceso se bloquea temporalmente tras varios intentos fallidos de un mismo correo.
- **Solo HTTPS en producción:** el sistema fuerza conexión segura y cookie de sesión `Secure` cuando `APP_ENV=production`.
- **Bitácora de seguridad:** los inicios de sesión (éxito, fallo, cuenta bloqueada o inactiva) se registran en un canal de log aparte (`security`).

> [!IMPORTANT]
> Al poner el sistema en producción: cambia **todas** las contraseñas de demostración, coloca `APP_ENV=production` y `APP_DEBUG=false`, y define `SESSION_SECURE_COOKIE=true`.

---

## 3. Usuarios y permisos

Menú **Usuarios** (solo Dueño).

- **Crear usuario:** correo, nombre, contraseña, **rol** (Dueño, Gerente o Vendedor) y opcionalmente **PIN de Supervisor** (4 dígitos numéricos, obligatorio para roles que autorizan descuentos o compras).
- **Editar:** datos, rol y actualización de PIN de supervisor.
- **PIN de Supervisor:** clave numérica personal e intransferible utilizada para autorizaciones presenciales rápidas en el Punto de Venta sin cerrar la sesión de caja del vendedor.
- **Activar / desactivar:** un usuario inactivo **no puede iniciar sesión** (el intento queda registrado como cuenta inactiva).
- **Eliminar:** retira el acceso del usuario.

Buenas prácticas:
- Una cuenta por persona; nunca compartir cuentas ni el PIN de 4 dígitos.
- Aplica el **principio de menor privilegio**: da rol Vendedor por defecto y escala solo cuando se necesite.
- Revisa periódicamente las cuentas activas y elimina las que ya no correspondan.

---

## 4. Inventario y catálogo

### 4.1 Alta y edición de piezas
En **Inventario** → **Crear pieza** puedes registrar: SKU y código de barras (si se dejan vacíos, el sistema los genera), nombre, descripción, categoría, metal, pureza/ley, peso (g), costo, precio de venta, ubicación física, estado, origen y fotos. La pieza puede marcarse como **destacada** y **pública** para el sitio web.

> El **stock nunca se edita a mano**. Todo ajuste se hace por **Ajustar existencias** con un motivo, generando un **movimiento inmutable** en el historial.

### 4.2 Ajustes de existencias
- **Ajustar existencias:** cambia la cantidad/estado con un **motivo obligatorio**.
- **Fundición (melt):** envía una pieza a fundición, registrando el movimiento correspondiente.
- **Cambio de ubicación:** se realiza mediante movimiento y queda auditado.

### 4.3 Materia prima (Lotes)
En **Materia Prima** se gestionan **lotes de metal** por peso: alta, **ajuste de peso** y **envío a fundición**. El control es siempre **por peso** mediante movimientos.

### 4.4 Etiquetas y exportación
- **Etiqueta:** desde el detalle de cada pieza se imprime su etiqueta con **código de barras**.
- **Exportar inventario:** exporta el listado para Excel/CSV.

### 4.5 Conteo físico de inventario
En **Conteos**:
1. **Crear** un conteo (toma una fotografía del inventario a esa fecha).
2. **Registrar** las cantidades contadas y ver las **diferencias** contra el sistema.
3. **Finalizar** y **Aplicar**: al aplicar, las diferencias se convierten en **ajustes auditados** y el inventario queda conciliado.

---

## 5. Precio del metal y cálculos

Menú **Precio del Metal**.

- La **barra superior** muestra siempre el precio del día por gramo de **Oro 24k** y de **Plata**.
- **Historial** de precios con fecha y origen (automático o manual).
- **Actualizar ahora:** obtiene el precio del proveedor configurado (también corre solo todos los días).
- **Ajuste manual:** fija un precio a mano **con motivo obligatorio**; queda auditado. Requiere permiso (`metal_price.override`).

### Descuentos de compra (solo Dueño)
En **Precio del Metal → Descuentos de compra** se configuran los porcentajes que se aplican al **comprar metal al cliente** y el **límite de aprobación**. Este cálculo es exclusivo del módulo de precios y siempre pasa por el calculador central (`PricingCalculator`), garantizando exactitud al centavo.

> Todos los cálculos monetarios y de metal se realizan con precisión decimal (`DECIMAL` / `brick/money`). El sistema **nunca** usa punto flotante para dinero y pesos.

---

## 6. Caja: supervisión y arqueo

- La Gerencia y el Dueño pueden **consultar todos los turnos** y sus movimientos; el Vendedor ve solo el propio.
- **Apertura / cierre:** ver el [Manual del Usuario](file:///c:/laragon/www/joyeria-web/docs/07-manual-usuario.md) (sección de caja).
- Al **cerrar turno**, el sistema compara lo **contado** por método de pago contra lo **esperado** y registra la **diferencia**. Si hay diferencia, exige una **nota**.
- Buen indicador de salud del turno: **efectivo esperado = efectivo contado** (diferencia cero).

### 6.2 Autorización de descuentos especiales (> 10%)
Los vendedores tienen un límite de descuento autónomo del 10%. Todo descuento superior requiere autorización de un usuario con permiso (`products.edit_price`, reservado a Dueño y Gerente). El sistema ofrece dos modalidades complementarias:

1. **Autorización Presencial (PIN de 4 dígitos):**
   - Cuando el supervisor está presente en el mostrador, puede teclear su PIN numérico en el modal del POS del vendedor.
   - El sistema valida el PIN al instante, registra quién autorizó en las notas de la venta y procesa la transacción inmediatamente. El arqueo de caja y las comisiones permanecen a nombre del vendedor titular.

2. **Autorización Remota (Campana de Notificaciones):**
   - Si el supervisor está en otra oficina, su pantalla monitorea solicitudes en segundo plano cada 5 segundos.
   - Al generarse una solicitud, la **campana de notificaciones** de la barra superior muestra un contador rojo parpadeante con el número de solicitudes pendientes.
   - Al hacer clic en la campana, se despliega una tarjeta con los datos de la venta: sucursal, vendedor, subtotal, porcentaje y monto del descuento, total a pagar y el motivo redactado por el vendedor.
   - El supervisor dispone de dos botones inmediatos:
     - **Aprobar:** Guarda la autorización; el POS del vendedor lo detecta en menos de 2.5 segundos y completa el cobro automáticamente.
     - **Rechazar:** Solicita el motivo del rechazo (ej. *"Margen insuficiente para esa pieza"*) y se lo notifica en pantalla al vendedor para que renegocie.
   - Cada autorización aprobada solo puede utilizarse una vez y expira en 30 minutos si no se concreta la venta.

---

## 7. Dashboard, reportes y auditoría

### 7.1 Dashboard
Resumen operativo con ventas del día, compras de metal del día, flujo de caja, precio del oro, gráfico de ventas de los últimos 7 días, inventario por metal, **alertas de stock bajo** y últimas acciones auditadas.

### 7.2 Reportes (Dueño y Gerente)
Menú **Reportes**:
- **Ventas** — por período, con cliente, vendedor y estado.
- **Compras de Metal** — por período, con gramaje y monto pagado.
- **Inventario** — piezas disponibles valoradas a costo y a precio de venta.
- **Caja** — turnos del período con arqueo esperado, contado y diferencia.

Cada reporte se puede **exportar a CSV** (compatible con Excel, con BOM UTF-8) y **a PDF**.

### 7.3 Auditoría (Dueño y Gerente)
Menú **Auditoría**: bitácora de acciones sensibles (altas, ediciones, ajustes, anulaciones, cambios de precio, cierres, etc.) con **usuario, acción, fecha** y detalles. Úsala para investigar descuadres o reclamos.

---

## 8. Balanza digital

- Se conecta por **Web Serial API**; requiere **Chrome/Edge** y contexto seguro (HTTPS o `localhost`).
- En POS y Compra de Metales aparece el panel de balanza con **indicador de conexión** (verde = conectada).
- Si la balanza no está disponible, el peso se puede capturar **manualmente**; el sistema lo marca con origen **Manual** para su trazabilidad.

---

## 9. Respaldos (copias de seguridad)

El sistema incluye respaldos de base de datos y su restauración.

### 9.1 Generar un respaldo
```
php artisan backup:database
```
- Respalda **MySQL** (con `mysqldump`) o **SQLite** (copia del archivo), según la conexión activa.
- Guarda el archivo en el disco privado (`storage/app/private/backups` por defecto), **nunca accesible por web**.
- Conserva por defecto los últimos **30 días** y borra los más antiguos.

Opciones útiles:
```
php artisan backup:database --keep-days=60
```

### 9.2 Respaldo automático
Está programado a diario a las **02:30** (`routes/console.php`, `withoutOverlapping`). Asegúrate de que el **programador de tareas** de Laravel corra en el servidor (`php artisan schedule:run` cada minuto vía cron). Si el respaldo falla, se registra un error en el log.

### 9.3 Restaurar un respaldo
```
php artisan backup:restore ruta/al/respaldo.sql
```
Pide **confirmación** antes de sobrescribir los datos. Úsalo con mucho cuidado: restaura sobre la base actual.

Configuración en `config/backup.php` (disco, carpeta, retención y binario de `mysqldump`). En Windows/Laragon, si `mysqldump` no está en el PATH, define `MYSQLDUMP_BINARY` con la ruta completa.

> [!IMPORTANT]
> Prueba la **restauración** periódicamente en un entorno de pruebas: un respaldo que nunca se ha restaurado no es un respaldo confiable.

---

## 10. Importación del inventario inicial

Permite cargar el inventario actual desde un archivo **Excel (.xlsx)** o **CSV**.

### 10.1 Comando
```
php artisan inventory:import almacen/inventario.xlsx \
    --user=admin@joyeriaweb.com \
    --store=1 \
    --dry-run
```

| Opción | Descripción |
| :--- | :--- |
| `file` | Ruta del archivo `.xlsx` o `.csv` (relativa a la raíz del proyecto o absoluta). |
| `--user=` | Correo o ID del usuario que registra los movimientos (por defecto, el primer usuario). |
| `--store=` | ID de la tienda destino (por defecto, la del usuario o la primera). |
| `--dry-run` | **Simula** la importación: valida y muestra errores **sin guardar nada**. |

Recomendación: corre primero con `--dry-run`, corrige los errores y luego ejecuta sin `--dry-run`.

### 10.2 Formato del archivo
- **Primera fila = encabezados.** El sistema los reconoce de forma flexible en español o inglés (por ejemplo: `Nombre`/`name`, `Peso`/`weight`, `Precio`/`price`, `Metal`, `Pureza`/`Ley`, `Categoria`, `Ubicacion`, `Codigo de barras`, `Origen`, `Estado`, `Publico`).
- **Columnas requeridas:** **Nombre**, **Peso (g)** mayor que cero, **Precio** y **Categoría**.
- **Metal:** debe existir previamente en el catálogo (Oro, Plata, etc.). Si no existe, la fila se reporta como error.
- **Pureza:** opcional; si se indica, debe existir para ese metal.
- **SKU / Código de barras:** opcionales; si se dejan vacíos se **generan**; si vienen, **no pueden repetirse**.
- **Costo:** opcional (por defecto `0.00`).
- **Ubicación:** opcional; si no existe, se **crea**.
- **Público:** si se deja vacío, la pieza se publica en el sitio web (usa valores como `No`, `0` para no publicarla).

Cada pieza importada se registra mediante el **servicio de inventario**, por lo que se crea su **movimiento de entrada** y el stock queda respaldado por movimientos inmutables.

---

## 11. Sitio público

El sitio de marca es público (sin datos internos como costo o margen).

- **Catálogo público:** muestra las piezas marcadas como **públicas** y, para destacarlas en la portada, como **destacadas**.
- **Cotizador "Vender mi oro":** estima el valor según peso y quilataje con el precio del día.
- **Contacto:** formulario con protección anti-spam y enlace a WhatsApp.
- **Control editorial:** para publicar o retirar una pieza del sitio, edita la pieza en **Inventario** y ajusta **Público**/**Destacada**.

---

## 12. Configuración del sistema

Menú **Configuración** (solo Dueño). Desde aquí se parametrizan (según disponibilidad de cada opción): datos de la tienda, logotipo y colores, **límites de aprobación** y descuentos por rol, textos legales del ticket y del comprobante de compraventa, y el umbral de alerta de stock bajo.

> Algunas secciones de Configuración pueden estar marcadas como *en habilitación progresiva*. Confirma siempre con el equipo técnico antes de cambiar parámetros de cálculo.

---

## 13. Operación y mantenimiento del servidor

Comandos de referencia:

| Comando | Uso |
| :--- | :--- |
| `php artisan backup:database` | Genera respaldo manual. |
| `php artisan backup:restore {archivo}` | Restaura respaldo (con confirmación). |
| `php artisan inventory:import {archivo}` | Importa inventario inicial. |
| `php artisan metal-prices:fetch` | Actualiza precios del metal (corre a diario a las 06:00). |
| `php artisan schedule:work` / cron | Ejecuta tareas programadas (precios y respaldo). |
| `php artisan test --compact` | Ejecuta la batería de pruebas automatizadas. |
| `php artisan optimize:clear` | Limpia cachés de configuración, rutas y vistas. |

Buenas prácticas de puesta en marcha:
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` con **HTTPS**.
- **Programador de tareas** activo para respaldos y precios.
- Respaldos verificados mediante **restauración de prueba**.
- Revisar `composer audit` para vulnerabilidades de dependencias.

---

## 14. Solución de problemas

**Un usuario no puede entrar.**
Verifica que esté **activo** (menú Usuarios). Si se bloqueó por intentos, espera y reintenta; revisa el canal de seguridad.

**El POS o la compra de metales están deshabilitados.**
No hay **turno de caja abierto** para ese usuario. Abre turno en **Caja**.

**El inventario no cuadra.**
Usa **Conteos** para conciliar y **Auditoría** para ver el historial de movimientos. Recuerda: el stock son los **movimientos**, no un número editable.

**El respaldo automático no corre.**
Verifica el programador de tareas de Laravel y el log. En Windows, confirma `MYSQLDUMP_BINARY`.

**La importación reporta errores.**
Corre con `--dry-run`, revisa la tabla de filas con problema (metal inexistente, SKU/código duplicado, peso o precio faltante) y vuelve a ejecutar.

---

## 15. Glosario rápido

| Término | Significado |
| :--- | :--- |
| **Turno / sesión de caja** | Periodo entre apertura y cierre de caja de un usuario. |
| **Movimiento** | Registro inmutable que cambia existencias (entrada, venta, ajuste, fundición). |
| **Apartado (layaway)** | Reserva de una pieza con abonos. |
| **Pureza / ley** | Proporción de metal fino (p. ej. 18k, 925). |
| **Origen** | Nueva o comprada (ocasión). |
| **Conteo** | Toma física de inventario y conciliación de diferencias. |
