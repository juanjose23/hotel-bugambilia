<?php

declare(strict_types=1);

use App\Enums\Compras\EstadoSolicitud;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Interactors\Restaurante\Cocina\CrearSolicitudAbastecimientoCocina;
use App\Interactors\Restaurante\Cocina\DespacharSolicitudAbastecimientoCocina;
use App\Interactors\Restaurante\Cocina\ResolverSolicitudAbastecimientoCocina;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\ObtenerAbastecimientoInteligenteCocina;
use Spatie\Permission\Models\Permission;

function usuarioAutorizaInventario(): User
{
    Permission::firstOrCreate(['name' => 'Inventario:ResolverAbastecimientoCocina', 'guard_name' => 'web']);

    $usuario = User::factory()->create(['is_admin' => true]);
    $usuario->givePermissionTo('Inventario:ResolverAbastecimientoCocina');

    return $usuario;
}

test('la solicitud de abastecimiento de cocina guarda producto variante y observaciones', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo abastecimiento {$sufijo}",
        'codigo' => "tipo-abastecimiento-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes {$sufijo}",
        'codigo' => "ingredientes-abast-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Gramos {$sufijo}",
        'codigo' => "gramos-{$sufijo}",
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Queso {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "QUESO-{$sufijo}",
        'nombre_variante' => '250g',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $colaborador = Colaborador::factory()->create();

    $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
        motivo: 'Falta ingrediente para cocina',
        items: [[
            'producto_variante_id' => $variante->id,
            'cantidad' => 4,
            'justificacion' => 'Reponer para menú del día',
        ]],
        colaboradorId: (int) $colaborador->id,
    );

    $item = $solicitud->items()->firstOrFail();

    expect((int) $solicitud->colaborador_id)->toBe((int) $colaborador->id)
        ->and((int) $item->producto_id)->toBe((int) $producto->id)
        ->and((int) $item->producto_variante_id)->toBe((int) $variante->id)
        ->and((int) $item->unidad_medida_id)->toBe((int) $unidad->id)
        ->and($item->observaciones)->toBe('Reponer para menú del día');
});

test('despacha solicitud aprobada desde bodega hacia cocina y registra movimiento', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo despacho {$sufijo}",
        'codigo' => "tipo-despacho-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes despacho {$sufijo}",
        'codigo' => "ingredientes-despacho-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad despacho {$sufijo}",
        'codigo' => "unidad-despacho-{$sufijo}",
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Cebolla despacho {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "CEB-DESP-{$sufijo}",
        'nombre_variante' => 'kg',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $origen = Ubicacion::query()->create([
        'nombre' => "Bodega despacho {$sufijo}",
        'tipo' => 'bodega',
        'orden' => random_int(10000, 99999),
        'estado' => 1,
    ]);
    $destino = Ubicacion::query()->create([
        'nombre' => "Cocina despacho {$sufijo}",
        'tipo' => 'cocina',
        'orden' => random_int(10000, 99999),
        'estado' => 1,
    ]);
    $stockOrigen = Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $origen->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 20,
        'cantidad_actual' => 10,
    ]);
    $colaborador = Colaborador::factory()->create();

    $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
        motivo: 'Despacho cocina',
        items: [[
            'producto_variante_id' => $variante->id,
            'cantidad' => 4,
            'justificacion' => 'Reponer cocina',
        ]],
        colaboradorId: (int) $colaborador->id,
    );
    $solicitud->items()->firstOrFail()->update(['cantidad_aprobada' => 4]);
    $solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

    $usuario = usuarioAutorizaInventario();

    app(DespacharSolicitudAbastecimientoCocina::class)->ejecutar($solicitud, (int) $origen->id, (int) $destino->id, (int) $usuario->id);

    $stockDestino = Stock::query()
        ->where('stockable_type', Ubicacion::class)
        ->where('stockable_id', $destino->id)
        ->where('producto_variante_id', $variante->id)
        ->firstOrFail();

    expect((float) $stockOrigen->refresh()->cantidad_actual)->toBe(6.0)
        ->and((float) $stockDestino->cantidad_actual)->toBe(4.0)
        ->and(MovimientoStock::query()->where('documento_tipo', 'solicitud_abastecimiento_cocina')->where('documento_id', $solicitud->id)->exists())->toBeTrue();
});

test('no despacha solicitud de cocina si bodega no tiene stock suficiente', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo sin stock {$sufijo}",
        'codigo' => "tipo-sin-stock-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes sin stock {$sufijo}",
        'codigo' => "ingredientes-sin-stock-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad sin stock {$sufijo}",
        'codigo' => "unidad-sin-stock-{$sufijo}",
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Ajo sin stock {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "AJO-SIN-{$sufijo}",
        'nombre_variante' => 'kg',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $origen = Ubicacion::query()->create([
        'nombre' => "Bodega sin stock {$sufijo}",
        'tipo' => 'bodega',
        'orden' => random_int(10000, 99999),
        'estado' => 1,
    ]);
    $destino = Ubicacion::query()->create([
        'nombre' => "Cocina sin stock {$sufijo}",
        'tipo' => 'cocina',
        'orden' => random_int(10000, 99999),
        'estado' => 1,
    ]);
    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $origen->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 20,
        'cantidad_actual' => 1,
    ]);
    $colaborador = Colaborador::factory()->create();

    $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
        motivo: 'Despacho cocina',
        items: [[
            'producto_variante_id' => $variante->id,
            'cantidad' => 4,
        ]],
        colaboradorId: (int) $colaborador->id,
    );
    $solicitud->items()->firstOrFail()->update(['cantidad_aprobada' => 4]);
    $solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

    $usuario = usuarioAutorizaInventario();

    app(DespacharSolicitudAbastecimientoCocina::class)->ejecutar($solicitud, (int) $origen->id, (int) $destino->id, (int) $usuario->id);
})->throws(DomainException::class, 'Stock insuficiente en bodega');

test('resuelve solicitud de cocina tomando stock de varias bodegas internas', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo multi bodega {$sufijo}",
        'codigo' => "tipo-multi-bodega-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes multi bodega {$sufijo}",
        'codigo' => "ingredientes-multi-bodega-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad multi bodega {$sufijo}",
        'codigo' => "unidad-multi-bodega-{$sufijo}",
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Harina multi bodega {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "HAR-MULTI-{$sufijo}",
        'nombre_variante' => 'kg',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $bodegaUno = Ubicacion::query()->create(['nombre' => "Bodega A {$sufijo}", 'tipo' => 'bodega', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    $bodegaDos = Ubicacion::query()->create(['nombre' => "Bodega B {$sufijo}", 'tipo' => 'bodega', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    $cocina = Ubicacion::query()->create(['nombre' => "Cocina multi {$sufijo}", 'tipo' => 'cocina', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    $stockUno = Stock::query()->create(['stockable_type' => Ubicacion::class, 'stockable_id' => $bodegaUno->id, 'producto_variante_id' => $variante->id, 'cantidad_ideal' => 10, 'cantidad_actual' => 3]);
    $stockDos = Stock::query()->create(['stockable_type' => Ubicacion::class, 'stockable_id' => $bodegaDos->id, 'producto_variante_id' => $variante->id, 'cantidad_ideal' => 10, 'cantidad_actual' => 4]);
    $colaborador = Colaborador::factory()->create();

    $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
        motivo: 'Resolver desde bodegas',
        items: [['producto_variante_id' => $variante->id, 'cantidad' => 6]],
        colaboradorId: (int) $colaborador->id,
    );
    $solicitud->items()->firstOrFail()->update(['cantidad_aprobada' => 6]);
    $solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

    $usuario = usuarioAutorizaInventario();

    app(ResolverSolicitudAbastecimientoCocina::class)->ejecutar($solicitud, (int) $usuario->id);

    $stockCocina = Stock::query()
        ->where('stockable_type', Ubicacion::class)
        ->where('stockable_id', $cocina->id)
        ->where('producto_variante_id', $variante->id)
        ->firstOrFail();

    expect((float) $stockCocina->cantidad_actual)->toBe(6.0)
        ->and((float) $stockUno->refresh()->cantidad_actual + (float) $stockDos->refresh()->cantidad_actual)->toBe(1.0)
        ->and(MovimientoStock::query()->where('documento_id', $solicitud->id)->where('documento_tipo', 'solicitud_abastecimiento_cocina')->count())->toBeGreaterThanOrEqual(2)
        ->and((string) $solicitud->refresh()->notas)->toContain("Bodega B {$sufijo}")
        ->and((string) $solicitud->refresh()->notas)->toContain("Cocina multi {$sufijo}");
});

test('si inventario interno no alcanza la solicitud debe seguir a compra', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create(['nombre' => "Tipo compra {$sufijo}", 'codigo' => "tipo-compra-{$sufijo}", 'estado' => 1]);
    $categoria = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'nombre' => "Ingredientes compra {$sufijo}", 'codigo' => "ingredientes-compra-{$sufijo}", 'estado' => 1]);
    $unidad = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'nombre' => "Unidad compra {$sufijo}", 'codigo' => "unidad-compra-{$sufijo}", 'estado' => 1]);
    $producto = Producto::query()->create(['categoria_id' => $categoria->id, 'nombre' => "Aceite compra {$sufijo}", 'unidad_medida_id' => $unidad->id, 'tipo' => 1, 'estado' => 1]);
    $variante = ProductoVariante::query()->create(['producto_id' => $producto->id, 'codigo' => "ACE-COMPRA-{$sufijo}", 'nombre_variante' => 'litro', 'unidad_medida_id' => $unidad->id, 'estado' => 1]);
    $bodega = Ubicacion::query()->create(['nombre' => "Bodega compra {$sufijo}", 'tipo' => 'bodega', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    Ubicacion::query()->create(['nombre' => "Cocina compra {$sufijo}", 'tipo' => 'cocina', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    Stock::query()->create(['stockable_type' => Ubicacion::class, 'stockable_id' => $bodega->id, 'producto_variante_id' => $variante->id, 'cantidad_ideal' => 10, 'cantidad_actual' => 1]);
    $colaborador = Colaborador::factory()->create();

    $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
        motivo: 'Debe comprar',
        items: [['producto_variante_id' => $variante->id, 'cantidad' => 5]],
        colaboradorId: (int) $colaborador->id,
    );
    $solicitud->items()->firstOrFail()->update(['cantidad_aprobada' => 5]);
    $solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

    $usuario = usuarioAutorizaInventario();

    app(ResolverSolicitudAbastecimientoCocina::class)->ejecutar($solicitud, (int) $usuario->id);
})->throws(DomainException::class, 'Inventario interno insuficiente');

test('no resuelve abastecimiento entre bodegas sin permiso de inventario', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create(['nombre' => "Tipo sin permiso {$sufijo}", 'codigo' => "tipo-sin-permiso-{$sufijo}", 'estado' => 1]);
    $categoria = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'nombre' => "Ingredientes sin permiso {$sufijo}", 'codigo' => "ingredientes-sin-permiso-{$sufijo}", 'estado' => 1]);
    $unidad = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'nombre' => "Unidad sin permiso {$sufijo}", 'codigo' => "unidad-sin-permiso-{$sufijo}", 'estado' => 1]);
    $producto = Producto::query()->create(['categoria_id' => $categoria->id, 'nombre' => "Sal sin permiso {$sufijo}", 'unidad_medida_id' => $unidad->id, 'tipo' => 1, 'estado' => 1]);
    $variante = ProductoVariante::query()->create(['producto_id' => $producto->id, 'codigo' => "SAL-SIN-{$sufijo}", 'nombre_variante' => 'kg', 'unidad_medida_id' => $unidad->id, 'estado' => 1]);
    $bodega = Ubicacion::query()->create(['nombre' => "Bodega sin permiso {$sufijo}", 'tipo' => 'bodega', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    Ubicacion::query()->create(['nombre' => "Cocina sin permiso {$sufijo}", 'tipo' => 'cocina', 'orden' => random_int(10000, 99999), 'estado' => 1]);
    Stock::query()->create(['stockable_type' => Ubicacion::class, 'stockable_id' => $bodega->id, 'producto_variante_id' => $variante->id, 'cantidad_ideal' => 10, 'cantidad_actual' => 5]);
    $colaborador = Colaborador::factory()->create();
    $usuario = User::factory()->create(['is_admin' => true]);

    $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
        motivo: 'Debe pedir autorización',
        items: [['producto_variante_id' => $variante->id, 'cantidad' => 2]],
        colaboradorId: (int) $colaborador->id,
    );
    $solicitud->items()->firstOrFail()->update(['cantidad_aprobada' => 2]);
    $solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

    app(ResolverSolicitudAbastecimientoCocina::class)->ejecutar($solicitud, (int) $usuario->id);
})->throws(DomainException::class, 'permiso de inventario');

test('la solicitud inteligente sugiere stock bajo y faltantes de pedidos bloqueados agrupados', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo inteligente {$sufijo}",
        'codigo' => "tipo-inteligente-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes inteligente {$sufijo}",
        'codigo' => "ingredientes-inteligente-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad inteligente {$sufijo}",
        'codigo' => "unidad-inteligente-{$sufijo}",
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Tomate inteligente {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "TOM-INT-{$sufijo}",
        'nombre_variante' => 'kg',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->create([
        'nombre' => "Cocina inteligente {$sufijo}",
        'tipo' => 'cocina',
        'orden' => random_int(10000, 99999),
        'estado' => 1,
    ]);

    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 6,
    ]);

    $pedido = Pedido::query()->create([
        'codigo' => "PED-INT-{$sufijo}",
        'estado' => 1,
        'subtotal' => 0,
        'abierto_en' => now(),
    ]);
    PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'estado' => EstadoItemPedido::BLOQUEADO_STOCK,
        'bloqueo_stock_detalle' => [[
            'variante_original_id' => $variante->id,
            'ingrediente' => "Tomate inteligente {$sufijo} - kg",
            'faltante' => 2,
        ]],
        'bloqueado_stock_en' => now(),
    ]);

    $sugerencia = app(ObtenerAbastecimientoInteligenteCocina::class)->ejecutar();
    $item = collect($sugerencia['items'])->firstWhere('producto_variante_id', $variante->id);

    expect($item)->not->toBeNull()
        ->and((float) $item['cantidad'])->toBe(6.0)
        ->and($item['justificacion'])->toContain('Stock actual')
        ->and($item['justificacion'])->toContain('Pedido bloqueado');
});
