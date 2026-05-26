<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\Activo;
use App\Models\Activos\ActPlanMantenimiento;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\User;
use App\UseCases\Activos\Mutations\DetectarMantenimientosPreventivos;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $tipo = CatalogoTipo::factory()->create();
    $this->categoria = Catalogo::factory()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => 'Tecnología',
        'estado' => 1,
    ]);
});

it('crea un mantenimiento preventivo programado cuando el plan vence', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor 50 pulgadas',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $activo = Activo::create([
        'codigo_inventario' => 'ACT-2026-0001',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subDays(30)->toDateString(),
        'estado' => EstadoActivo::Activo,
    ]);

    $plan = ActPlanMantenimiento::create([
        'nombre' => 'Plan de TV',
        'tipo' => 'preventivo',
        'frecuencia_dias' => 30,
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'estado' => 1,
    ]);

    $plan->mantenimientos()->create([
        'activo_id' => $activo->id,
        'fecha_programada' => now()->subDays(30)->toDateString(),
        'fecha_realizada' => now()->subDays(30)->toDateString(),
        'estado' => EstadoMantenimiento::Completado,
        'notas' => 'Mantenimiento inicial completado',
    ]);

    $creados = app(DetectarMantenimientosPreventivos::class)->execute();

    expect($creados)->toBe(1);

    $this->assertDatabaseHas('inv_mantenimientos', [
        'activo_id' => $activo->id,
        'estado' => EstadoMantenimiento::Programado->value,
    ]);
});

it('no duplica mantenimientos preventivos si ya existe uno abierto', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Aire acondicionado',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $activo = Activo::create([
        'codigo_inventario' => 'ACT-2026-0002',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subDays(30)->toDateString(),
        'estado' => EstadoActivo::Activo,
    ]);

    $plan = ActPlanMantenimiento::create([
        'nombre' => 'Plan Aire',
        'tipo' => 'preventivo',
        'frecuencia_dias' => 30,
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'estado' => 1,
    ]);

    $activo->mantenimientos()->create([
        'plan_id' => $plan->id,
        'fecha_programada' => now()->toDateString(),
        'estado' => EstadoMantenimiento::Programado,
    ]);

    $creados = app(DetectarMantenimientosPreventivos::class)->execute();

    expect($creados)->toBe(0);
    expect($activo->mantenimientos()->count())->toBe(1);
});
