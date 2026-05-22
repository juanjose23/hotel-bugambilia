# Módulo de Servicios — Hotel Bugambilias

## Descripción
Módulo para gestionar los servicios adicionales que se ofrecen a los huéspedes (spa, gimnasio, transporte, lavandería, etc.).

## Generación de Código
Los servicios se identifican con un código único auto-generado con formato `SRV-{NNNN}`.

**Clase**: `App\UseCases\Servicios\Mutations\GenerarCodigoServicio`
**Punto de generación**: `ServicioForm::configure()` mediante `default()` callback

```php
// Lógica: Obtiene el último código registrado (incluyendo eliminados), 
// extrae el número, le suma 1 y lo formatea con padding de 4 dígitos.
$ultimo = Servicio::withTrashed()->latest('id')->first();
$numero = $ultimo ? intval(substr($ultimo->codigo, 4)) + 1 : 1;
return 'SRV-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
```

Ejemplos: `SRV-0001`, `SRV-0002`, ..., `SRV-0042`

## Precios por Moneda
Cada servicio puede tener múltiples precios registrados en `servicios_precios`, asociados a diferentes monedas. Un servicio solo puede tener un precio vigente (estado=1) por moneda a la vez.

## Reportes

### HTB-SER-001 — Histórico de Servicios por Precio por Moneda
Reporte de todos los precios históricos de servicios, agrupados por moneda.

| Campo | Valor |
|-------|-------|
| **Controlador** | `ServicioReportController@historicoPreciosPdf` / `historicoPreciosExcel` |
| **Rutas** | `GET /admin/servicios/reportes/historico-precios/pdf` / `excel` |
| **Permiso** | `Servicios:ReporteHistoricoPrecios` |
| **Filtros** | servicio_id, moneda_id, estado (1=Vigente, 2=No Vigente) |
| **Contenido** | Servicio, moneda, precio, vigencia desde/hasta, estado, si es oferta |
| **Formato** | PDF (Spatie) + Excel (Maatwebsite) |
| **Página Filament** | `app/Filament/Pages/Servicios/ReporteHistoricoPrecios.php` |

## Modelos

- `App\Models\Servicios\Servicio` — tabla `servicios`
- `App\Models\Servicios\ServiciosPrecio` — tabla `servicios_precios`
