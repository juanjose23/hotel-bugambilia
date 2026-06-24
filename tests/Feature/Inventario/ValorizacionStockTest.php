<?php

use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use Illuminate\Support\Str;

function crearLoteConPrecio(Producto $producto, Ubicacion $ubicacion, float $cantidad, float $precioUnitario, string $codigo, ?EstadoLote $estado = null): Lote
{
    $estado ??= EstadoLote::Disponible;
    $proveedor = Proveedor::factory()->create();
    $moneda = Moneda::create(['codigo' => Str::random(8), 'nombre' => 'Dolar', 'simbolo' => '$']);

    $oc = OrdenCompra::create([
        'proveedor_id' => $proveedor->id,
        'moneda_id' => $moneda->id,
        'codigo' => 'OC-'.Str::random(6),
        'subtotal' => $precioUnitario * $cantidad,
        'total' => $precioUnitario * $cantidad,
        'tasa_cambio' => 1.0,
        'fecha_orden' => now()->toDateString(),
        'estado' => 1,
    ]);

    $oci = OrdenCompraItem::create([
        'orden_compra_id' => $oc->id,
        'producto_id' => $producto->id,
        'cantidad' => $cantidad,
        'precio_unitario' => $precioUnitario,
        'subtotal' => $precioUnitario * $cantidad,
    ]);

    $rc = RecepcionCompra::create([
        'orden_compra_id' => $oc->id,
        'codigo' => 'REC-'.Str::random(6),
        'fecha_recepcion' => now()->toDateString(),
        'recibido_por_id' => User::factory()->create()->id,
        'estado' => EstadoRecepcion::Completa,
    ]);

    $ri = RecepcionItem::create([
        'recepcion_id' => $rc->id,
        'orden_item_id' => $oci->id,
        'producto_id' => $producto->id,
        'cantidad_recibida' => $cantidad,
    ]);

    return Lote::create([
        'codigo_lote' => $codigo,
        'producto_id' => $producto->id,
        'estado' => $estado,
        'cantidad_disponible' => $cantidad,
        'cantidad_inicial' => $cantidad,
        'ubicacion_id' => $ubicacion->id,
        'recepcion_item_id' => $ri->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);
}

beforeEach(function () {
    $this->categoria = Catalogo::factory()->create(['nombre' => 'Insumos']);
    $this->producto = Producto::factory()->create([
        'nombre' => 'Detergente Industrial',
        'categoria_id' => $this->categoria->id,
    ]);
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Central',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);
});

it('calcula valorizacion usando precio de orden de compra', function () {
    crearLoteConPrecio($this->producto, $this->ubicacion, 100.0, 15.50, 'LOT-VAL-1');

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar();

    expect($resultado)->toHaveCount(1);
    $row = $resultado->first();
    expect($row->producto)->toBe('Detergente Industrial');
    expect($row->categoria)->toBe('Insumos');
    expect($row->ubicacion)->toBe('Almacén Central');
    expect((float) $row->stock_total)->toBe(100.0);
    expect((float) $row->costo_promedio)->toBe(15.50);
    expect((float) $row->valor_total)->toBe(1550.0);
});

it('agrupa correctamente multiples lotes del mismo producto', function () {
    crearLoteConPrecio($this->producto, $this->ubicacion, 50.0, 10.0, 'LOT-VAL-2A');
    crearLoteConPrecio($this->producto, $this->ubicacion, 30.0, 12.0, 'LOT-VAL-2B');

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar();

    expect($resultado)->toHaveCount(1);
    $row = $resultado->first();
    expect((float) $row->stock_total)->toBe(80.0);
    expect((float) $row->costo_promedio)->toBe(11.0);
    expect((float) $row->valor_total)->toBe(860.0);
});

it('filtra por ubicacion_id', function () {
    $otraUbicacion = Ubicacion::create([
        'nombre' => 'Bodega Sur',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    crearLoteConPrecio($this->producto, $otraUbicacion, 20.0, 8.0, 'LOT-VAL-UBI');

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar(
        filtros: ['ubicacion_id' => $this->ubicacion->id]
    );

    expect($resultado)->toHaveCount(0);
});

it('filtra por producto_id', function () {
    $otroProducto = Producto::factory()->create(['nombre' => 'Otro Producto']);
    crearLoteConPrecio($otroProducto, $this->ubicacion, 10.0, 5.0, 'LOT-VAL-OTRO');

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar(
        filtros: ['producto_id' => $this->producto->id]
    );

    expect($resultado)->toHaveCount(0);
});

it('ignora lotes en estado diferente a Disponible', function () {
    crearLoteConPrecio($this->producto, $this->ubicacion, 100.0, 20.0, 'LOT-CUAR-VAL', EstadoLote::Cuarentena);

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar();

    expect($resultado)->toHaveCount(0);
});

it('ignora lotes con cantidad_disponible igual a 0', function () {
    $proveedor = Proveedor::factory()->create();
    $moneda = Moneda::create(['codigo' => Str::random(8), 'nombre' => 'Dolar', 'simbolo' => '$']);
    $oc = OrdenCompra::create([
        'proveedor_id' => $proveedor->id,
        'moneda_id' => $moneda->id,
        'codigo' => 'OC-CERO',
        'subtotal' => 1000,
        'total' => 1000,
        'fecha_orden' => now()->toDateString(),
        'estado' => 1,
    ]);
    $oci = OrdenCompraItem::create(['orden_compra_id' => $oc->id, 'producto_id' => $this->producto->id, 'cantidad' => 100, 'precio_unitario' => 10, 'subtotal' => 1000]);
    $rc = RecepcionCompra::create(['orden_compra_id' => $oc->id, 'codigo' => 'REC-CERO', 'fecha_recepcion' => now()->toDateString(), 'recibido_por_id' => User::factory()->create()->id, 'estado' => EstadoRecepcion::Completa]);
    $ri = RecepcionItem::create(['recepcion_id' => $rc->id, 'orden_item_id' => $oci->id, 'producto_id' => $this->producto->id, 'cantidad_recibida' => 100]);
    Lote::create([
        'codigo_lote' => 'LOT-VAL-CERO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 0.0,
        'cantidad_inicial' => 100.0,
        'ubicacion_id' => $this->ubicacion->id,
        'recepcion_item_id' => $ri->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar();

    expect($resultado)->toHaveCount(0);
});

it('trata precio null como 0 cuando no hay orden de compra asociada', function () {
    Lote::create([
        'codigo_lote' => 'LOT-VAL-NULL',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar();

    expect($resultado)->toHaveCount(1);
    expect((float) $resultado->first()->costo_promedio)->toBe(0.0);
    expect((float) $resultado->first()->valor_total)->toBe(0.0);
});

it('totalGeneral retorna la suma de todos los valor_total', function () {
    crearLoteConPrecio($this->producto, $this->ubicacion, 10.0, 100.0, 'LOT-VAL-T1');

    $otroProducto = Producto::factory()->create(['nombre' => 'Producto B']);
    crearLoteConPrecio($otroProducto, $this->ubicacion, 5.0, 50.0, 'LOT-VAL-T2');

    $total = app(ObtenerValorizacionInventario::class)->totalGeneral();

    expect($total)->toBe(1250.0);
});

it('totalGeneral retorna 0 cuando no hay inventario valorizable', function () {
    $total = app(ObtenerValorizacionInventario::class)->totalGeneral();

    expect($total)->toBe(0.0);
});

it('excluye lotes soft-deleted', function () {
    $lote = crearLoteConPrecio($this->producto, $this->ubicacion, 100.0, 25.0, 'LOT-VAL-DEL');
    $lote->delete();

    $resultado = app(ObtenerValorizacionInventario::class)->ejecutar();

    expect($resultado)->toHaveCount(0);
});
