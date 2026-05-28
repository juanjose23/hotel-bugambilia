<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Jobs\Activos\NotificarMantenimientosJob;
use App\Jobs\Activos\SincronizarEstadoActivoJob;
use App\Jobs\Activos\VerificarGarantiasJob;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimientoNotificacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\User;
use App\UseCases\Activos\Mutations\Gestion\VerificarGarantiasActivos;
use App\UseCases\Activos\Mutations\Mantenimiento\NotificarMantenimientos;
use App\UseCases\Activos\Mutations\Mantenimiento\SincronizarEstadoActivo;
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

it('despacha NotificarMantenimientosJob', function () {
    $useCase = mock(NotificarMantenimientos::class);
    $useCase->shouldReceive('execute')->once()->andReturn(0);

    $this->app->instance(NotificarMantenimientos::class, $useCase);

    NotificarMantenimientosJob::dispatchSync();
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

it('envía notificaciones de mantenimiento dirigidas y previene duplicidad de envíos', function () {
    $producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Aire acondicionado',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $activo = Activo::create([
        'codigo_inventario' => 'ACT-MAN-0001',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->subYear()->toDateString(),
        'estado' => EstadoActivo::Activo,
    ]);

    $tecnico = User::factory()->create();

    // Mantenimiento programado para dentro de 7 días exactos
    $mantenimiento = $activo->mantenimientos()->create([
        'tipo' => TipoMantenimiento::Preventivo,
        'fecha_programada' => today()->addDays(7)->toDateString(),
        'descripcion' => 'Revisión periódica de 7 días',
        'estado' => EstadoMantenimiento::Programado,
        'realizado_por_id' => $tecnico->id,
    ]);

    // Primer envío: Debe notificar al técnico y guardar el histórico de trazabilidad
    $enviadas = app(NotificarMantenimientos::class)->execute();
    expect($enviadas)->toBe(1);
    expect(ActivoMantenimientoNotificacion::count())->toBe(1);
    expect(ActivoMantenimientoNotificacion::first()->tipo)->toBe('proximo_7_dias');
    expect(ActivoMantenimientoNotificacion::first()->enviado_a)->toBe($tecnico->id);

    // Segundo envío: Al estar registrado en el histórico, no debe duplicar la notificación
    $reEnviadas = app(NotificarMantenimientos::class)->execute();
    expect($reEnviadas)->toBe(0);
    expect(ActivoMantenimientoNotificacion::count())->toBe(1);
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
