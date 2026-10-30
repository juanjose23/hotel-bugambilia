<?php

declare(strict_types=1);

use App\Interactors\Restaurante\Cocina\AnularItemPedido;
use App\Interactors\Restaurante\Cocina\RegistrarMermaCocina;
use App\Interactors\Restaurante\Cocina\TransformarMateriaPrimaCocina;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Shared\Stock;

require_once __DIR__.'/../../Support/CocinaFixtures.php';

test('enviar a cocina consume una sola vez y repetir el envío no duplica consumo', function () {
    $f = fixtureCorreccionesCocina();
    [$pedido,$item] = pedidoCorreccionesCocina($f);
    app(EnviarPedidoACocina::class)->ejecutar($pedido);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0);
    expect(fn () => app(EnviarPedidoACocina::class)->ejecutar($pedido))->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0);
});
test('anular partida ajusta la cuenta pero conserva el consumo físico realizado', function () {
    $f = fixtureCorreccionesCocina();
    [$pedido,$item] = pedidoCorreccionesCocina($f);
    app(EnviarPedidoACocina::class)->ejecutar($pedido);
    app(AnularItemPedido::class)->ejecutar($item->id);
    expect((float) $pedido->refresh()->cuenta->saldo)->toBe(0.0)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0);
});
test('transformación rechaza resultados inválidos sin descontar origen', function () {
    $f = fixtureCorreccionesCocina();
    $data = datosTransformacionCocina($f);
    $data['items'] = [['producto_destino_id' => 0, 'cantidad' => 1]];
    expect(fn () => app(TransformarMateriaPrimaCocina::class)->ejecutar($data))->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});
test('transformación rechaza un rendimiento imposible en la misma unidad', function () {
    $f = fixtureCorreccionesCocina();
    expect(fn () => app(TransformarMateriaPrimaCocina::class)->ejecutar(datosTransformacionCocina($f, 100)))->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});
test('transformación conserva cada lote elaborado y calcula costos desde el origen', function () {
    $f = fixtureCorreccionesCocina();
    $lote = Lote::create(['producto_id' => $f['productoOrigen']->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'ubicacion_id' => $f['cocina']->id, 'codigo_lote' => 'REG-'.bin2hex(random_bytes(4)), 'cantidad_inicial' => 10, 'cantidad_disponible' => 10, 'costo_unitario' => 20, 'costo_total' => 200, 'estado' => 1, 'fecha_recepcion' => now(), 'fecha_vencimiento' => now()->addDays(2)]);
    $f['stockOrigen']->update(['lote_id' => $lote->id]);
    $first = app(TransformarMateriaPrimaCocina::class)->ejecutar(datosTransformacionCocina($f, .8));
    $second = app(TransformarMateriaPrimaCocina::class)->ejecutar(datosTransformacionCocina($f, .7));
    $stocks = Stock::where('producto_variante_id', $f['varianteDestino']->id)->with('lote')->get();
    expect($stocks)->toHaveCount(2)->and((float) $stocks->sum('cantidad_actual'))->toBe(1.5)->and((float) $first->costo_total)->toBe(20.0)->and((float) $lote->refresh()->cantidad_disponible)->toBe(8.0);
    foreach ($stocks as $stock) {
        expect((float) $stock->cantidad_actual)->toBe((float) $stock->lote->cantidad_disponible)->and($stock->lote->fecha_vencimiento)->not->toBeNull();
    }
});
test('transformación bloquea lotes vencidos', function () {
    $f = fixtureCorreccionesCocina();
    $lote = Lote::create(['producto_id' => $f['productoOrigen']->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'ubicacion_id' => $f['cocina']->id, 'codigo_lote' => 'REG-'.bin2hex(random_bytes(4)), 'cantidad_inicial' => 10, 'cantidad_disponible' => 10, 'costo_unitario' => 20, 'costo_total' => 200, 'estado' => 3, 'fecha_recepcion' => now()->subDays(3), 'fecha_vencimiento' => now()->subDay()]);
    $f['stockOrigen']->update(['lote_id' => $lote->id]);
    expect(fn () => app(TransformarMateriaPrimaCocina::class)->ejecutar(datosTransformacionCocina($f)))->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});
test('merma diaria de cocina descuenta su existencia física', function () {
    $f = fixtureCorreccionesCocina();
    app(RegistrarMermaCocina::class)->ejecutar(now()->toDateString(), $f['cocina']->id, [['producto_id' => $f['productoOrigen']->id, 'cantidad' => 1, 'motivo' => 'Derrame']]);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0);
});

test('el reparto de centavos conserva el costo sin producir resultados negativos', function () {
    $f = fixtureCorreccionesCocina();
    $lote = Lote::query()->create(['producto_id' => $f['productoOrigen']->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'ubicacion_id' => $f['cocina']->id, 'codigo_lote' => 'CENT-'.bin2hex(random_bytes(4)), 'cantidad_inicial' => 10, 'cantidad_disponible' => 10, 'costo_unitario' => .02, 'costo_total' => .2, 'estado' => 1, 'fecha_recepcion' => now()]);
    $f['stockOrigen']->update(['lote_id' => $lote->id]);
    $data = datosTransformacionCocina($f, .25);
    $data['items'] = array_fill(0, 4, $data['items'][0]);
    $resultado = app(TransformarMateriaPrimaCocina::class)->ejecutar($data);
    expect((float) $resultado->items()->sum('costo_asignado'))->toBe(.02)
        ->and($resultado->items()->where('costo_asignado', '<', 0)->exists())->toBeFalse();
});
