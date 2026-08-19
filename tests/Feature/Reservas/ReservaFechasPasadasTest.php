<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use Illuminate\Support\Carbon;

test('impide crear reservaciones para fechas pasadas', function (): void {
    $crearReserva = app(CrearReserva::class);
    $tipoCat = CatalogoTipo::query()->create(['codigo' => 'TIPO-CAT-TEST', 'nombre' => 'Tipo Cat Test']);
    $cat = Catalogo::query()->create(['codigo' => 'CAT-P', 'nombre' => 'Cat P', 'catalogo_tipo_id' => $tipoCat->id, 'activo' => true]);
    $ub = Ubicacion::query()->create(['codigo' => 'UB-P', 'nombre' => 'Ubicacion P', 'tipo' => 'area']);
    $hab = Habitacion::query()->create(['codigo' => 'HAB-PASADA', 'nombre' => 'Hab Pasada', 'estado' => 1, 'categoria_id' => $cat->id, 'ubicacion_id' => $ub->id]);

    expect(fn () => $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Cliente Pasado',
        'habitacion_id' => $hab->id,
        'fecha_check_in' => now()->subDay()->toDateString(),
        'fecha_check_out' => now()->toDateString(),
        'adultos' => 1,
    ]))->toThrow(DomainException::class, 'No es posible realizar una reservación para fechas pasadas.');
});

test('impide crear reservaciones para horas pasadas el mismo día', function (): void {
    Carbon::setTestNow(now()->setTime(12, 0, 0));
    $crearReserva = app(CrearReserva::class);
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Mesa Hoy Recurso',
        'tipo' => 2,
        'control_disponibilidad' => 2,
        'estado' => 1,
    ]);

    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-HOY-2',
        'nombre' => 'Mesa Hoy 2',
        'tipo' => TipoEspacio::MESA->value,
        'capacidad_personas' => 4,
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Disponible->value,
    ]);

    $horaPasada = now()->subHour()->format('H:i');

    expect(fn () => $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'nombre_cliente' => 'Cliente Hora Pasada',
        'espacio_id' => $mesa->id,
        'fecha_check_in' => now()->toDateString(),
        'hora_reserva' => $horaPasada,
        'adultos' => 2,
    ]))->toThrow(DomainException::class, 'No es posible realizar una reservación para una hora que ya ha transcurrido hoy.');
});
