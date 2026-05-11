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

## 2. Exportación de datos

### HTB-CP-004 — Exportación de Productos

| Campo | Valor |
|-------|-------|
| Formato | `.xlsx` (Nativo) |
| Clase | `ExportProductosUseCase` / `ProductosExport` |
| Filtros | Categoría, marca, tipo de producto, estado |
| Contenido | Productos y variantes en formato tabular con encabezados y estilos |

> **Nota:** Se utiliza la librería oficial `Maatwebsite\Excel` para generar archivos de alto rendimiento y compatibilidad total.

---

## 3. Sistema de auditoría

Cada generación de reporte se registra automáticamente en la tabla `auditoria_reportes`.

### Modelo

`App\Models\Audits\AuditoriaReporte`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigIncrements | ID único |
| `usuario_id` | foreignId | Usuario que generó el reporte (nullable) |
| `tipo_reporte` | string | Código del reporte (ej. `HTB-CP-001`) |
| `parametros` | json | Filtros aplicados |
| `ruta_archivo` | string | Ruta al archivo generado (nullable) |
| `conteo_descargas` | integer | Siempre `1` en cada registro |
| `ultima_descarga_en` | timestamp | Marca de tiempo de la generación |

### Use Cases

| Clase | Método | Uso |
|-------|--------|-----|
| `RegistrarAuditoriaReporteUseCase` | `ejecutar(tipo, parametros, rutaArchivo)` | Usado por todas las Actions — hace **INSERT** directo con `DB::table()` |
| `RegistrarReporteUseCase` | `registrar(tipo, parametros, rutaArchivo, usuarioId)` e `incrementarDescarga(id)` | Usa el modelo Eloquent — tiene lógica de incremento de descargas, pero **no está siendo usado actualmente** por las Actions |

---

## 4. Ubicación de clases

| Reporte | Archivo |
|---------|---------|
| HTB-CP-001 / HTB-CP-002 | `app/Actions/Catalogos/GenerarReporteProductosAction.php` |
| HTB-CP-003 | `app/Actions/Catalogos/GenerarEtiquetasCodigosBarrasAction.php` |
| HTB-CP-004 | `app/UseCases/Catalogos/ExportProductosUseCase.php` |
| Auditoría | `app/UseCases/Reportes/RegistrarAuditoriaReporteUseCase.php` |
| Modelo auditoría | `app/Models/Audits/AuditoriaReporte.php` |

---

## 5. Vistas Blade

Los PDF se renderizan desde `resources/views/reportes/`:

- `reportes.productos-variantes` — CP-001 y CP-002
- `reportes.etiquetas-codigos-barras` — CP-003
