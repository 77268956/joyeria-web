# Brief de Diseño: JoyeriaWeb

> **Paleta, tipografía, componentes y estructura visual de cada pantalla. Versión 1.0.**  
> *Se basa en el [PRD](file:///c:/laragon/www/joyeria-web/docs/01-prd.md), el [TRD](file:///c:/laragon/www/joyeria-web/docs/02-trd.md) y el [Flujo de la App](file:///c:/laragon/www/joyeria-web/docs/03-flujo-app.md).*

---

## 1. Propósito y concepto

Este documento define la identidad visual y los estándares de interfaz de JoyeriaWeb, tanto para el sitio web público como para el panel de administración. Garantiza que todas las pantallas reflejen una misma imagen de marca sofisticada y coherente.

### Concepto: Lujo sobrio
La estética evoca la alta joyería contemporánea: amplios espacios en blanco, fondos neutros y cálidos, tipografía editorial refinada y un único acento dorado usado con estricta moderación. Se evitan colores estridentes, sombras pesadas o adornos superficiales.
- **Tres principios rectores:** Sobrio, luminoso y preciso.

---

## 2. Principios de diseño

1. **La joya es la protagonista:** La interfaz se retira visualmente para que las piezas y su fotografía destaquen.
2. **Menos es más:** Cualquier elemento que no aporte a vender, comprar o tomar decisiones operativas debe eliminarse.
3. **El dorado es acento, no relleno:** Se reserva exclusivamente para detalles finos, íconos destacados y llamadas a la acción clave.
4. **Legibilidad prioritaria:** Cifras clave (peso, precio, total a cobrar) son siempre el punto focal de mayor visibilidad en pantalla.
5. **Ergonomía táctil en mostrador:** En el punto de venta, todos los elementos interactivos tienen tamaño amplio para facilitar el uso en pantallas táctiles o terminales rápidos.

---

## 3. Paleta de colores

| Nombre | Código Hex | Uso principal |
| :--- | :--- | :--- |
| **Noche** | `#12161F` | Color principal oscuro: títulos principales, menú lateral, botones primarios y secciones oscuras del sitio público. |
| **Hueso** | `#F7F4EE` | Fondo general cálido de las pantallas y del sitio público. |
| **Blanco** | `#FFFFFF` | Fondo de tarjetas, tablas, formularios modales y contenedores sobre fondo hueso. |
| **Dorado champán** | `#B89A5E` | Acento refinado: filtros activos, líneas divisorias finas, botones de acción del sitio público e íconos destacados. |
| **Dorado claro** | `#E3D6B4` | Fondos tenues para resaltes, selecciones y efectos hover. |
| **Piedra** | `#6B6B70` | Textos secundarios, etiquetas de ayuda, encabezados de tabla e íconos inactivos. |
| **Línea** | `#E4DFD3` | Bordes suaves de 1 px, separadores y cuadrículas. |
| **Éxito** | `#3E7C59` | Confirmaciones, ventas completadas y estado `available`. |
| **Error** | `#A63D40` | Errores de validación, anulaciones y faltantes de caja. |
| **Aviso** | `#B7791F` | Advertencias operativas y compras `pending_approval`. |
| **Información** | `#3F5F7A` | Mensajes informativos y estado `reserved` (apartados). |

> [!CAUTION]
> El color Dorado champán (`#B89A5E`) nunca debe emplearse como color de texto pequeño sobre fondo claro por falta de contraste. Se reserva para títulos grandes, líneas decorativas, íconos y botones con texto Noche.

---

## 4. Tipografía

- **Títulos:** `Cormorant Garamond` — Serif editorial de trazo fino que transmite distinción y elegancia clásica.
- **Interfaz y lectura:** `Jost` — Sans serif geométrica, limpia y de excelente legibilidad en pantallas digitales.
- **Cifras tabulares:** En tablas de inventario y cifras numéricas de POS se activan fuentes con números monoespaciados (tabulares) para alineación perfecta de columnas.

| Uso | Tipografía | Tamaño | Peso |
| :--- | :--- | :--- | :--- |
| **Título principal del sitio** | Cormorant Garamond | 56 px (escritorio) / 36 px (móvil) | Ligero (300) |
| **Título de sección del sitio** | Cormorant Garamond | 40 px | Regular (400) |
| **Título de pantalla del panel** | Cormorant Garamond | 30 px | Medio (500) |
| **Subtítulos y destacados** | Jost | 20 px | Regular (400) |
| **Texto general** | Jost | 16 px | Regular (400) |
| **Texto de tablas del panel** | Jost | 15 px | Regular (400) |
| **Etiquetas y textos de ayuda** | Jost | 13 px | Medio (500), mayúsculas espaciadas |
| **Cifras del POS (total, peso)** | Jost (números tabulares) | 32 a 48 px | Medio (500) |
| **Botones de acción** | Jost | 15 px | Medio (500) |

---

## 5. Base visual y componentes

- **Escala de espaciado:** Múltiplos de 8 px (`8px`, `16px`, `24px`, `32px`, `48px`, `64px`).
- **Bordes y esquinas:** Radio sutil de 4 px (`border-radius: 4px`), evitando redondeces excesivas. Bordes limpios de 1 px en color Línea (`#E4DFD3`).
- **Sombras:** Prácticamente planas; sombras difusas muy suaves reservadas para menús flotantes y diálogos modales.
- **Íconos:** Juego lineal de trazo fino uniforme en color Noche o Piedra. No mezclar íconos sólidos con lineales.
- **Fotografía de producto:** Fondos neutros (blanco o gris suave), iluminación uniforme sin brillos quemados, encuadre 1:1 para tarjetas de catálogo y 4:5 vertical para vistas de detalle.

### Componentes clave de UI:
- **Botón principal:** Fondo Noche, texto Hueso, altura 44 px (56 px en POS para soporte táctil).
- **Botón de acento (sitio público):** Fondo Dorado champán, texto Noche.
- **Botón peligroso:** Fondo Error (`#A63D40`), texto Blanco, con confirmación modal obligatoria.
- **Campos numéricos:** Texto alineado a la derecha, tipografía tabular y sufijo de unidad visible (`g`, `$`).
- **Insignias de estado (Badges):** Píldoras con fondos pasteles suaves según el color de estado y texto contrastado.
- **SweetAlert2:** Personalizado con tipografía Cormorant Garamond en títulos, botones en color Noche y acentos dorados.

---

## 6. Documentos impresos

1. **Ticket térmico de 80 mm:** Monocromático (blanco y negro estricto), logotipo superior, datos de la tienda, desglose de piezas con peso y quilataje, total en tamaño grande y número de transacción.
2. **Factura y comprobante de compraventa legal (PDF):** Formato carta/A4, fondo blanco, logotipo en color Noche, tipografía corporativa, desglose formal de cálculos y casillas para firma del cliente y responsable de tienda.
3. **Etiqueta adhesiva de joya:** Código de barras legible (Code128), SKU, descripción breve de metal, quilataje y peso en gramos.
