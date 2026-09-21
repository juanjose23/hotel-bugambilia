<?php

declare(strict_types=1);

use App\Actions\Reservas\GenerarCodigoReserva;
use App\BusinessLogic\Reservas\Builders\ReservaBuilder;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('builder construye atributos de reserva con exito', function (): void {
    $builder = ReservaBuilder::nuevo()
        ->paraTipo(TipoReserva::HABITACION)
        ->conCliente(10, 'Juan Perez', '555-1234', 'juan@example.com')
        ->conFechas(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-03'))
        ->conCapacidad(2, 1)
        ->conPeriodoCalculado(new DateTimeImmutable('2026-10-01 15:00:00'), new DateTimeImmutable('2026-10-03 12:00:00'), 2)
        ->conRecursoPrincipal(entidadId: 5, precioPrincipal: 100.0, habitacionId: 5)
        ->conTotales(200.0, 20.0, 180.0)
        ->conMoneda(1)
        ->conPromocion(3)
        ->conNotas('Piso alto por favor');

    $generarCodigo = app(GenerarCodigoReserva::class);
    $obtenerMoneda = app(ObtenerMonedaPredeterminadaQuery::class);

    $atributos = $builder->construirAtributos($generarCodigo, $obtenerMoneda);

    expect($atributos['codigo_reserva'])->toStartWith('HTB-')
        ->and($atributos['cliente_id'])->toBe(10)
        ->and($atributos['nombre_cliente'])->toBe('Juan Perez')
        ->and($atributos['telefono_cliente'])->toBe('555-1234')
        ->and($atributos['email_cliente'])->toBe('juan@example.com')
        ->and($atributos['tipo_reserva'])->toBe(TipoReserva::HABITACION)
        ->and($atributos['habitacion_id'])->toBe(5)
        ->and($atributos['fecha_check_in'])->toBe('2026-10-01')
        ->and($atributos['fecha_check_out'])->toBe('2026-10-03')
        ->and($atributos['adultos'])->toBe(2)
        ->and($atributos['ninos'])->toBe(1)
        ->and($atributos['subtotal'])->toBe(200.0)
        ->and($atributos['descuento'])->toBe(20.0)
        ->and($atributos['total'])->toBe(180.0)
        ->and($atributos['saldo'])->toBe(180.0)
        ->and($atributos['estado'])->toBe(EstadoReserva::CONFIRMADA)
        ->and($atributos['notas'])->toBe('Piso alto por favor');
});

test('builder utiliza moneda predeterminada cuando no se especifica moneda_id', function (): void {
    $moneda = Moneda::query()->where('es_predeterminada', true)->first();
    if ($moneda === null) {
        $moneda = Moneda::query()->create([
            'codigo' => 'USD',
            'nombre' => 'Dólar Estadounidense',
            'simbolo' => '$',
            'tasa_cambio' => 1.0,
            'es_predeterminada' => true,
        ]);
    }

    $builder = ReservaBuilder::nuevo()
        ->paraTipo(TipoReserva::SERVICIO)
        ->conCliente(null, 'Cliente Mostrador')
        ->conFechas(new DateTimeImmutable('2026-10-05'))
        ->conRecursoPrincipal(entidadId: 8, precioPrincipal: 50.0, servicioId: 8)
        ->conTotales(50.0, 0.0, 50.0);

    $generarCodigo = app(GenerarCodigoReserva::class);
    $obtenerMoneda = app(ObtenerMonedaPredeterminadaQuery::class);

    $atributos = $builder->construirAtributos($generarCodigo, $obtenerMoneda);

    expect($atributos['moneda_id'])->toBe($moneda->id);
});

test('builder lanza excepcion si falta el tipo de reserva', function (): void {
    $builder = ReservaBuilder::nuevo()
        ->conCliente(1, 'Cliente Test')
        ->conFechas(new DateTimeImmutable('2026-10-01'));

    $generarCodigo = app(GenerarCodigoReserva::class);
    $obtenerMoneda = app(ObtenerMonedaPredeterminadaQuery::class);

    $builder->construirAtributos($generarCodigo, $obtenerMoneda);
})->throws(DomainException::class, 'tipo de reserva no definido');

test('builder lanza excepcion si el nombre del cliente esta vacio', function (): void {
    $builder = ReservaBuilder::nuevo()
        ->paraTipo(TipoReserva::HABITACION)
        ->conCliente(null, '   ')
        ->conFechas(new DateTimeImmutable('2026-10-01'));

    $generarCodigo = app(GenerarCodigoReserva::class);
    $obtenerMoneda = app(ObtenerMonedaPredeterminadaQuery::class);

    $builder->construirAtributos($generarCodigo, $obtenerMoneda);
})->throws(InvalidArgumentException::class, 'El nombre del cliente es obligatorio');
