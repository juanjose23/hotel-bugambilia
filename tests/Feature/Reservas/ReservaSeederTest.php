<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use Database\Seeders\DatabaseSeeder;

test('reserva seeder siembra ciclo de vida completo con mas de 220 reservas multidominio, clientes corporativos, borradores de facturacion y DGI', function (): void {
    $this->seed(DatabaseSeeder::class);

    // 1. Verificar total de reservas sembradas (al menos 220)
    $reservas = Reserva::query()->get();
    expect($reservas->count())->toBeGreaterThanOrEqual(220);

    // 2. Verificar diversidad de tipos de reserva (multidominio)
    $reservasHabitacion = Reserva::query()->where('tipo_reserva', TipoReserva::HABITACION)->count();
    $reservasRestaurante = Reserva::query()->where('tipo_reserva', TipoReserva::RESTAURANTE)->count();
    $reservasServicio = Reserva::query()->where('tipo_reserva', TipoReserva::SERVICIO)->count();
    $reservasPaquete = Reserva::query()->where('tipo_reserva', TipoReserva::PAQUETE)->count();

    expect($reservasHabitacion)->toBeGreaterThanOrEqual(180)
        ->and($reservasRestaurante)->toBeGreaterThanOrEqual(20)
        ->and($reservasServicio)->toBeGreaterThanOrEqual(5)
        ->and($reservasPaquete)->toBeGreaterThanOrEqual(5);

    // 3. Verificar reservas históricas completadas
    $reservasHistoricas = Reserva::query()->where('estado', EstadoReserva::CHECKED_OUT)->get();
    expect($reservasHistoricas->count())->toBeGreaterThanOrEqual(140);

    // Comprobar que cubren fechas de los últimos 12 meses
    $fechaMinima = (string) $reservasHistoricas->min('fecha_check_in');
    expect($fechaMinima)->not->toBeEmpty()
        ->and(abs((int) now()->diffInDays($fechaMinima)))->toBeGreaterThanOrEqual(300);

    // 4. Verificar huéspedes In-House activos hoy
    $reservasInHouse = Reserva::query()->where('estado', EstadoReserva::CHECKED_IN)->get();
    expect($reservasInHouse->count())->toBeGreaterThanOrEqual(10);

    $estanciasActivas = Estancia::query()->where('estado', EstadoEstancia::ACTIVA)->get();
    expect($estanciasActivas->count())->toBeGreaterThanOrEqual(10);

    $cuentasAbiertas = Cuenta::query()->where('estado', EstadoCuenta::ABIERTA)->get();
    expect($cuentasAbiertas->count())->toBeGreaterThanOrEqual(10);

    // 5. Verificar reservas futuras proyectadas
    $reservasFuturas = Reserva::query()->whereIn('estado', [EstadoReserva::CONFIRMADA, EstadoReserva::PENDIENTE])
        ->where('fecha_check_in', '>', now()->toDateString())
        ->get();
    expect($reservasFuturas->count())->toBeGreaterThanOrEqual(35);

    // 6. Verificar Ventas, Cobros y Facturación Fiscal DGI en todos sus estados
    $ventas = Venta::query()->get();
    $facturasEmitidas = Factura::query()->with(['autorizacionDgi', 'detalles'])->where('estado', EstadoFactura::Emitida)->get();
    $facturasBorrador = Factura::query()->where('estado', EstadoFactura::Borrador)->get();
    $facturasAnuladas = Factura::query()->where('estado', EstadoFactura::Anulada)->get();
    $pagos = PagoCuenta::query()->get();

    expect($ventas->count())->toBeGreaterThanOrEqual(150)
        ->and($facturasEmitidas->count())->toBeGreaterThanOrEqual(140)
        ->and($facturasBorrador->count())->toBeGreaterThanOrEqual(5)
        ->and($facturasAnuladas->count())->toBeGreaterThanOrEqual(5)
        ->and($pagos->count())->toBeGreaterThanOrEqual(200);

    // 7. Verificar cargos a la habitación (room charges de restaurante en folios)
    $cargosRestaurante = CuentaDetalle::query()->where('concepto', 'like', '%Restaurante%')->get();
    expect($cargosRestaurante->count())->toBeGreaterThanOrEqual(20);

    // 8. Verificar clientes corporativos B2B y turistas
    $empresasB2B = Cliente::query()->whereHas('persona', fn ($q) => $q->where('tipo_persona', 'juridica'))->count();
    $turistas = Persona::query()->whereHas('personaNatural', fn ($q) => $q->where('tipo_identificacion', 'pasaporte'))->count();

    expect($empresasB2B)->toBeGreaterThanOrEqual(10)
        ->and($turistas)->toBeGreaterThanOrEqual(3);

    // 9. Verificar estructura de una factura DGI emitida
    $facturaEjemplo = $facturasEmitidas->first();
    expect($facturaEjemplo)->not->toBeNull()
        ->and($facturaEjemplo?->numero)->not->toBeEmpty()
        ->and($facturaEjemplo?->autorizacionDgi?->numero_autorizacion)->not->toBeEmpty()
        ->and((float) $facturaEjemplo?->total)->toBeGreaterThan(0)
        ->and((float) $facturaEjemplo?->iva_total)->toBeGreaterThan(0)
        ->and($facturaEjemplo?->detalles->count())->toBeGreaterThanOrEqual(1);
});
