# JoyeriaWeb - AI Agents Project Instructions & Documentation Map

> [!IMPORTANT]
> **MANUAL Y DOCUMENTACIÓN DEL PROYECTO PARA AGENTES DE IA:**
> Este proyecto cuenta con 6 documentos normativos estructurados en la carpeta [`docs/`](file:///c:/laragon/www/joyeria-web/docs/README.md).
> Antes de diseñar, proponer o programar cualquier funcionalidad, el agente DEBE consultar el documento correspondiente:
>
> 1. [PRD (Qué hace el producto)](file:///c:/laragon/www/joyeria-web/docs/01-prd.md)
> 2. [TRD (Requisitos técnicos y stack)](file:///c:/laragon/www/joyeria-web/docs/02-trd.md)
> 3. [Flujo de la App (UX, pantallas y botones)](file:///c:/laragon/www/joyeria-web/docs/03-flujo-app.md)
> 4. [Brief de Diseño (UI, colores y tipografía)](file:///c:/laragon/www/joyeria-web/docs/04-brief-diseno.md)
> 5. [Esquema de Base de Datos (Tablas, tipos exactos y reglas)](file:///c:/laragon/www/joyeria-web/docs/05-esquema-bd.md)
> 6. [Plan de Implementación (Fases y pasos)](file:///c:/laragon/www/joyeria-web/docs/06-plan-implementacion.md)

## Reglas Técnicas y Arquitectura Clave
- **Framework:** Laravel 12 con PHP 8.5, MySQL 8 (InnoDB, utf8mb4_unicode_ci).
- **Frontend / Panel:** Blade, Livewire 3, Alpine.js, Bootstrap 5 personalizado con SCSS.
- **Cálculo de metales:** Centralizado estrictamente en `PricingCalculator` (`app/Services/PricingCalculator.php`).
- **Control de existencias:** No se modifican saldos a mano; únicamente mediante `InventoryService` y movimientos inmutables (`inventory_movements`).
- **Precisión numérica:** Usar `DECIMAL` en base de datos y `brick/money` en PHP; **nunca float ni double**.
- **Seguridad e integridad:** `ON DELETE RESTRICT` en registros con transacciones; snapshots inmutables en ventas y compras; fotos de clientes y piezas en disco privado.
- **Convenciones:** Código, métodos, variables y tablas en inglés; interfaz de usuario y mensajes en español.
- **Formateo:** Ejecutar `vendor/bin/pint --format agent` antes de finalizar cambios de código PHP.
