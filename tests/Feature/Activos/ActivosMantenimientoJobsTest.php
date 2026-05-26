<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Jobs\Activos\DetectarMantenimientosAtrasadosJob;
use App\Jobs\Activos\SincronizarEstadoActivoJob;
use App\Jobs\Activos\VerificarActivosSinMantenimientoHistoricoJob;
use App\Jobs\Activos\VerificarGarantiasJob;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\User;
use App\UseCases\Activos\Mutations\DetectarMantenimientosAtrasados;
use App\UseCases\Activos\Mutations\SincronizarEstadoActivo;
use App\UseCases\Activos\Mutations\VerificarActivosSinMantenimientoHistorico;
use App\UseCases\Activos\Mutations\VerificarGarantiasActivos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\mock;

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

it('despacha VerificarGarantiasJob', function () {
    $useCase = mock(VerificarGarantiasActivos::class);
    $useCase->shouldReceive('execute')->once()->andReturn(0);

    $this->app->instance(VerificarGarantiasActivos::class, $useCase);

    VerificarGarantiasJob::dispatchSync();
});

it('despacha DetectarMantenimientosAtrasadosJob', function () {
    $useCase = mock(DetectarMantenimientosAtrasados::class);
    $useCase->shouldReceive('execute')->once()->andReturn(0);

    $this->app->instance(DetectarMantenimientosAtrasados::class, $useCase);

    DetectarMantenimientosAtrasadosJob::dispatchSync();
});

it('despacha VerificarActivosSinMantenimientoHistoricoJob', function () {
    $useCase = mock(VerificarActivosSinMantenimientoHistorico::class);
    $useCase->shouldReceive('execute')->once()->andReturn(0);

    $this->app->instance(VerificarActivosSinMantenimientoHistorico::class, $useCase);

    VerificarActivosSinMantenimientoHistoricoJob::dispatchSync();
});

it('despacha SincronizarEstadoActivoJob', function () {
    $useCase = mock(SincronizarEstadoActivo::class);
    $useCase->shouldReceive('execute')->once()->andReturn(0);

    $this->app->instance(SincronizarEstadoActivo::class, $useCase);

    SincronizarEstadoActivoJob::dispatchSync();
});

it('notifica garantías próximas a vencer', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor 65 pulgadas',
        'tipo' => 3,
        'estado' => 1,
    ]);

    Activo::create([
        'codigo_inventario' => 'ACT-GAR-0001',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subYear()->toDateString(),
        'fecha_garantia_fin' => now()->addDays(10)->toDateString(),
        'estado' => EstadoActivo::Activo,
    ]);

    $count = app(VerificarGarantiasActivos::class)->execute();

    expect($count)->toBe(1);
    expect(database_notifications_count())->toBe(1);
});

it('detecta mantenimientos atrasados y prolongados', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Aire acondicionado',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $activoProgramado = Activo::create([
        'codigo_inventario' => 'ACT-MAN-0001',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subYear()->toDateString(),
        'estado' => EstadoActivo::EnMantenimiento,
    ]);

    $activoEnProceso = Activo::create([
        'codigo_inventario' => 'ACT-MAN-0002',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subYear()->toDateString(),
        'estado' => EstadoActivo::EnMantenimiento,
    ]);

    $activoProgramado->mantenimientos()->create([
        'tipo' => TipoMantenimiento::Preventivo,
        'fecha_programada' => now()->subDays(8)->toDateString(),
        'descripcion' => 'Mantenimiento programado vencido',
        'estado' => EstadoMantenimiento::Programado,
    ]);

    $activoEnProceso->mantenimientos()->create([
        'tipo' => TipoMantenimiento::Correctivo,
        'fecha_programada' => now()->subDays(20)->toDateString(),
        'descripcion' => 'Mantenimiento en proceso prolongado',
        'estado' => EstadoMantenimiento::EnProceso,
    ]);

    expect(ActivoMantenimiento::count())->toBe(2);

    $count = app(DetectarMantenimientosAtrasados::class)->execute();

    expect($count)->toBe(2);
    expect(database_notifications_count())->toBe(2);
});

it('verifica activos sin mantenimiento histórico', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Extractor industrial',
        'tipo' => 3,
        'estado' => 1,
    ]);

    Activo::create([
        'codigo_inventario' => 'ACT-HIS-0001',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subYears(2)->toDateString(),
        'estado' => EstadoActivo::Activo,
    ]);

    $count = app(VerificarActivosSinMantenimientoHistorico::class)->execute();

    expect($count)->toBe(1);
    expect(database_notifications_count())->toBe(1);
});

it('sincroniza el estado del activo cuando el mantenimiento se completa', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Bomba de agua',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $activo = Activo::create([
        'codigo_inventario' => 'ACT-SIN-0001',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subYear()->toDateString(),
        'estado' => EstadoActivo::EnMantenimiento,
    ]);

    $activo->mantenimientos()->create([
        'tipo' => TipoMantenimiento::Correctivo,
        'fecha_programada' => now()->subDays(3)->toDateString(),
        'fecha_fin' => now()->toDateString(),
        'descripcion' => 'Mantenimiento finalizado',
        'estado' => EstadoMantenimiento::Completado,
    ]);

    $actualizados = app(SincronizarEstadoActivo::class)->execute();

    expect($actualizados)->toBe(1);
    expect($activo->refresh()->estado)->toBe(EstadoActivo::Activo);
});

function database_notifications_count(): int
{
    return DB::table('notifications')->count();
}
