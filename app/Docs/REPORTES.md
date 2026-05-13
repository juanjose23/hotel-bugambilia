# Guía de Reportes Oficiales — Hotel Bugambilias

Este documento detalla la suite de reportes estandarizados, exportaciones y el sistema de auditoría del proyecto.

---

## 1. Reportes PDF

Todos los reportes PDF usan **DomPDF** y cuentan con encabezado (logo + nombre) y pie de página (fecha, usuario, paginación).

### HTB-CP-001 — Reporte General de Productos

| Campo | Valor |
|-------|-------|
| Tipo | Lista ejecutiva simple |
| Clase | `GenerarReporteProductosAction` (`incluirVariantes: false`) |
| Filtros | Categoría, marca, tipo de producto, estado |
| Contenido | Productos con tipo (percedero/no perecdero), imágenes, metadatos básicos |

### HTB-CP-002 — Catálogo Técnico de Variantes

| Campo | Valor |
|-------|-------|
| Tipo | Reporte detallado |
| Clase | `GenerarReporteProductosAction` (`incluirVariantes: true`) |
| Filtros | Categoría, marca, tipo de producto, estado |
| Contenido | Desglose de variantes, especificaciones técnicas, **códigos de barras** integrados |

### HTB-CP-003 — Etiquetas de Almacén

| Campo | Valor |
|-------|-------|
| Tipo | Impresión de etiquetas 3×N |
| Clase | `GenerarEtiquetasCodigosBarrasAction` |
| Filtros | Producto individual (opcional) |
| Contenido | Etiquetas con SKU y código de barras (Code 128) para escaneo físico |

---

## 2. Reportes de Compras (Serie HTB-COM)

Todos los reportes de compras utilizan el layout maestro `layouts.reporte-htb` y el motor **Spatie PDF** (Browsershot) para máxima fidelidad con Tailwind CSS.

### HTB-COM-001 — Solicitud de Compra
- **Clase:** `CompraReportController@imprimirSolicitud`
- **Contenido:** Detalle de ítems solicitados, justificación y firmas de aprobación.

### HTB-COM-002 — Cotización de Proveedor
- **Clase:** `CompraReportController@imprimirCotizacion`
- **Contenido:** Comparativa de precios, vigencia y selección de ítems ganadores.

### HTB-COM-003 — Orden de Compra
- **Clase:** `CompraReportController@imprimirOrdenCompra`
- **Contenido:** Documento oficial de compromiso, totales con impuestos y términos legales.

### HTB-COM-004 — Recepción de Mercancía
- **Clase:** `CompraReportController@imprimirRecepcion`
- **Contenido:** Control de ingreso, cantidades recibidas vs rechazadas y notas de almacén.

---

## 3. Exportación de datos

### HTB-CP-004 — Exportación de Productos

| Campo | Valor |
|-------|-------|
| Formato | `.xlsx` (Nativo) |
| Clase | `ExportProductosUseCase` / `ProductosExport` |
| Filtros | Categoría, marca, tipo de producto, estado |
| Contenido | Productos y variantes en formato tabular con encabezados y estilos |

> **Nota:** Se utiliza la librería oficial `Maatwebsite\Excel` para generar archivos de alto rendimiento y compatibilidad total.

---

## 4. Sistema de auditoría

Cada generación de reporte se registra automáticamente en la tabla `auditoria_reportes`.

### Modelo

`App\Models\Audits\AuditoriaReporte`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigIncrements | ID único |
| `usuario_id` | foreignId | Usuario que generó el reporte (nullable) |
| `tipo_reporte` | string | Código del reporte (ej. `HTB-COM-001`) |
| `parametros` | json | Filtros aplicados (ID del registro, código, etc.) |
| `ruta_archivo` | string | Ruta al archivo generado (si aplica) |
| `conteo_descargas` | integer | Siempre `1` en cada registro |
| `ultima_descarga_en` | timestamp | Marca de tiempo de la generación |

---

## 5. Ubicación de clases

| Reporte | Archivo |
|---------|---------|
| HTB-CP-001 / HTB-CP-002 | `app/Actions/Catalogos/GenerarReporteProductosAction.php` |
| HTB-CP-003 | `app/Actions/Catalogos/GenerarEtiquetasCodigosBarrasAction.php` |
| HTB-CP-004 | `app/UseCases/Catalogos/ExportProductosUseCase.php` |
| HTB-COM-XXX | `app/Http/Controllers/Compras/CompraReportController.php` |
| Auditoría | `app/UseCases/Reportes/RegistrarAuditoriaReporteUseCase.php` |
| Modelo auditoría | `app/Models/Audits/AuditoriaReporte.php` |

---

## 6. Vistas Blade

Los reportes se encuentran en:

- `resources/views/reportes/` — Catálogos y Etiquetas (DomPDF)
- `resources/views/reports/compras/` — Compras (Serie HTB-COM, Layout Maestro)
- `resources/views/layouts/reporte-htb.blade.php` — Layout Maestro compartido
