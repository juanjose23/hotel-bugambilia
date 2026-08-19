<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Gestion\CrearReservaPublica;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Event;

function crearMesaRestaurantePublica(): Espacio
{
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Mesa Publica '.Str::random(4),
        'tipo' => 2,
        'control_disponibilidad' => 2,
        'estado' => 1,
    ]);

    return Espacio::query()->create([
        'nombre' => 'Mesa Test',
        'codigo' => 'M-PUB-'.Str::random(4),
        'capacidad' => 4,
        'tipo' => TipoEspacio::MESA,
        'reservable_id' => $recurso->id,
        'tarifa_hora' => 50.00,
        'tarifa_dia' => 200.00,
        'estado' => 1,
    ]);
}

test('crea reserva publica con sin pago y no requiere stripe', function (): void {
    Event::fake();

    $espacio = crearMesaRestaurantePublica();

    $moneda = Moneda::query()->create([
        'codigo' => 'USP',
        'nombre' => 'Dolar Publica',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $datos = [
        'nombre_cliente' => 'Cliente Publico SinPago',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $espacio->id,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'hora_reserva' => '19:00',
        'tipo_pago_reserva' => TipoPagoReserva::SIN_PAGO->value,
        'moneda_id' => $moneda->id,
    ];

    $resultado = app(CrearReservaPublica::class)->ejecutar($datos, [], [], null);

    expect($resultado['reserva'])->toBeInstanceOf(Reserva::class);
    expect($resultado['requiere_pago_stripe'])->toBeFalse();
    expect($resultado['stripe_pago'])->toBeNull();
});

test('normaliza canal de pago transferencia y no requiere stripe', function (): void {
    Event::fake();

    $espacio = crearMesaRestaurantePublica();

    $moneda = Moneda::query()->create([
        'codigo' => 'UST',
        'nombre' => 'Dolar Transfer',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $datos = [
        'nombre_cliente' => 'Cliente Transferencia',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $espacio->id,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'hora_reserva' => '20:00',
        'canal_pago_reserva' => 'transferencia',
        'moneda_id' => $moneda->id,
    ];

    $resultado = app(CrearReservaPublica::class)->ejecutar($datos, [], [], null);

    expect($resultado['reserva'])->toBeInstanceOf(Reserva::class);
    expect($resultado['requiere_pago_stripe'])->toBeFalse();
});
