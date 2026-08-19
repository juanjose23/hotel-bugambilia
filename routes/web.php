<?php

use App\Http\Controllers\Activos\ReporteActivoController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Clientes\CuentaClienteController;
use App\Http\Controllers\Compras\ReporteCompraController;
use App\Http\Controllers\Espacios\EspacioController;
use App\Http\Controllers\Financiero\ReporteFinancieroController;
use App\Http\Controllers\Habitaciones\HabitacionController;
use App\Http\Controllers\Inventario\ReporteInventarioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Publico\AcercaDeController;
use App\Http\Controllers\Publico\ContactoController;
use App\Http\Controllers\Publico\FavoritosController;
use App\Http\Controllers\Publico\PagoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\Reservas\DetalleReservaPortalController;
use App\Http\Controllers\Reservas\MisReservasController;
use App\Http\Controllers\Reservas\ReporteReservaController;
use App\Http\Controllers\Restaurante\ComandaController;
use App\Http\Controllers\Restaurante\RestauranteController;
use App\Http\Controllers\Restaurante\VoucherRestauranteController;
use App\Http\Controllers\Servicios\ServicioController;
use App\Http\Controllers\Servicios\ServicioReportController;
use App\Http\Controllers\WebServices\Reservas\CancelarReservaWebServiceController;
use App\Http\Controllers\WebServices\Stripe\StripeReservaPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas / Sitio Web
|--------------------------------------------------------------------------
*/
Route::get('/', LandingController::class)->name('home');

Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::get('/servicios/{slug}', [ServicioController::class, 'show'])->name('servicio-detalle');

Route::get('/acerca-de', AcercaDeController::class)->name('acerca-de');
Route::get('/contacto', ContactoController::class)->name('contacto');
Route::get('/pago', PagoController::class)->name('pago');
Route::get('/reservas/{reserva}/pago', [PagoController::class, 'reserva'])
    ->name('reservas.pago');
Route::post('/pagos/stripe/reservas/intento', [StripeReservaPaymentController::class, 'crearIntento'])
    ->name('pagos.stripe.reservas.intento');
Route::post('/pagos/stripe/reservas/confirmar', [StripeReservaPaymentController::class, 'confirmarCliente'])
    ->name('pagos.stripe.reservas.confirmar');
Route::post('/stripe/webhook', [StripeReservaPaymentController::class, 'webhook'])
    ->name('stripe.webhook');
Route::get('/favoritos', FavoritosController::class)->name('favoritos');
Route::get('/restaurante', RestauranteController::class)->name('restaurante');
Route::get('/pantalla-turnos', [ComandaController::class, 'pantallaTurnosPublica'])->name('pantalla-turnos');
Route::get('/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones');
Route::get('/habitaciones/{slug}', [HabitacionController::class, 'show'])->name('habitacion-detalle');
Route::get('/habitaciones/{slug}/disponibilidad', [HabitacionController::class, 'disponibilidad'])->name('habitaciones.disponibilidad');
Route::get('/habitaciones/{slug}/dias-agotados', [HabitacionController::class, 'diasAgotados'])->name('habitaciones.dias-agotados');
Route::get('/habitaciones/{slug}/reservar', [HabitacionController::class, 'mostrarReserva'])->name('habitaciones.reservar');
Route::get('/espacios', [EspacioController::class, 'index'])->name('espacios');
Route::get('/espacios/{slug}', [EspacioController::class, 'show'])->name('espacio-detalle');
Route::get('/espacios/{slug}/reservar', [EspacioController::class, 'mostrarReserva'])->name('espacios.reservar');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'iniciarSesion'])->middleware('throttle:auth');
    Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])->middleware('throttle:auth');
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registrar'])->middleware('throttle:auth');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'cerrarSesion'])->middleware(['auth', 'throttle:logout'])->name('logout');
    Route::get('/cambiar-contrasena', [AuthController::class, 'mostrarCambiarContrasena'])->name('cambiar-contrasena');
    Route::post('/cambiar-contrasena', [AuthController::class, 'cambiarContrasena']);
});

/*
|--------------------------------------------------------------------------
| Portal de Huéspedes (Subdominio Dedicado)
|--------------------------------------------------------------------------
*/
$portalDomain = config('app.portal_domain');
if (is_string($portalDomain) && $portalDomain !== '') {
    Route::domain($portalDomain)->group(function (): void {
        Route::get('/', MisReservasController::class)->name('portal.inicio');
        Route::get('/mis-reservas', MisReservasController::class)->name('portal.mis-reservas');
    });
}

Route::get('/portal', MisReservasController::class)->name('portal');
Route::get('/mis-reservas', MisReservasController::class)->name('mis-reservas');
Route::get('/reservas/mis-reservas', MisReservasController::class);
Route::get('/portal/reserva/{id}', [DetalleReservaPortalController::class, 'show'])->name('portal.reserva-detalle');
Route::get('/portal/cuenta', [CuentaClienteController::class, 'show'])->name('portal.cuenta');

/*
|--------------------------------------------------------------------------
| Reservas
|--------------------------------------------------------------------------
*/
Route::post('/reservas', [ReservaController::class, 'crear'])->name('reservas.crear');
Route::post('/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])
    ->middleware('auth')
    ->name('reservas.cancelar');
Route::post('/web-services/reservas/{reserva}/cancelar', CancelarReservaWebServiceController::class)
    ->middleware('auth')
    ->name('web-services.reservas.cancelar');
Route::get('/reservas/{reserva}/voucher', [ReservaController::class, 'voucher'])
    ->name('reservas.voucher');

/*
|--------------------------------------------------------------------------
| Panel de Administración y Reportes (Agrupados por Módulos)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Financiero, Cuentas & Facturación
    Route::prefix('financiero/reportes')->name('financiero.reportes.')->group(function () {
        Route::get('/cuentas-cobrar/pdf', [ReporteFinancieroController::class, 'cuentasCobrarPdf'])->name('cuentas-cobrar.pdf');
        Route::get('/facturacion-ventas/pdf', [ReporteFinancieroController::class, 'facturacionVentasPdf'])->name('facturacion-ventas.pdf');
        Route::get('/resumen-ejecutivo/pdf', [ReporteFinancieroController::class, 'resumenEjecutivoPdf'])->name('resumen-ejecutivo.pdf');
    });

    // Reservas & Ventas
    Route::prefix('reservas/reportes')->name('reservas.reportes.')->group(function () {
        Route::get('/ocupacion/pdf', [ReporteReservaController::class, 'ocupacionPdf'])->name('ocupacion.pdf');
        Route::get('/ventas-ingresos/pdf', [ReporteReservaController::class, 'ventasIngresosPdf'])->name('ventas-ingresos.pdf');
        Route::get('/reservas-estado/pdf', [ReporteReservaController::class, 'reservasEstadoPdf'])->name('reservas-estado.pdf');
        Route::get('/huespedes/pdf', [ReporteReservaController::class, 'huespedesPdf'])->name('huespedes.pdf');
        Route::get('/rendimiento-habitaciones/pdf', [ReporteReservaController::class, 'rendimientoHabitacionesPdf'])->name('rendimiento-habitaciones.pdf');
    });

    // Restaurante
    Route::get('/restaurante/pedidos/{pedido}/comanda', [ComandaController::class, 'imprimir'])
        ->name('restaurante.comanda');

    Route::get('/restaurante/pedidos/{pedido}/voucher', [VoucherRestauranteController::class, 'generar'])
        ->name('restaurante.voucher');

    // Compras
    Route::prefix('compras/reportes')->name('compras.reportes.')->group(function () {
        Route::get('/solicitud/{solicitud}', [ReporteCompraController::class, 'imprimirSolicitud'])->middleware('can:Compras:ImprimirSolicitud')->name('solicitud');
        Route::get('/orden-compra/{orden}', [ReporteCompraController::class, 'imprimirOrdenCompra'])->middleware('can:Compras:ImprimirOrdenCompra')->name('orden-compra');
        Route::get('/recepcion/{recepcion}', [ReporteCompraController::class, 'imprimirRecepcion'])->middleware('can:Compras:ImprimirRecepcion')->name('recepcion');
        Route::get('/cotizacion/{cotizacion}', [ReporteCompraController::class, 'imprimirCotizacion'])->middleware('can:Compras:ImprimirCotizacion')->name('cotizacion');
        Route::get('/comparativa/{solicitud}', [ReporteCompraController::class, 'imprimirComparativa'])->middleware('can:Compras:ImprimirComparativa')->name('comparativa');
        Route::get('/devolucion/{devolucion}', [ReporteCompraController::class, 'devolucion'])->middleware('can:Compras:ImprimirDevolucion')->name('devolucion');

        // Reportes Generales de Compras
        Route::middleware('can:Compras:ImprimirReportesCompras')->group(function () {
            Route::get('/resumen-departamentos', [ReporteCompraController::class, 'imprimirResumenDepartamentos'])->name('resumen-departamentos');
            Route::get('/solicitudes-estado', [ReporteCompraController::class, 'solicitudesEstado'])->name('solicitudes-estado');
            Route::get('/seguimiento-oc', [ReporteCompraController::class, 'seguimientoOc'])->name('seguimiento-oc');
            Route::get('/recepciones-proveedor', [ReporteCompraController::class, 'recepcionesPorProveedor'])->name('recepciones-proveedor');
            Route::get('/analisis-precio', [ReporteCompraController::class, 'analisisPrecio'])->name('analisis-precio');
            Route::get('/valorizacion', [ReporteCompraController::class, 'valorizacion'])->name('valorizacion');
            Route::get('/rotacion', [ReporteCompraController::class, 'rotacion'])->name('rotacion');
            Route::get('/tiempos-entrega', [ReporteCompraController::class, 'tiemposEntrega'])->name('tiempos-entrega');
            Route::get('/ranking-proveedores', [ReporteCompraController::class, 'rankingProveedores'])->name('ranking-proveedores');
            Route::get('/devoluciones', [ReporteCompraController::class, 'devoluciones'])->name('devoluciones');
            Route::get('/trazabilidad-completa/{solicitud}', [ReporteCompraController::class, 'trazabilidadCompleta'])->name('trazabilidad-completa');
        });
    });

    // Inventario
    Route::prefix('inventario/reportes')->name('inventario.reportes.')->group(function () {
        Route::get('/stock-producto/pdf', [ReporteInventarioController::class, 'stockPorProductoPdf'])->middleware('can:Inventario:ReporteStock')->name('stock-producto.pdf');
        Route::get('/stock-producto/excel', [ReporteInventarioController::class, 'stockPorProductoExcel'])->middleware('can:Inventario:ReporteStock')->name('stock-producto.excel');
        Route::get('/movimientos/pdf', [ReporteInventarioController::class, 'movimientosPdf'])->middleware('can:Inventario:ReporteMovimientos')->name('movimientos.pdf');
        Route::get('/movimientos/excel', [ReporteInventarioController::class, 'movimientosExcel'])->middleware('can:Inventario:ReporteMovimientos')->name('movimientos.excel');
        Route::get('/cuarentena/pdf', [ReporteInventarioController::class, 'cuarentenaPdf'])->middleware('can:Inventario:ReporteCuarentena')->name('cuarentena.pdf');
        Route::get('/cuarentena/excel', [ReporteInventarioController::class, 'cuarentenaExcel'])->middleware('can:Inventario:ReporteCuarentena')->name('cuarentena.excel');
        Route::get('/proximos-vencer/pdf', [ReporteInventarioController::class, 'proximosVencerPdf'])->middleware('can:Inventario:ReporteProximosVencer')->name('proximos-vencer.pdf');
        Route::get('/proximos-vencer/excel', [ReporteInventarioController::class, 'proximosVencerExcel'])->middleware('can:Inventario:ReporteProximosVencer')->name('proximos-vencer.excel');
        Route::get('/vencidos/pdf', [ReporteInventarioController::class, 'vencidosPdf'])->middleware('can:Inventario:ReporteVencidos')->name('vencidos.pdf');
        Route::get('/vencidos/excel', [ReporteInventarioController::class, 'vencidosExcel'])->middleware('can:Inventario:ReporteVencidos')->name('vencidos.excel');
        Route::get('/mermas/pdf', [ReporteInventarioController::class, 'mermasPdf'])->middleware('can:Inventario:ReporteMermas')->name('mermas.pdf');
        Route::get('/mermas/excel', [ReporteInventarioController::class, 'mermasExcel'])->middleware('can:Inventario:ReporteMermas')->name('mermas.excel');
        Route::get('/valorizacion/pdf', [ReporteInventarioController::class, 'valorizacionPdf'])->middleware('can:Inventario:ReporteValorizacion')->name('valorizacion.pdf');
        Route::get('/valorizacion/excel', [ReporteInventarioController::class, 'valorizacionExcel'])->middleware('can:Inventario:ReporteValorizacion')->name('valorizacion.excel');
        Route::get('/rotacion/pdf', [ReporteInventarioController::class, 'rotacionPdf'])->middleware('can:Inventario:ReporteRotacion')->name('rotacion.pdf');
        Route::get('/rotacion/excel', [ReporteInventarioController::class, 'rotacionExcel'])->middleware('can:Inventario:ReporteRotacion')->name('rotacion.excel');
        Route::get('/mermas-totales/excel', [ReporteInventarioController::class, 'mermasTotalesExcel'])->middleware('can:Inventario:ReporteMermasTotales')->name('mermas-totales.excel');
        Route::get('/trazabilidad/{loteId}/pdf', [ReporteInventarioController::class, 'trazabilidadLotePdf'])->middleware('can:Inventario:ReporteTrazabilidad')->name('trazabilidad.pdf');
        Route::get('/stock-minimo/pdf', [ReporteInventarioController::class, 'stockMinimoPdf'])->middleware('can:Inventario:ReporteStockMinimo')->name('stock-minimo.pdf');
        Route::get('/stock-minimo/excel', [ReporteInventarioController::class, 'stockMinimoExcel'])->middleware('can:Inventario:ReporteStockMinimo')->name('stock-minimo.excel');
        Route::get('/ajustes/pdf', [ReporteInventarioController::class, 'ajustesPdf'])->middleware('can:Inventario:ReporteAjustes')->name('ajustes.pdf');
        Route::get('/ajustes/excel', [ReporteInventarioController::class, 'ajustesExcel'])->middleware('can:Inventario:ReporteAjustes')->name('ajustes.excel');
        Route::get('/costo-ventas/pdf', [ReporteInventarioController::class, 'costoVentasPdf'])->middleware('can:Inventario:ReporteCostoVentas')->name('costo-ventas.pdf');
        Route::get('/costo-ventas/excel', [ReporteInventarioController::class, 'costoVentasExcel'])->middleware('can:Inventario:ReporteCostoVentas')->name('costo-ventas.excel');
    });

    // Servicios
    Route::prefix('servicios/reportes')->name('servicios.reportes.')->group(function () {
        Route::get('/historico-precios/pdf', [ServicioReportController::class, 'historicoPreciosPdf'])->middleware('can:Servicios:ReporteHistoricoPrecios')->name('historico-precios.pdf');
        Route::get('/historico-precios/excel', [ServicioReportController::class, 'historicoPreciosExcel'])->middleware('can:Servicios:ReporteHistoricoPrecios')->name('historico-precios.excel');
    });

    // Activos Fijos
    Route::prefix('activos/reportes')->name('activos.reportes.')->group(function () {
        Route::get('/inventario-general/pdf', [ReporteActivoController::class, 'inventarioGeneralPdf'])->middleware('can:Activos:ReporteInventario')->name('inventario-general.pdf');
        Route::get('/inventario-general/excel', [ReporteActivoController::class, 'inventarioGeneralExcel'])->middleware('can:Activos:RouteMiddleware')->name('inventario-general.excel');
        Route::get('/ficha/{activo}/pdf', [ReporteActivoController::class, 'fichaActivoPdf'])->middleware('can:Activos:ReporteFicha')->name('ficha.pdf');
        Route::get('/mantenimiento/{mantenimiento}/pdf', [ReporteActivoController::class, 'fichaMantenimientoPdf'])->middleware('can:Activos:ReporteMantenimiento')->name('mantenimiento.pdf');
        Route::get('/etiquetas/pdf', [ReporteActivoController::class, 'etiquetasPdf'])->middleware('can:Activos:ReporteEtiquetas')->name('etiquetas.pdf');
        Route::get('/por-ubicacion/pdf', [ReporteActivoController::class, 'porUbicacionPdf'])->middleware('can:Activos:ReportePorUbicacion')->name('por-ubicacion.pdf');
        Route::get('/historial-movimientos/pdf', [ReporteActivoController::class, 'historialMovimientosPdf'])->middleware('can:Activos:ReporteHistorial')->name('historial-movimientos.pdf');
        Route::get('/en-mantenimiento/pdf', [ReporteActivoController::class, 'enMantenimientoPdf'])->middleware('can:Activos:ReporteMantenimientoActivos')->name('en-mantenimiento.pdf');
        Route::get('/garantias-proximas/pdf', [ReporteActivoController::class, 'garantiasProximasPdf'])->middleware('can:Activos:ReporteGarantias')->name('garantias-proximas.pdf');
        Route::get('/dados-de-baja/pdf', [ReporteActivoController::class, 'dadosDeBajaPdf'])->middleware('can:Activos:ReporteBajas')->name('dados-de-baja.pdf');
        Route::get('/extraviados/pdf', [ReporteActivoController::class, 'extraviadosPdf'])->middleware('can:Activos:ReporteExtraviados')->name('extraviados.pdf');
        Route::get('/sin-asignacion/pdf', [ReporteActivoController::class, 'sinAsignacionPdf'])->middleware('can:Activos:ReporteSinAsignacion')->name('sin-asignacion.pdf');
        Route::get('/mantenimientos-vencidos/pdf', [ReporteActivoController::class, 'mantenimientosVencidosPdf'])->middleware('can:Activos:ReporteMantenimientosVencidos')->name('mantenimientos-vencidos.pdf');
        Route::get('/hoja-habitacion/{tipo}/{id}/pdf', [ReporteActivoController::class, 'hojaHabitacionPdf'])->middleware('can:Activos:ReporteHojaHabitacion')->name('hoja-habitacion.pdf');
    });
});
