<?php

declare(strict_types=1);

use App\BusinessLogic\CheckIn\ValidarRequisitosCheckIn;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;

test('rechaza el check-in si la reserva no esta confirmada', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CHK-01',
        'nombre_cliente' => 'Cliente Test',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    $validator = new ValidarRequisitosCheckIn;
    $validator->validar($reserva);
})->throws(DomainException::class, 'confirmadas');

test('rechaza el check-in si la reserva no tiene habitacion ni espacio asignado', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CHK-02',
        'nombre_cliente' => 'Cliente Test',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CONFIRMADA,
        'habitacion_id' => null,
        'espacio_id' => null,
    ]);

    $validator = new ValidarRequisitosCheckIn;
    $validator->validar($reserva);
})->throws(DomainException::class, 'habitación o espacio físico');

test('permite el check-in cuando la reserva esta confirmada y tiene habitacion asignada', function (): void {
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'CAT_HABITACIONES'],
        ['nombre' => 'Categorías de Habitación']
    );

    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT-SUITE'],
        ['nombre' => 'Suite', 'catalogo_tipo_id' => $tipo->id, 'estado' => EstadoGeneral::Activo]
    );

    $ubicacion = Ubicacion::query()->first()
        ?? Ubicacion::query()->create([
            'nombre' => 'Piso 1',
            'tipo' => 'piso',
            'estado' => EstadoGeneral::Activo,
        ]);

    /** @var Habitacion $habitacion */
    $habitacion = Habitacion::query()->first() ?? Habitacion::factory()->create([
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'codigo' => 'HAB-101',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CHK-03',
        'nombre_cliente' => 'Cliente Valido',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CONFIRMADA,
        'habitacion_id' => $habitacion->id,
    ]);

    $validator = new ValidarRequisitosCheckIn;
    expect(fn () => $validator->validar($reserva))->not->toThrow(Exception::class);
});
