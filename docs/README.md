# Manual del Proyecto JoyeriaWeb - Índice Central de Documentación

Bienvenido a la documentación oficial y manual técnico y funcional de **JoyeriaWeb**.

Este directorio contiene los **documentos rectores** que definen la totalidad del sistema: negocio, arquitectura, flujo de usuario, identidad visual, base de datos, plan de desarrollo paso a paso, y los manuales operativos y el guion de aceptación de la puesta en marcha.

---

## Mapa de Documentación

| # | Documento | Archivo | Propósito y Consulta |
| :-: | :--- | :--- | :--- |
| **01** | **PRD (Product Requirements Document)** | [`01-prd.md`](file:///c:/laragon/www/joyeria-web/docs/01-prd.md) | **Qué hace el producto:** Objetivos, usuarios (Dueño, Gerente, Vendedor, Cliente), alcance, historias de usuario, reglas de negocio y métricas de éxito. |
| **02** | **TRD (Technical Requirements Document)** | [`02-trd.md`](file:///c:/laragon/www/joyeria-web/docs/02-trd.md) | **Cómo se construye técnicamente:** Stack (Laravel 12, Livewire 3, Bootstrap 5 SCSS, Redis, MySQL 8), librerías autorizadas, arquitectura de servicios, seguridad e integraciones (balanza, precios). |
| **03** | **Flujo de la App** | [`03-flujo-app.md`](file:///c:/laragon/www/joyeria-web/docs/03-flujo-app.md) | **Comportamiento en pantalla:** Recorrido del usuario, mapas de navegación, menú por roles, reglas de interfaz y tabla botón por botón de qué ocurre y cómo reaccionar ante errores. |
| **04** | **Brief de Diseño** | [`04-brief-diseno.md`](file:///c:/laragon/www/joyeria-web/docs/04-brief-diseno.md) | **Identidad visual:** Paleta de colores (Noche, Hueso, Dorado champán, etc.), tipografías (Cormorant Garamond y Jost), componentes, estados y especificaciones para tickets y comprobantes. |
| **05** | **Esquema de Base de Datos** | [`05-esquema-bd.md`](file:///c:/laragon/www/joyeria-web/docs/05-esquema-bd.md) | **Estructura y reglas de datos:** Todas las tablas, columnas, tipos de datos exactos (`DECIMAL`), relaciones, índices, integridad referencial (`ON DELETE RESTRICT`), inmutabilidad y seeders. |
| **06** | **Plan de Implementación** | [`06-plan-implementacion.md`](file:///c:/laragon/www/joyeria-web/docs/06-plan-implementacion.md) | **Hoja de ruta paso a paso:** Fases de desarrollo (Fase 0 a Fase 9), criterios de aceptación de cada fase, estrategia TDD y protocolo de despliegue. |
| **07** | **Manual del Usuario** | [`07-manual-usuario.md`](file:///c:/laragon/www/joyeria-web/docs/07-manual-usuario.md) | **Uso diario (mostrador):** acceso, caja, punto de venta, apartados, compra de metales, clientes, inventario (consulta) y balanza digital. |
| **08** | **Manual del Administrador** | [`08-manual-administrador.md`](file:///c:/laragon/www/joyeria-web/docs/08-manual-administrador.md) | **Administración:** usuarios y permisos, inventario, precio del metal, reportes, auditoría, respaldos, importación inicial, sitio público y seguridad. |
| **09** | **Guion de Aceptación** | [`09-aceptacion.md`](file:///c:/laragon/www/joyeria-web/docs/09-aceptacion.md) | **Puesta en marcha:** guion de pruebas por rol, pruebas clave de negocio y firma de aceptación del personal. |

---

## Jerarquía de Autoridad para Desarrolladores y Agentes de IA

1. **Si dos documentos discrepan:**
   - En aspectos **funcionales y de negocio**, el **PRD** prevalece.
   - En aspectos **técnicos y de implementación**, el **TRD** y el **Esquema de BD** prevalecen.
2. **Reglas de oro de desarrollo:**
   - **Cálculo de metal unificado:** Toda operación matemática de metales pasa exclusivamente por `PricingCalculator` (`app/Services/PricingCalculator.php`).
   - **Sin coma flotante:** Dinero y pesos siempre usan `DECIMAL` en base de datos y `brick/money` en PHP; **nunca `float` ni `double`**.
   - **Inventario por movimientos:** Las existencias nunca se modifican directamente; se crean registros inmutables en `inventory_movements` a través de `InventoryService`.
   - **Transaccionalidad:** Cobros, compras, apartados y turnos de caja se envuelven en una única transacción de base de datos (`DB::transaction`).
   - **Código y traducciones:** Código, clases, métodos y nombres de tablas en **inglés**; textos visibles al usuario en **español** mediante archivos de idioma de Laravel.
