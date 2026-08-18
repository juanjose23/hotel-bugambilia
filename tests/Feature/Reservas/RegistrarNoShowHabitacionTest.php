<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\RegistrarNoShowData;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaHabitacionNoShow;
use App\Interactors\Reservas\Habitaciones\RegistrarNoShowHabitacion;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Event;

test('marca reserva como no show y dispara evento', function (): void {
    Event::fake([ReservaHabitacionNoShow::class]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-NOSHOW-001',
        'nombre_cliente' => 'Cliente NoShow',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->toDateString(),
        'fecha_check_out' => now()->addDay()->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    $data = new RegistrarNoShowData(
        reservaId: $reserva->id,
        motivo: 'Cliente no se presento',
    );

    $resultado = app(RegistrarNoShowHabitacion::class)->ejecutar($data);

    expect($resultado->estado)->toBe(EstadoReserva::NO_SHOW);
    Event::assertDispatched(ReservaHabitacionNoShow::class);
});
