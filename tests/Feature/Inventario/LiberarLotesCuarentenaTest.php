<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\Services\Inventario\NotificadorInventario;
use App\UseCases\Inventario\Lotes\Mutations\LiberarLotesCuarentena;
use App\UseCases\Inventario\Services\PutawayPolicy;

use function Pest\Laravel\mock;

beforeEach(function () {
    // Reset static cache in PutawayPolicy to prevent test pollution
    $ref = new ReflectionClass(PutawayPolicy::class);
    $ref->setStaticPropertyValue('cache', null);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacionCuarentena = Ubicacion::create([
        'nombre' => 'Zona de Cuarentena',
        'tipo' => 'sector',
        'estado' => 1,
    ]);
    $this->ubicacionAlmacen = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);
});

it('puede liberar un lote que se encuentra en cuarentena', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-CUAR-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Cuarentena,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacionCuarentena->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    // Mock notificador
    $notificador = mock(NotificadorInventario::class);
    $notificador->shouldReceive('loteLiberado')
        ->once()
        ->withArgs(fn ($arg) => $arg->id === $lote->id);
    $this->app->instance(NotificadorInventario::class, $notificador);

    $liberar = app(LiberarLotesCuarentena::class);
    $resultado = $liberar->execute([$lote->id], $this->user->id);

    expect($resultado)->toHaveCount(1);
    expect($resultado[0]['lote_id'])->toBe($lote->id);

    // Verificar actualización del lote
    $loteRefrescado = $lote->fresh();
    expect($loteRefrescado->estado)->toBe(EstadoLote::Disponible);
    expect($loteRefrescado->ubicacion_id)->toBe($this->ubicacionAlmacen->id); // Debe sugerir Almacén Principal

    // Verificar movimiento de stock
    $movimiento = MovimientoStock::where('lote_id', $lote->id)->first();
    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo)->toBe('MOV_TRANSFERENCIA');
    expect($movimiento->cantidad)->toBe(50.0);
    expect($movimiento->ubicacion_origen_id)->toBe($this->ubicacionCuarentena->id);
    expect($movimiento->ubicacion_destino_id)->toBe($this->ubicacionAlmacen->id);
    expect($movimiento->documento_tipo)->toBe('liberacion_cuarentena');
    expect($movimiento->creado_por_id)->toBe($this->user->id);
});

it('ignora lotes que no estan en cuarentena y devuelve un resultado vacio', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-DISP-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible, // Ya está disponible
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacionAlmacen->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    // Mock notificador (no debe recibir llamadas)
    $notificador = mock(NotificadorInventario::class);
    $notificador->shouldNotReceive('loteLiberado');
    $this->app->instance(NotificadorInventario::class, $notificador);

    $liberar = app(LiberarLotesCuarentena::class);
    $resultado = $liberar->execute([$lote->id], $this->user->id);

    expect($resultado)->toBeEmpty();
    expect($lote->fresh()->estado)->toBe(EstadoLote::Disponible);
});
