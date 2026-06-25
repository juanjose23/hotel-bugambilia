<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\ProductoKit;
use App\Models\Inventario\Stock;
use App\Models\User;
use App\UseCases\Inventario\Queries\Stock\VerificarStockPack;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Producto pack
    $this->productoPack = Producto::factory()->create([
        'nombre' => 'Kit de Limpieza',
        'tipo' => 2,
    ]);

    // Componentes del pack
    $this->producto1 = Producto::factory()->create([
        'nombre' => 'Jabón Líquido',
        'tipo' => 1,
    ]);
    $this->variante1 = ProductoVariante::create([
        'producto_id' => $this->producto1->id,
        'codigo' => 'JAB-1L',
        'nombre_variante' => '1L',
        'estado' => 1,
    ]);

    $this->producto2 = Producto::factory()->create([
        'nombre' => 'Cloro',
        'tipo' => 1,
    ]);
    $this->variante2 = ProductoVariante::create([
        'producto_id' => $this->producto2->id,
        'codigo' => 'CLR-500',
        'nombre_variante' => '500ml',
        'estado' => 1,
    ]);

    $this->bodega = Ubicacion::create([
        'nombre' => 'Almacén de Packs',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);
});

it('retorna suficiente=true cuando hay stock suficiente para armar los packs', function () {
    // Definir componentes del kit
    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante1->id,
        'cantidad' => 2.0,
    ]);

    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante2->id,
        'cantidad' => 1.0,
    ]);

    // Stock suficiente para 5 packs
    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 15.0,
    ]);

    Stock::create([
        'producto_id' => $this->producto2->id,
        'producto_variante_id' => $this->variante2->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 10.0,
    ]);

    $resultado = app(VerificarStockPack::class)->ejecutar(
        productoPackId: $this->productoPack->id,
        bodegaOrigenId: $this->bodega->id,
        cantidadPacks: 5.0
    );

    expect($resultado['suficiente'])->toBeTrue();
    expect($resultado['items'])->toHaveCount(2);

    $item1 = $resultado['items'][0];
    expect($item1['producto'])->toBe('Jabón Líquido');
    expect($item1['necesario'])->toBe(10.0);
    expect($item1['disponible'])->toBe(15.0);
    expect($item1['suficiente'])->toBeTrue();

    $item2 = $resultado['items'][1];
    expect($item2['producto'])->toBe('Cloro');
    expect($item2['necesario'])->toBe(5.0);
    expect($item2['disponible'])->toBe(10.0);
    expect($item2['suficiente'])->toBeTrue();
});

it('retorna suficiente=false cuando un componente tiene stock insuficiente', function () {
    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante1->id,
        'cantidad' => 3.0,
    ]);

    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante2->id,
        'cantidad' => 1.0,
    ]);

    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 5.0,
    ]);

    Stock::create([
        'producto_id' => $this->producto2->id,
        'producto_variante_id' => $this->variante2->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 10.0,
    ]);

    $resultado = app(VerificarStockPack::class)->ejecutar(
        productoPackId: $this->productoPack->id,
        bodegaOrigenId: $this->bodega->id,
        cantidadPacks: 3.0
    );

    expect($resultado['suficiente'])->toBeFalse();

    $item1 = $resultado['items'][0];
    expect($item1['producto'])->toBe('Jabón Líquido');
    expect($item1['necesario'])->toBe(9.0);
    expect($item1['disponible'])->toBe(5.0);
    expect($item1['suficiente'])->toBeFalse();

    $item2 = $resultado['items'][1];
    expect($item2['suficiente'])->toBeTrue();
});

it('retorna suficiente=false cuando todos los componentes tienen stock insuficiente', function () {
    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante1->id,
        'cantidad' => 2.0,
    ]);

    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante2->id,
        'cantidad' => 1.0,
    ]);

    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 1.0,
    ]);

    $resultado = app(VerificarStockPack::class)->ejecutar(
        productoPackId: $this->productoPack->id,
        bodegaOrigenId: $this->bodega->id,
        cantidadPacks: 2.0
    );

    expect($resultado['suficiente'])->toBeFalse();
    expect($resultado['items'][0]['suficiente'])->toBeFalse();
    expect($resultado['items'][1]['suficiente'])->toBeFalse();
});

it('no considera stock de lotes vencidos ni en cuarentena', function () {
    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante1->id,
        'cantidad' => 1.0,
    ]);

    // Stock sin lote (disponible siempre)
    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 5.0,
    ]);

    // Stock con lote vencido
    $loteVencido = Lote::create([
        'codigo_lote' => 'LOT-VENC-PACK',
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'estado' => EstadoLote::Vencido,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->bodega->id,
        'fecha_recepcion' => now()->subMonths(2)->toDateString(),
        'fecha_vencimiento' => now()->subDay()->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'lote_id' => $loteVencido->id,
        'cantidad' => 50.0,
    ]);

    // Stock con lote en cuarentena
    $loteCuarentena = Lote::create([
        'codigo_lote' => 'LOT-CUAR-PACK',
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'estado' => EstadoLote::Cuarentena,
        'cantidad_disponible' => 30.0,
        'cantidad_inicial' => 30.0,
        'ubicacion_id' => $this->bodega->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'lote_id' => $loteCuarentena->id,
        'cantidad' => 30.0,
    ]);

    // Solo deberia contar el stock sin lote (5.0), ignorando los lotes vencido/cuarentena
    $resultado = app(VerificarStockPack::class)->ejecutar(
        productoPackId: $this->productoPack->id,
        bodegaOrigenId: $this->bodega->id,
        cantidadPacks: 10.0
    );

    expect($resultado['suficiente'])->toBeFalse();
    expect($resultado['items'][0]['disponible'])->toBe(5.0);
    expect($resultado['items'][0]['necesario'])->toBe(10.0);
});

it('retorna items vacios cuando el pack no tiene componentes definidos', function () {
    $resultado = app(VerificarStockPack::class)->ejecutar(
        productoPackId: $this->productoPack->id,
        bodegaOrigenId: $this->bodega->id,
        cantidadPacks: 1.0
    );

    expect($resultado['suficiente'])->toBeTrue();
    expect($resultado['items'])->toBe([]);
});

it('muestra la variante y el tipo de producto en cada item', function () {
    ProductoKit::create([
        'producto_padre_id' => $this->productoPack->id,
        'producto_variante_id' => $this->variante1->id,
        'cantidad' => 1.0,
    ]);

    Stock::create([
        'producto_id' => $this->producto1->id,
        'producto_variante_id' => $this->variante1->id,
        'ubicacion_id' => $this->bodega->id,
        'cantidad' => 10.0,
    ]);

    $resultado = app(VerificarStockPack::class)->ejecutar(
        productoPackId: $this->productoPack->id,
        bodegaOrigenId: $this->bodega->id,
        cantidadPacks: 1.0
    );

    expect($resultado['items'])->toHaveCount(1);
    $item = $resultado['items'][0];
    expect($item['variante'])->toBe('1L');
    expect($item['producto_variante_id'])->toBe($this->variante1->id);
    expect($item['tipo_producto'])->toBe(1);
});
