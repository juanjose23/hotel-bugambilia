<?php

declare(strict_types=1);

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Pages\Tesoreria\CajaChica;
use App\Filament\Pages\Tesoreria\ComprobantesTransferencia;
use App\Filament\Pages\Tesoreria\ConciliacionBancaria;
use App\Filament\Pages\Tesoreria\DevolucionesCaja;
use App\Filament\Pages\Tesoreria\EntregasCaja;
use App\Filament\Pages\Tesoreria\FondosTesoreria;
use App\Filament\Pages\Tesoreria\FondosTurnoCaja;
use App\Filament\Pages\Tesoreria\GestionCajas;
use App\Filament\Pages\Tesoreria\HistorialCajas;
use App\Filament\Pages\Tesoreria\MiTurnoCaja;
use App\Filament\Pages\Tesoreria\MovimientosFondos;
use App\Filament\Resources\Cuentas\CargoFacturacionResource\Pages\CreateCargoFacturacion;
use App\Filament\Resources\Cuentas\CargoFacturacionResource\Pages\EditCargoFacturacion;
use App\Filament\Resources\Cuentas\CargoFacturacionResource\Pages\ListCargosFacturacion;
use App\Filament\Resources\Cuentas\CuentaResource\Pages\ListCuentas;
use App\Filament\Resources\Cuentas\CuentaResource\Pages\ViewCuenta;
use App\Filament\Resources\Cuentas\VentaResource\Pages\CreateVenta;
use App\Filament\Resources\Cuentas\VentaResource\Pages\ListVentas;
use App\Filament\Resources\Cuentas\VentaResource\Pages\ViewVenta;
use App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\Pages\CreateFacturaAutorizacionDgi;
use App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\Pages\EditFacturaAutorizacionDgi;
use App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\Pages\ListFacturaAutorizacionesDgi;
use App\Filament\Resources\Facturacion\FacturaFolioResource\Pages\ListFacturaFolios;
use App\Filament\Resources\Facturacion\FacturaSerieResource\Pages\CreateFacturaSerie;
use App\Filament\Resources\Facturacion\FacturaSerieResource\Pages\EditFacturaSerie;
use App\Filament\Resources\Facturacion\FacturaSerieResource\Pages\ListFacturaSeries;
use App\Filament\Resources\Facturacion\PagoConciliacionResource\Pages\ListPagoConciliaciones;
use App\Filament\Resources\Facturacion\PasarelaPagoResource\Pages\CreatePasarelaPago;
use App\Filament\Resources\Facturacion\PasarelaPagoResource\Pages\EditPasarelaPago;
use App\Filament\Resources\Facturacion\PasarelaPagoResource\Pages\ListPasarelasPago;
use App\Http\Controllers\Admin\RedirigirFacturacionController;
use Illuminate\Support\Facades\Route;

Route::get('cargos-facturacion', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListCargosFacturacion::class)
    ->name('resources.cargos-facturacion.index');

Route::get('cargos-facturacion/create', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', CreateCargoFacturacion::class)
    ->name('resources.cargos-facturacion.create');

Route::get('cargos-facturacion/{record}/edit', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', EditCargoFacturacion::class)
    ->name('resources.cargos-facturacion.edit');

Route::get('cuentas', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListCuentas::class)
    ->name('resources.cuentas.index');

Route::get('cuentas/{record}', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ViewCuenta::class)
    ->name('resources.cuentas.view');

Route::get('facturacion-config', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', FacturacionConfigCluster::class)
    ->name('facturacion-config');

Route::get('facturacion-config/facturacion/autorizaciones-dgi', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListFacturaAutorizacionesDgi::class)
    ->name('facturacion-config.resources.facturacion.autorizaciones-dgi.index');

Route::get('facturacion-config/facturacion/autorizaciones-dgi/create', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', CreateFacturaAutorizacionDgi::class)
    ->name('facturacion-config.resources.facturacion.autorizaciones-dgi.create');

Route::get('facturacion-config/facturacion/autorizaciones-dgi/{record}/edit', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', EditFacturaAutorizacionDgi::class)
    ->name('facturacion-config.resources.facturacion.autorizaciones-dgi.edit');

Route::get('facturacion-config/facturacion/conciliacion', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListPagoConciliaciones::class)
    ->name('facturacion-config.resources.facturacion.conciliacion.index');

Route::get('facturacion-config/facturacion/folios', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListFacturaFolios::class)
    ->name('facturacion-config.resources.facturacion.folios.index');

Route::get('facturacion-config/facturacion/pasarelas', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListPasarelasPago::class)
    ->name('facturacion-config.resources.facturacion.pasarelas.index');

Route::get('facturacion-config/facturacion/pasarelas/create', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', CreatePasarelaPago::class)
    ->name('facturacion-config.resources.facturacion.pasarelas.create');

Route::get('facturacion-config/facturacion/pasarelas/{record}/edit', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', EditPasarelaPago::class)
    ->name('facturacion-config.resources.facturacion.pasarelas.edit');

Route::get('facturacion-config/facturacion/series', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListFacturaSeries::class)
    ->name('facturacion-config.resources.facturacion.series.index');

Route::get('facturacion-config/facturacion/series/create', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', CreateFacturaSerie::class)
    ->name('facturacion-config.resources.facturacion.series.create');

Route::get('facturacion-config/facturacion/series/{record}/edit', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', EditFacturaSerie::class)
    ->name('facturacion-config.resources.facturacion.series.edit');

Route::get('tesoreria/caja-chica', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', CajaChica::class)
    ->name('pages.tesoreria.caja-chica');

Route::get('tesoreria/cajas', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', GestionCajas::class)
    ->name('pages.tesoreria.cajas');

Route::get('tesoreria/cierres', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', HistorialCajas::class)
    ->name('pages.tesoreria.cierres');

Route::get('tesoreria/comprobantes', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ComprobantesTransferencia::class)
    ->name('pages.tesoreria.comprobantes');

Route::get('tesoreria/conciliacion', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ConciliacionBancaria::class)
    ->name('pages.tesoreria.conciliacion');

Route::get('tesoreria/devoluciones', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', DevolucionesCaja::class)
    ->name('pages.tesoreria.devoluciones');

Route::get('tesoreria/entregas', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', EntregasCaja::class)
    ->name('pages.tesoreria.entregas');

Route::get('tesoreria/fondos', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', FondosTesoreria::class)
    ->name('pages.tesoreria.fondos');

Route::get('tesoreria/fondos-turno', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', FondosTurnoCaja::class)
    ->name('pages.tesoreria.fondos-turno');

Route::get('tesoreria/libro-fondos', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', MovimientosFondos::class)
    ->name('pages.tesoreria.libro-fondos');

Route::get('tesoreria/mi-turno', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', MiTurnoCaja::class)
    ->name('pages.tesoreria.mi-turno');

Route::get('ventas', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ListVentas::class)
    ->name('resources.ventas.index');

Route::get('ventas/crear', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', CreateVenta::class)
    ->name('resources.ventas.create');

Route::get('ventas/{record}', RedirigirFacturacionController::class)
    ->defaults('destino_facturacion', ViewVenta::class)
    ->name('resources.ventas.view');
