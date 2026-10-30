<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Restaurante\AreaCocina;
use App\Filament\Pages\Restaurante\BuffetsCocina;
use App\Interactors\Restaurante\Cocina\ConfirmarProduccionCocina;
use App\Interactors\Restaurante\Cocina\GestionarBuffet;
use App\Interactors\Restaurante\Cocina\ProcesarProcesoCocina;
use App\Interactors\Restaurante\Cocina\TransformarMateriaPrimaCocina;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/CocinaFixtures.php';

function buffetRegresion(): Buffet
{
    Moneda::query()->firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);

    return app(GestionarBuffet::class)->crear(['nombre' => 'Buffet mixto', 'fecha_servicio' => today()->toDateString(), 'modalidad' => 'mixto', 'precio_persona' => 100, 'precio_paquete' => 1000, 'comensales_previstos' => 12]);
}
function loteRegresionCocina(array $f, float $cantidad, float $costo = 20): Lote
{
    return Lote::query()->create(['producto_id' => $f['productoOrigen']->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'ubicacion_id' => $f['cocina']->id, 'codigo_lote' => 'LOT-'.bin2hex(random_bytes(4)), 'cantidad_inicial' => $cantidad, 'cantidad_disponible' => $cantidad, 'costo_unitario' => $costo, 'costo_total' => $cantidad * $costo, 'estado' => 1, 'fecha_recepcion' => now(), 'fecha_vencimiento' => now()->addDays(3)]);
}

test('la venta de producto repartido consume todas las filas y registra movimientos', function () {
    $f = fixtureCorreccionesCocina();
    [$pedido,$item] = pedidoCorreccionesCocina($f);
    $item->update(['plato_id' => null, 'tipo_item' => 'producto', 'producto_id' => $f['productoOrigen']->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'cantidad' => 5]);
    $lote1 = loteRegresionCocina($f, 2);
    $lote2 = loteRegresionCocina($f, 3);
    $f['stockOrigen']->update(['producto_id' => $f['productoOrigen']->id, 'lote_id' => $lote1->id, 'cantidad_actual' => 2]);
    Stock::query()->create(['stockable_type' => $f['stockOrigen']->stockable_type, 'stockable_id' => $f['cocina']->id, 'producto_id' => $f['productoOrigen']->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'lote_id' => $lote2->id, 'cantidad_ideal' => 0, 'cantidad_actual' => 3]);
    app(EnviarPedidoACocina::class)->ejecutar($pedido);
    expect((float) Stock::query()->where('producto_variante_id', $f['varianteOrigen']->id)->sum('cantidad_actual'))->toBe(0.0)
        ->and(MovimientoStock::query()->where('documento_tipo', 'pedido_item')->where('documento_id', $item->id)->count())->toBe(2)
        ->and((float) $lote1->refresh()->cantidad_disponible)->toBe(0.0)->and((float) $lote2->refresh()->cantidad_disponible)->toBe(0.0);
});

test('abrir botella convierte volumen a mililitros y mantiene costo y trazabilidad', function () {
    $f = fixtureCorreccionesCocina();
    $unidadBotella = Catalogo::query()->create(['catalogo_tipo_id' => $f['tipo']->id, 'nombre' => 'Botella', 'codigo' => 'botella-reg', 'estado' => 1]);
    $unidadMl = Catalogo::query()->create(['catalogo_tipo_id' => $f['tipo']->id, 'nombre' => 'Mililitro', 'codigo' => 'ml', 'estado' => 1]);
    $f['varianteOrigen']->update(['unidad_medida_id' => $unidadBotella->id, 'volumen' => 750]);
    $f['varianteDestino']->update(['unidad_medida_id' => $unidadMl->id]);
    $lote = loteRegresionCocina($f, 10, 150);
    $f['stockOrigen']->update(['lote_id' => $lote->id]);
    $resultado = app(TransformarMateriaPrimaCocina::class)->ejecutar(datosTransformacionCocina($f, 750));
    $salida = Stock::query()->where('producto_variante_id', $f['varianteDestino']->id)->with('lote')->firstOrFail();
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0)->and((float) $salida->cantidad_actual)->toBe(750.0)->and((float) $salida->lote->costo_unitario)->toBe(.2)->and((float) $resultado->costo_total)->toBe(150.0);
});

test('una receta de bar consume del bar y no de cocina', function () {
    $f = fixtureCorreccionesCocina();
    [$pedido,$item] = pedidoCorreccionesCocina($f);
    $item->update(['area_cocina' => AreaCocina::BAR]);
    $bar = Ubicacion::query()->create(['nombre' => 'Bar regresión', 'tipo' => 'bar', 'estado' => 1, 'orden' => 999]);
    $stockBar = Stock::query()->create(['stockable_type' => $f['stockOrigen']->stockable_type, 'stockable_id' => $bar->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'cantidad_ideal' => 0, 'cantidad_actual' => 5]);
    app(EnviarPedidoACocina::class)->ejecutar($pedido);
    expect((float) $stockBar->refresh()->cantidad_actual)->toBe(4.0)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});

test('producción confirmada genera lote y no puede volver a consumirse ni editarse', function () {
    $f = fixtureCorreccionesCocina();
    [$pedido,$item] = pedidoCorreccionesCocina($f);
    $proceso = app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'producto_origen_id' => $item->plato->producto_receta_id, 'cantidad_platos' => 1, 'items' => []]);
    $resultado = app(ConfirmarProduccionCocina::class)->ejecutar($proceso->id, $f['varianteDestino']->id, $f['cocina']->id, .8, now()->addDay()->toDateString());
    expect($resultado->getAttribute('confirmado_at'))->not->toBeNull()->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0)
        ->and((float) Stock::query()->where('lote_id', $resultado->getAttribute('lote_resultado_id'))->value('cantidad_actual'))->toBe(.8);
    expect(fn () => app(ConfirmarProduccionCocina::class)->ejecutar($proceso->id, $f['varianteDestino']->id, $f['cocina']->id, .8, now()->addDay()->toDateString()))->toThrow(DomainException::class);
    expect(fn () => app(ProcesarProcesoCocina::class)->guardar($resultado, ['cantidad_platos' => 2]))->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0);
});

test('buffet concilia servido sobrante y merma sin duplicar consumo ni retornar sobrantes', function () {
    $f = fixtureCorreccionesCocina();
    $lote = loteRegresionCocina($f, 10);
    $f['stockOrigen']->update(['lote_id' => $lote->id]);
    $buffet = buffetRegresion();
    app(GestionarBuffet::class)->abastecer($buffet->id, $f['stockOrigen']->id, 6);
    $stockBuffet = $buffet->existencias()->firstOrFail();
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(4.0)->and((float) $lote->refresh()->cantidad_disponible)->toBe(10.0);
    app(GestionarBuffet::class)->cerrar($buffet->id, 12, [['stock_id' => $stockBuffet->id, 'sobrante' => 1.0, 'merma' => .5]]);
    expect((float) $stockBuffet->refresh()->cantidad_actual)->toBe(1.0)->and((float) $lote->refresh()->cantidad_disponible)->toBe(5.0)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(4.0);
    expect(fn () => app(GestionarBuffet::class)->cerrar($buffet->id, 12, [['stock_id' => $stockBuffet->id, 'sobrante' => 1.0, 'merma' => 0.0]]))->toThrow(DomainException::class);
    app(GestionarBuffet::class)->descartarSobrantes($buffet->id, 'Disposición al cierre');
    expect((float) $stockBuffet->refresh()->cantidad_actual)->toBe(0.0)->and((float) $lote->refresh()->cantidad_disponible)->toBe(4.0);
});

test('buffet mixto carga paquetes y personas adicionales una sola vez', function () {
    $buffet = buffetRegresion();
    $cuenta = Cuenta::query()->create(['numero_cuenta' => 'BUF-'.bin2hex(random_bytes(4)), 'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO, 'estado' => EstadoCuenta::ABIERTA, 'moneda_id' => $buffet->moneda_id, 'abierta_at' => now()]);
    app(GestionarBuffet::class)->cargarCuenta($buffet->id, $cuenta->id, 2, 1);
    expect((float) $cuenta->detalles()->sum('subtotal'))->toBe(1200.0);
    expect(fn () => app(GestionarBuffet::class)->cargarCuenta($buffet->id, $cuenta->id, 2, 1))->toThrow(DomainException::class);
    expect($cuenta->detalles()->count())->toBe(1);
});

test('buffet rechaza cierre incoherente y no pierde existencias', function () {
    $f = fixtureCorreccionesCocina();
    $buffet = buffetRegresion();
    app(GestionarBuffet::class)->abastecer($buffet->id, $f['stockOrigen']->id, 2);
    $stock = $buffet->existencias()->firstOrFail();
    expect(fn () => app(GestionarBuffet::class)->cerrar($buffet->id, 1, [['stock_id' => $stock->id, 'sobrante' => 3.0, 'merma' => 0.0]]))->toThrow(DomainException::class);
    expect((float) $stock->refresh()->cantidad_actual)->toBe(2.0)->and($buffet->refresh()->estado)->toBe('abierto');
});

test('pantalla administrativa de buffet se renderiza para un usuario autorizado', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Gate::before(fn () => true);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::test(BuffetsCocina::class)->assertSuccessful()->assertSee('Buffets y eventos');
});

test('desempacar bebidas respeta el contenido configurado de la caja', function () {
    $f = fixtureCorreccionesCocina();
    $caja = Catalogo::query()->create(['catalogo_tipo_id' => $f['tipo']->id, 'nombre' => 'Caja', 'codigo' => 'caja-bebida', 'estado' => 1]);
    $lata = Catalogo::query()->create(['catalogo_tipo_id' => $f['tipo']->id, 'nombre' => 'Lata', 'codigo' => 'lata-bebida', 'estado' => 1]);
    $f['varianteOrigen']->update(['unidad_medida_id' => $caja->id, 'unidades_por_empaque' => 24, 'volumen' => 355]);
    $f['varianteDestino']->update(['unidad_medida_id' => $lata->id, 'unidades_por_empaque' => 1, 'volumen' => 355]);
    app(TransformarMateriaPrimaCocina::class)->ejecutar(datosTransformacionCocina($f, 24));
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0)
        ->and((float) Stock::query()->where('producto_variante_id', $f['varianteDestino']->id)->sum('cantidad_actual'))->toBe(24.0);
});

test('un usuario sin permiso no puede abrir la página de buffet', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => false);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::test(BuffetsCocina::class)->assertForbidden();
});

test('un registro histórico no puede confirmarse como producción ni eliminarse', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $proceso = app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'producto_origen_id' => $item->plato->producto_receta_id, 'cantidad_platos' => 1, 'items' => []]);
    $proceso->update(['tipo_registro' => 'historico']);
    expect(fn () => app(ConfirmarProduccionCocina::class)->ejecutar($proceso->id, $f['varianteDestino']->id, $f['cocina']->id, .8, now()->addDay()->toDateString()))->toThrow(DomainException::class);
    expect(fn () => $proceso->delete())->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});

test('la falta de un segundo ingrediente revierte todo el consumo de producción', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    ProductoKit::query()->create(['producto_padre_id' => $item->plato->producto_receta_id, 'producto_variante_id' => $f['varianteDestino']->id, 'cantidad' => .2]);
    $proceso = app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'producto_origen_id' => $item->plato->producto_receta_id, 'cantidad_platos' => 1, 'items' => []]);
    expect(fn () => app(ConfirmarProduccionCocina::class)->ejecutar($proceso->id, $f['varianteMerma']->id, $f['cocina']->id, .8, now()->addDay()->toDateString()))->toThrow(DomainException::class);
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0)
        ->and($proceso->refresh()->getAttribute('confirmado_at'))->toBeNull()
        ->and(MovimientoStock::query()->where('documento_tipo', 'produccion_cocina')->where('documento_id', $proceso->id)->count())->toBe(0);
});

test('el buffet no puede cargar precios de una moneda distinta a la cuenta', function () {
    $buffet = buffetRegresion();
    $otraMoneda = Moneda::query()->firstOrCreate(['codigo' => 'USD'], ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => false]);
    $cuenta = Cuenta::query()->create(['numero_cuenta' => 'BUF-USD-'.bin2hex(random_bytes(4)), 'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO, 'estado' => EstadoCuenta::ABIERTA, 'moneda_id' => $otraMoneda->id, 'abierta_at' => now()]);
    expect(fn () => app(GestionarBuffet::class)->cargarCuenta($buffet->id, $cuenta->id, 2, 1))->toThrow(DomainException::class);
    expect($cuenta->detalles()->count())->toBe(0)->and($buffet->refresh()->cargado_at)->toBeNull();
});

test('los pedidos consumen de la bodega configurada para su espacio', function () {
    $f = fixtureCorreccionesCocina();
    [$pedido, $item] = pedidoCorreccionesCocina($f);
    $bodega = Ubicacion::query()->create(['nombre' => 'Bodega asignada', 'tipo' => 'almacen', 'estado' => 1, 'orden' => 98]);
    $espacio = Espacio::query()->create(['nombre' => 'Punto de venta configurado', 'codigo' => 'CFG-'.bin2hex(random_bytes(4)), 'tipo' => 'restaurante', 'capacidad_personas' => 10, 'estado' => 1, 'ubicacion_cocina_id' => $bodega->id]);
    $pedido->update(['mesa_id' => $espacio->id]);
    $stock = Stock::query()->create(['stockable_type' => $f['stockOrigen']->stockable_type, 'stockable_id' => $bodega->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'cantidad_actual' => 5, 'cantidad_ideal' => 0]);
    app(EnviarPedidoACocina::class)->ejecutar($pedido);
    expect((float) $stock->refresh()->cantidad_actual)->toBe(4.0)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});
