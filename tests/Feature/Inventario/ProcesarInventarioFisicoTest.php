<?php

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\InventarioFisico;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\UseCases\Inventario\InventarioFisico\Mutations\ProcesarInventarioFisico;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);

    $this->lote = Lote::create([
        'codigo_lote' => 'LOTE-123',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    // Create physical inventory session sheet data
    $cellData = [
        '0' => [
            '0' => ['v' => 'ID Lote'],
            '4' => ['v' => 'Stock Sistema'],
            '5' => ['v' => 'Cantidad Física'],
            '7' => ['v' => 'Notas'],
        ],
        '1' => [
            '0' => ['v' => (string) $this->lote->id],
            '4' => ['v' => 50.0],
            '5' => ['v' => 45.0],
            '7' => ['v' => 'Ajuste por merma natural'],
        ],
    ];

    $this->inventario = InventarioFisico::create([
        'fecha_toma' => now()->toDateString(),
        'estado' => EstadoInventarioFisico::Borrador,
        'creado_por_id' => $this->user->id,
        'observaciones' => 'Sesión de inventario de prueba',
        'datos_hoja' => [
            'sheets' => [
                'sheet-1' => [
                    'cellData' => $cellData,
                ],
            ],
        ],
    ]);
});

it('procesa inventario fisico y ajusta stock de lotes con discrepancia', function () {
    $useCase = app(ProcesarInventarioFisico::class);
    $useCase->execute($this->inventario, $this->user->id);

    // Refresh Lote and assert stock is reconciled
    $this->lote->refresh();
    expect($this->lote->cantidad_disponible)->toBe(45.0);

    // Verify stock adjustment movement (MOV_AJUSTE)
    $movimiento = MovimientoStock::where('lote_id', $this->lote->id)
        ->where('tipo', 'MOV_AJUSTE')
        ->first();

    expect($movimiento)->not->toBeNull();
    expect($movimiento->cantidad)->toBe(5.0);
    // Since it was a discrepancy < 0 (missing items), original location should be the lote location, destination null.
    expect($movimiento->ubicacion_origen_id)->toBe($this->ubicacion->id);
    expect($movimiento->ubicacion_destino_id)->toBeNull();

    // Verify session state is updated to processed
    $this->inventario->refresh();
    expect($this->inventario->estado)->toBe(EstadoInventarioFisico::Procesado);
});

it('falla al procesar un inventario fisico ya procesado', function () {
    $this->inventario->update(['estado' => EstadoInventarioFisico::Procesado]);

    $useCase = app(ProcesarInventarioFisico::class);

    expect(fn () => $useCase->execute($this->inventario, $this->user->id))
        ->toThrow(RuntimeException::class, "El inventario físico {$this->inventario->codigo} ya ha sido procesado");
});
