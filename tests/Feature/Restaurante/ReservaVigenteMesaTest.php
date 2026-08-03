<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Restaurante\Mesas\ObtenerReservasVigentesMesaQuery;
use Illuminate\Support\Carbon;

test('la mesa solo aparece reservada cerca de su horario y se libera al finalizar', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-VENTANA-1',
        'nombre' => 'Mesa ventana',
        'tipo' => TipoEspacio::MESA,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
    ]);
    $recurso = RecursoReservable::query()->create([
        'tipo' => TipoRecursoReservable::ESPACIO,
        'nombre' => $mesa->nombre,
        'control_disponibilidad' => ControlDisponibilidad::HORARIO,
        'estado' => EstadoRecursoReservable::ACTIVO,
    ]);
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-MESA-VENTANA-1',
        'nombre_cliente' => 'Cliente de mesa',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'espacio_id' => $mesa->id,
        'fecha_check_in' => '2026-08-10',
        'hora_reserva' => '19:00',
        'adultos' => 2,
        'estado' => EstadoReserva::CONFIRMADA,
        'total' => 100,
    ]);
    $reserva->detalles()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoReservaDetalle::CONFIRMADO,
        'fecha_inicio' => '2026-08-10 19:00:00',
        'fecha_fin' => '2026-08-10 21:00:00',
        'cantidad' => 1,
        'precio_unitario' => 100,
        'subtotal' => 100,
    ]);

    $query = app(ObtenerReservasVigentesMesaQuery::class);

    expect($query->paraMesa($mesa->id, Carbon::parse('2026-08-10 18:29')))->toBeNull()
        ->and($query->paraMesa($mesa->id, Carbon::parse('2026-08-10 18:30'))?->is($reserva))->toBeTrue()
        ->and($query->paraMesa($mesa->id, Carbon::parse('2026-08-10 20:00'))?->is($reserva))->toBeTrue()
        ->and($query->paraMesa($mesa->id, Carbon::parse('2026-08-10 21:00')))->toBeNull();
});
