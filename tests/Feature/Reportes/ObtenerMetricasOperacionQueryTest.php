<?php

declare(strict_types=1);

namespace Tests\Feature\Reportes;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Queries\Reportes\ObtenerMetricasOperacionQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/**
 * @return array{habitacion: Habitacion, turno: Turno}
 */
function crearHabitacionYTurno(): array
{
    $habitacion = Habitacion::factory()->create(['estado' => EstadoEspacio::Disponible]);

    $turno = Turno::query()->create([
        'nombre' => 'Turno Test '.uniqid(),
        'hora_inicio' => '07:00:00',
        'hora_fin' => '15:00:00',
        'estado' => true,
    ]);

    return ['habitacion' => $habitacion, 'turno' => $turno];
}

it('agrega las ejecuciones de limpieza del período y su tiempo promedio', function (): void {
    $periodo = now()->format('Y-m-d');
    $base = crearHabitacionYTurno();

    $crear = function (string $fecha, EstadoLimpieza $estado, ?string $horaInicio = null, ?string $horaFin = null) use ($base): void {
        LimpiezaEjecucion::query()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $base['habitacion']->id,
            'turno_id' => $base['turno']->id,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'estado' => $estado,
        ]);
    };

    $crear($periodo, EstadoLimpieza::Completada, '08:00:00', '08:45:00');
    $crear($periodo, EstadoLimpieza::Completada, '09:00:00', '09:30:00');
    $crear($periodo, EstadoLimpieza::Pendiente);

    $data = app(ObtenerMetricasOperacionQuery::class)->paraRango($periodo, $periodo);

    expect($data['kpis']['ejecuciones'])->toBe(3)
        ->and($data['kpis']['finalizadas'])->toBe(2)
        ->and($data['kpis']['pendientes'])->toBe(1)
        ->and($data['kpis']['limpiezas_pendientes'])->toBe(1)
        ->and($data['kpis']['tiempo_promedio_minutos'])->toBe(38);
});

it('excluye las ejecuciones fuera del rango de fechas', function (): void {
    $periodo = now()->format('Y-m-d');
    $base = crearHabitacionYTurno();

    LimpiezaEjecucion::query()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $base['habitacion']->id,
        'turno_id' => $base['turno']->id,
        'fecha' => now()->subMonth()->format('Y-m-d'),
        'hora_inicio' => '08:00:00',
        'hora_fin' => '08:45:00',
        'estado' => EstadoLimpieza::Completada,
    ]);

    $data = app(ObtenerMetricasOperacionQuery::class)->paraRango($periodo, $periodo);

    expect($data['kpis']['ejecuciones'])->toBe(0);
});

it('cuenta las habitaciones bloqueadas del período', function (): void {
    Habitacion::factory()->count(3)->create(['estado' => EstadoEspacio::Disponible]);
    Habitacion::factory()->count(2)->create(['estado' => EstadoEspacio::Mantenimiento]);

    $data = app(ObtenerMetricasOperacionQuery::class)->paraRango(now()->format('Y-m-d'), now()->format('Y-m-d'));

    expect($data['kpis']['habitaciones_bloqueadas'])->toBe(2)
        ->and($data['detalle']['bloqueadas'])->toHaveCount(2);
});

it('compara el período actual contra el período anterior equivalente', function (): void {
    $periodo = now()->format('Y-m-d');
    $base = crearHabitacionYTurno();

    LimpiezaEjecucion::query()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $base['habitacion']->id,
        'turno_id' => $base['turno']->id,
        'fecha' => now()->subDays(1)->format('Y-m-d'),
        'hora_inicio' => '08:00:00',
        'hora_fin' => '08:45:00',
        'estado' => EstadoLimpieza::Completada,
    ]);

    LimpiezaEjecucion::query()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $base['habitacion']->id,
        'turno_id' => $base['turno']->id,
        'fecha' => $periodo,
        'hora_inicio' => '08:00:00',
        'hora_fin' => '08:45:00',
        'estado' => EstadoLimpieza::Completada,
    ]);

    $data = app(ObtenerMetricasOperacionQuery::class)->paraRango($periodo, $periodo);

    expect($data['kpis']['ejecuciones'])->toBe(1)
        ->and($data['anterior']['ejecuciones'])->toBe(1);
});
