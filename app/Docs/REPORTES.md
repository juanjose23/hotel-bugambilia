# Guía de Reportes Oficiales — Hotel Bugambilias

Este documento cataloga todos los reportes del sistema. Cada ficha incluye: código, nombre, concepto (qué hace) y filtros disponibles.

---

## Serie HTB-CP — Catálogos y Productos

Motor: **DomPDF** | Vistas: `resources/views/reportes/`

### HTB-CP-001 — Lista General de Productos

| Campo | Valor |
|-------|-------|
| **Concepto** | Lista ejecutiva de productos con tipo (perecedero/no perecedero), imágenes y metadatos básicos. |
| **Clase** | `GenerarReporteProductosAction` (`incluirVariantes: false`) |
| **Filtros** | Categoría, marca, tipo de producto (perecedero/no perecedero), estado (activo/inactivo) |
| **Contenido** | Productos agrupados por categoría, con imagen, tipo y estado |

### HTB-CP-002 — Catálogo Técnico de Variantes

| Campo | Valor |
|-------|-------|
| **Concepto** | Desglose completo de productos con todas sus variantes, especificaciones técnicas y códigos de barras. |
| **Clase** | `GenerarReporteProductosAction` (`incluirVariantes: true`) |
| **Filtros** | Categoría, marca, tipo de producto, estado |
| **Contenido** | Tabla plana con producto ↔ variantes, códigos de barra Code 128 incrustados |

### HTB-CP-003 — Etiquetas de Almacén

| Campo | Valor |
|-------|-------|
| **Concepto** | Impresión de etiquetas 3×N con código de barras para escaneo físico en almacén. |
| **Clase** | `GenerarEtiquetasCodigosBarrasAction` |
| **Filtros** | Producto individual (opcional) — si se omite, imprime etiquetas de todas las variantes |
| **Contenido** | Etiquetas con SKU, nombre de producto, variante y código de barras Code 128 |

### HTB-CP-004 — Exportación de Productos

| Campo | Valor |
|-------|-------|
| **Concepto** | Exportación tabular de productos y variantes a Excel nativo (.xlsx) para análisis externo. |
| **Clase** | `ExportProductosUseCase::exportarCsv()` / `ProductosExport` |
| **Formato** | `.xlsx` (librería `Maatwebsite\Excel`) |
| **Filtros** | Categoría, marca, tipo de producto, estado |
| **Contenido** | Productos y variantes con encabezados estilizados |

---

## Serie HTB-COM — Compras (P2P)

Motor: **Spatie PDF** (Browsershot) | Layout: `layouts.reporte-htb` | Vistas: `resources/views/reports/compras/`
Ruta base: `GET /admin/compras/reportes/{tipo}` | Controlador: `CompraReportController`

> [!NOTE]
> Todos los reportes de esta serie registran auditoría automática en `auditoria_reportes` con el código respectivo.

### HTB-COM-001 — Solicitud de Compra

| Campo | Valor |
|-------|-------|
| **Concepto** | Documento formal de solicitud de compra con detalle de ítems, justificación y firmas de aprobación. |
| **Ruta** | `/admin/compras/reportes/solicitud/{solicitud}` |
| **Permiso** | `ImprimirSolicitud` |
| **Filtros** | Ninguno (descarga directa del registro). |
| **Contenido** | Solicitante, departamento, justificación, tabla de ítems con cantidades solicitadas/aprobadas, espacios para firmas |

### HTB-COM-002 — Cotización de Proveedor

| Campo | Valor |
|-------|-------|
| **Concepto** | Oferta formal del proveedor con precios unitarios, condición de pago y vigencia. |
| **Ruta** | `/admin/compras/reportes/cotizacion/{cotizacion}` |
| **Permiso** | `ImprimirCotizacion` |
| **Filtros** | Ninguno (descarga directa del registro). |
| **Contenido** | Datos del proveedor, condición de pago, vigencia, ítems cotizados con precios y subtotales |

### HTB-COM-003 — Orden de Compra

| Campo | Valor |
|-------|-------|
| **Concepto** | Documento oficial de compromiso de compra con totales, impuestos y términos legales. |
| **Ruta** | `/admin/compras/reportes/orden-compra/{orden}` |
| **Permiso** | `ImprimirOrdenCompra` |
| **Filtros** | Ninguno (descarga directa del registro). |
| **Contenido** | Proveedor, condición de pago, ítems, precios, impuestos (IVA) y total |

### HTB-COM-004 — Recepción de Mercancía

| Campo | Valor |
|-------|-------|
| **Concepto** | Comprobante de ingreso a almacén con control de cantidades recibidas vs. rechazadas. |
| **Ruta** | `/admin/compras/reportes/recepcion/{recepcion}` |
| **Permiso** | `ImprimirRecepcion` |
| **Filtros** | Ninguno (descarga directa del registro). |
| **Contenido** | Orden de compra origen, ítems recibidos, cantidades recibidas/rechazadas, motivo de rechazo |

### HTB-COM-005 — Resumen por Departamentos

| Campo | Valor |
|-------|-------|
| **Concepto** | Reporte ejecutivo con conteo de órdenes de compra y total gastado, agrupado por departamento. |
| **Ruta** | `/admin/compras/reportes/resumen-departamentos` |
| **Permiso** | `ImprimirReportesCompras` |
| **Filtros** | `fecha_inicio` (query, formato `Y-m-d`), `fecha_fin` (query, formato `Y-m-d`). Si se omite: **inicio del mes actual → fecha actual**. |
| **Contenido** | Departamento, conteo de órdenes, total gastado |
| **Modal** | `ListOrdenCompras` → Action "Resumen por Departamento" con DatePickers para `fecha_inicio` y `fecha_fin` |

> **Nota:** Anteriormente el filtro de fechas estaba hardcodeado y no se podía modificar. Ahora el botón en `ListOrdenCompras` abre un modal con selectores de fecha, y envía los parámetros `fecha_inicio` y `fecha_fin` vía query string. El filtro se aplica sobre `oc.fecha_orden` con `whereBetween()`.

---

## 3. Sistema de Auditoría

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

### Uso

```php
$auditoria = new RegistrarAuditoriaReporteUseCase;
$auditoria->ejecutar('HTB-COM-001', ['solicitud_id' => $solicitud->id]);
```

---

## 4. Ubicación de Clases

| Reporte | Archivo |
|---------|---------|
| HTB-CP-001 / HTB-CP-002 | `app/Actions/Catalogos/GenerarReporteProductosAction.php` |
| HTB-CP-003 | `app/Actions/Catalogos/GenerarEtiquetasCodigosBarrasAction.php` |
| HTB-CP-004 | `app/UseCases/Catalogos/ExportProductosUseCase.php` |
| HTB-COM-001 al 005 | `app/Http/Controllers/Compras/CompraReportController.php` |
| Auditoría | `app/UseCases/Reportes/RegistrarAuditoriaReporteUseCase.php` |
| Modelo auditoría | `app/Models/Audits/AuditoriaReporte.php` |

---

## 5. Vistas Blade

| Ruta | Motor | Reportes |
|------|-------|----------|
| `resources/views/reportes/` | DomPDF | HTB-CP-001 al 004 (catálogos) |
| `resources/views/reports/compras/` | Spatie PDF | HTB-COM-001 al 005 (compras) |
| `resources/views/layouts/reporte-htb.blade.php` | Spatie PDF | Layout maestro HTB-COM |

---

## 6. Mapa: Código → Visibilidad

| Código | Nombre | ¿Dónde se genera? |
|--------|--------|-------------------|
| HTB-CP-001 | Lista General de Productos | `ListProductos` → Action PDF "Reporte Simple" |
| HTB-CP-002 | Catálogo Técnico de Variantes | `ListProductos` → Action PDF "Reporte Detallado" |
| HTB-CP-003 | Etiquetas de Almacén | `ListProductos` → Action PDF "Etiquetas" |
| HTB-CP-004 | Exportación de Productos | `ProductoResource` → Action "Excel", `ListProductos` → Action "Excel" |
| HTB-COM-001 | Solicitud de Compra | `ViewSolicitud` / `CompraReportController` |
| HTB-COM-002 | Cotización de Proveedor | `ViewCotizacion` / `ComparativaCotizaciones` / `CompraReportController` |
| HTB-COM-003 | Orden de Compra | `ViewOrdenCompra` / `CompraReportController` |
| HTB-COM-004 | Recepción de Mercancía | `ViewRecepcion` / `CompraReportController` |
| HTB-COM-005 | Resumen por Departamentos | `CompraReportController@imprimirResumenDepartamentos` |

---

## 7. Documentación Relacionada

- [Reportes y Notificaciones de Compras](../../docs/REPORTES_Y_NOTIFICACIONES.md) — detalle de correcciones, middleware, polling
- [Módulo de Compras (P2P)](../../docs/MODULO_COMPRAS.md) — flujo operativo completo

---

*Hotel Bugambilias — Estelí, Nicaragua*
