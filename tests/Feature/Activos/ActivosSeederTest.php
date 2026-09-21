<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Interactors\Activos\Mantenimiento\DetectarMantenimientosPreventivos;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Activos\ActPlanMantenimiento;
use App\Repository\Models\Activos\PrefijoCodigo;
use Database\Seeders\DatabaseSeeder;

test('activos seeder siembra prefijos dinamicos activos flujo de compras y planes de mantenimiento', function (): void {
    $this->seed(DatabaseSeeder::class);

    // 1. Verificar prefijos de inventario generados por la logica del GeneradorPrefijo
    $prefijos = PrefijoCodigo::query()->get();
    expect($prefijos->count())->toBeGreaterThanOrEqual(10)
        ->and($prefijos->pluck('prefijo')->contains('TV'))->toBeTrue()
        ->and($prefijos->pluck('prefijo')->contains('AC'))->toBeTrue()
        ->and($prefijos->pluck('prefijo')->contains('CAM'))->toBeTrue();

    // 2. Verificar que los planes de mantenimiento fueron creados
    $planes = ActPlanMantenimiento::query()->get();
    expect($planes->count())->toBeGreaterThanOrEqual(4);

    $planClima = ActPlanMantenimiento::query()->with(['activos', 'mantenimientos'])->where('nombre', 'like', '%Aire Acondicionado%')->first();
    expect($planClima)->not->toBeNull()
        ->and($planClima?->tipo)->toBe(TipoMantenimiento::Preventivo)
        ->and($planClima?->estado)->toBe(EstadoPlanMantenimiento::Activo)
        ->and($planClima?->frecuencia_dias)->toBe(90)
        ->and($planClima?->activos->count())->toBeGreaterThanOrEqual(1)
        ->and($planClima?->mantenimientos->count())->toBeGreaterThanOrEqual(1);

    // 3. Verificar que existen tickets de mantenimiento asociados (completados y programados)
    $ticketsCompletados = ActivoMantenimiento::query()->where('estado', EstadoMantenimiento::Completado)->get();
    $ticketsProgramados = ActivoMantenimiento::query()->where('estado', EstadoMantenimiento::Programado)->get();

    expect($ticketsCompletados->count())->toBeGreaterThanOrEqual(2)
        ->and($ticketsProgramados->count())->toBeGreaterThanOrEqual(1);

    // 4. Probar ejecucion del interactor DetectarMantenimientosPreventivos
    $creados = app(DetectarMantenimientosPreventivos::class)->ejecutar();
    expect($creados)->toBeGreaterThanOrEqual(0);
});
