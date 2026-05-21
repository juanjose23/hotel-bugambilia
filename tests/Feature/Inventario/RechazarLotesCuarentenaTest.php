<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\Services\Inventario\NotificadorInventario;
use App\UseCases\Inventario\Lotes\Mutations\RechazarLotesCuarentena;
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
        'tipo' => 'zona',
        'estado' => 1,
    ]);
    $this->ubicacionMerma = Ubicacion::create([
        'nombre' => 'Zona de Merma y Desecho',
        'tipo' => 'zona',
        'estado' => 1,
    ]);
});

it('puede rechazar un lote en cuarentena y enviarlo a la zona de merma', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-CUAR-2',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Cuarentena,
        'cantidad_disponible' => 30.0,
        'cantidad_inicial' => 30.0,
        'ubicacion_id' => $this->ubicacionCuarentena->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    // Mock notificador
    $notificador = mock(NotificadorInventario::class);
    $notificador->shouldReceive('loteRechazado')
        ->once()
        ->withArgs(fn ($arg, $mot) => $arg->id === $lote->id && $mot === 'Producto dañado');
    $this->app->instance(NotificadorInventario::class, $notificador);

    $rechazar = app(RechazarLotesCuarentena::class);
    $resultado = $rechazar->execute([$lote->id], 'Producto dañado', $this->user->id);

    expect($resultado)->toHaveCount(1);
    expect($resultado[0]['lote_id'])->toBe($lote->id);

    // Verificar lote actualizado
    $loteRefrescado = $lote->fresh();
    expect($loteRefrescado->estado)->toBe(EstadoLote::Rechazado);
    expect($loteRefrescado->cantidad_disponible)->toBe(0.0);
    expect($loteRefrescado->ubicacion_id)->toBe($this->ubicacionMerma->id);

    // Verificar movimiento de stock
    $movimiento = MovimientoStock::where('lote_id', $lote->id)->first();
    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo)->toBe('MOV_AJUSTE');
    expect($movimiento->cantidad)->toBe(30.0);
    expect($movimiento->ubicacion_origen_id)->toBe($this->ubicacionCuarentena->id);
    expect($movimiento->ubicacion_destino_id)->toBe($this->ubicacionMerma->id);
    expect($movimiento->creado_por_id)->toBe($this->user->id);
    expect($movimiento->notas)->toBe('Producto dañado');
});

it('lanza excepcion si no se encuentra configurada la zona de merma', function () {
    // Eliminar la ubicación de merma
    $this->ubicacionMerma->delete();

    $lote = Lote::create([
        'codigo_lote' => 'LOT-CUAR-3',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Cuarentena,
        'cantidad_disponible' => 30.0,
        'cantidad_inicial' => 30.0,
        'ubicacion_id' => $this->ubicacionCuarentena->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $rechazar = app(RechazarLotesCuarentena::class);

    expect(fn () => $rechazar->execute([$lote->id], 'Sin merma'))
        ->toThrow(RuntimeException::class, 'No se ha configurado una ubicación de "Zona de Merma" activa');
});

it('lanza excepcion si el lote no esta en cuarentena', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-DISP-2',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible, // No en cuarentena
        'cantidad_disponible' => 30.0,
        'cantidad_inicial' => 30.0,
        'ubicacion_id' => $this->ubicacionCuarentena->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $rechazar = app(RechazarLotesCuarentena::class);

    expect(fn () => $rechazar->execute([$lote->id], 'No en cuarentena'))
        ->toThrow(InvalidArgumentException::class, 'no está en cuarentena');
});
