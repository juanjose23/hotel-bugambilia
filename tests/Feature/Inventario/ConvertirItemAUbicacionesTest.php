<?php

use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\User;
use App\UseCases\Inventario\Recepciones\Mutations\ConvertirItemAUbicaciones;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create([
        'nombre' => 'Producto Prueba',
    ]);

    $this->proveedor = Proveedor::factory()->create();

    $this->orden = OrdenCompra::factory()->create([
        'proveedor_id' => $this->proveedor->id,
    ]);

    $this->ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 10.0,
    ]);

    $this->recepcion = RecepcionCompra::factory()->create([
        'codigo' => 'RC-2026-TEST',
        'orden_compra_id' => $this->orden->id,
    ]);

    $this->recepcionItem = RecepcionItem::factory()->create([
        'recepcion_id' => $this->recepcion->id,
        'orden_item_id' => $this->ordenItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_recibida' => 10.0,
    ]);
});

it('convierte un item recibido en una jerarquia de sub-ubicaciones recursivas', function () {
    $parentUbicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    $data = [
        'recepcion_item_id' => $this->recepcionItem->id,
        'parent_id' => $parentUbicacion->id,
        'nombre_prefijo' => 'Estante',
        'cantidad_a_convertir' => 2,
        'niveles_por_unidad' => 2,
        'posiciones_por_nivel' => 3,
    ];

    $useCase = app(ConvertirItemAUbicaciones::class);
    $creadas = $useCase->execute($data);

    // Total expected locations:
    // For each structure (2):
    //   1 estante base
    //   2 niveles (2 * 1 = 2)
    //   3 posiciones por nivel (2 * 3 = 6)
    //   Total per structure = 1 + 2 + 6 = 9
    // For 2 structures: 9 * 2 = 18 locations
    expect($creadas)->toHaveCount(18);

    // Verify structures
    $estantes = Ubicacion::where('tipo', 'estante')->get();
    expect($estantes)->toHaveCount(2);
    expect($estantes[0]->nombre)->toBe('Estante 1');
    expect($estantes[0]->padre_id)->toBe($parentUbicacion->id);
    expect($estantes[1]->nombre)->toBe('Estante 2');
    expect($estantes[1]->padre_id)->toBe($parentUbicacion->id);

    // Verify levels
    $niveles = Ubicacion::where('tipo', 'nivel')->get();
    expect($niveles)->toHaveCount(4); // 2 structures * 2 levels each
    expect($niveles->first()->nombre)->toBe('Nivel 1');

    // Verify positions
    $posiciones = Ubicacion::where('tipo', 'posicion')->get();
    expect($posiciones)->toHaveCount(12); // 4 levels * 3 positions each
    expect($posiciones->first()->nombre)->toBe('Posición 1');
});
