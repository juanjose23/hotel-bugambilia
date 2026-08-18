<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CambiarHabitacionData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\HuespedCambiadoDeHabitacion;
use App\Interactors\Reservas\Habitaciones\CambiarHabitacionEstancia;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

function crearEstanciaActiva(): array
{
    $tipo = CatalogoTipo::query()->create(['codigo' => 'HAB-T-'.Str::random(4), 'nombre' => 'Tipo Hab', 'estado' => 1]);
    $categoria = Catalogo::query()->create(['codigo' => 'CAT-HAB-'.Str::random(4), 'nombre' => 'Cat Hab', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ub Hab '.Str::random(4), 'tipo' => 1, 'estado' => 1]);

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab Cambio',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAMBIO-'.Str::random(5),
        'nombre_cliente' => 'Cliente Cambio',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $habitacionAnterior = Habitacion::query()->create([
        'codigo' => 'HAB-OLD-'.Str::random(4),
        'nombre' => 'Habitacion Anterior',
        'numero' => rand(100, 999),
        'slug' => 'hab-old-'.Str::random(4),
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 3,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDays(2),
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacionAnterior->id,
        'check_in_at' => now()->subDay(),
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    return [$estancia, $habitacionAnterior, $recurso, $reserva, $categoria, $ubicacion];
}

test('cambia habitacion de estancia activa', function (): void {
    Event::fake([HuespedCambiadoDeHabitacion::class]);

    [$estancia, $habitacionAnterior, $recursoAnterior, , $categoria, $ubicacion] = crearEstanciaActiva();

    $recursoNuevo = RecursoReservable::query()->create([
        'nombre' => 'Hab Nueva Recurso',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $habitacionNueva = Habitacion::query()->create([
        'codigo' => 'HAB-NEW-'.Str::random(4),
        'nombre' => 'Habitacion Nueva',
        'numero' => rand(100, 999),
        'slug' => 'hab-new-'.Str::random(4),
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'reservable_id' => $recursoNuevo->id,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $resultado = app(CambiarHabitacionEstancia::class)->ejecutar(new CambiarHabitacionData(
        estanciaId: $estancia->id,
        nuevaHabitacionId: $habitacionNueva->id,
        nuevoRecursoReservableId: $recursoNuevo->id,
        motivo: 'Mantenimiento',
    ));

    expect($resultado->habitacion_id)->toBe($habitacionNueva->id);
    expect($habitacionAnterior->fresh()->estado)->toBe(EstadoEspacio::Sucio);
    expect($habitacionNueva->fresh()->estado)->toBe(EstadoEspacio::Ocupado);
    Event::assertDispatched(HuespedCambiadoDeHabitacion::class);
});

test('lanza excepcion al cambiar habitacion de estancia no activa', function (): void {
    $tipo = CatalogoTipo::query()->create(['codigo' => 'HAB-T2-'.Str::random(4), 'nombre' => 'Tipo Inactiva', 'estado' => 1]);
    $categoria = Catalogo::query()->create(['codigo' => 'CAT-INACT-'.Str::random(4), 'nombre' => 'Cat Inactiva', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ub Inact '.Str::random(4), 'tipo' => 1, 'estado' => 1]);

    $recurso1 = RecursoReservable::query()->create([
        'nombre' => 'Hab Inactiva Recurso',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAMBIO-INACT-'.Str::random(4),
        'nombre_cliente' => 'Cliente Inactiva',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDays(5)->toDateString(),
        'fecha_check_out' => now()->subDays(2)->toDateString(),
        'estado' => EstadoReserva::CHECKED_OUT,
    ]);

    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-INACT-'.Str::random(4),
        'nombre' => 'Hab Finalizada',
        'numero' => rand(100, 999),
        'slug' => 'hab-inact-'.Str::random(4),
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'reservable_id' => $recurso1->id,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDays(5),
        'check_out_at' => now()->subDays(2),
        'estado' => EstadoEstancia::FINALIZADA,
    ]);

    $recurso2 = RecursoReservable::query()->create([
        'nombre' => 'Hab Nueva Recurso 2',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $nuevaHabitacion = Habitacion::query()->create([
        'codigo' => 'HAB-NUEVA-'.Str::random(4),
        'nombre' => 'Hab Nueva',
        'numero' => rand(100, 999),
        'slug' => 'hab-nueva-'.Str::random(4),
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'reservable_id' => $recurso2->id,
        'estado' => EstadoEspacio::Disponible,
    ]);

    app(CambiarHabitacionEstancia::class)->ejecutar(new CambiarHabitacionData(
        estanciaId: $estancia->id,
        nuevaHabitacionId: $nuevaHabitacion->id,
        nuevoRecursoReservableId: $recurso2->id,
        motivo: 'Test',
    ));
})->throws(DomainException::class, 'no se encuentra activa');
