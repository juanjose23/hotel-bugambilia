<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Operaciones\UnirMesasReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;

function crearReservaRestaurante(): array
{
    $recursoPrincipal = RecursoReservable::query()->create([
        'nombre' => 'Mesa Principal',
        'tipo' => 2,
        'control_disponibilidad' => 2,
        'estado' => 1,
    ]);

    $espacioPrincipal = Espacio::query()->create([
        'nombre' => 'Mesa Principal',
        'codigo' => 'MP-'.Str::random(4),
        'capacidad' => 4,
        'tipo' => TipoEspacio::MESA,
        'reservable_id' => $recursoPrincipal->id,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-MESAS-'.Str::random(5),
        'nombre_cliente' => 'Cliente Mesas',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'hora_reserva' => '19:00',
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recursoPrincipal->id,
        'estado' => 2,
        'fecha_inicio' => now()->addDay()->setTime(19, 0),
        'fecha_fin' => now()->addDay()->setTime(21, 0),
    ]);

    return [$reserva, $espacioPrincipal];
}

test('une mesas secundarias a la reserva', function (): void {
    [$reserva, $espacioPrincipal] = crearReservaRestaurante();

    $recursoSecundario = RecursoReservable::query()->create([
        'nombre' => 'Mesa Secundaria',
        'tipo' => 2,
        'control_disponibilidad' => 2,
        'estado' => 1,
    ]);

    $espacioSecundario = Espacio::query()->create([
        'nombre' => 'Mesa Secundaria',
        'codigo' => 'MS-'.Str::random(4),
        'capacidad' => 4,
        'tipo' => TipoEspacio::MESA,
        'reservable_id' => $recursoSecundario->id,
        'estado' => 1,
    ]);

    $resultado = app(UnirMesasReserva::class)->ejecutar($reserva, $espacioPrincipal->id, [$espacioSecundario->id]);

    expect($resultado->detalles()->count())->toBeGreaterThanOrEqual(2);
});
