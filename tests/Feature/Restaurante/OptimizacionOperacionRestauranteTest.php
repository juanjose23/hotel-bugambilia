<?php

declare(strict_types=1);

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Pages\Restaurante\CocinaPedidos;
use App\Filament\Pages\Restaurante\PantallaPedidos;
use App\Filament\Resources\Restaurante\PedidoResource\Pages\ListPedidos;
use App\Interactors\Restaurante\Reportes\GenerarReporteRestaurante;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\ObtenerOpcionesSustitutosQuery;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMapaMesasQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerIngredientesPedidoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosCocinaQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosPantallaQuery;
use App\Repository\Queries\Restaurante\Reportes\ObtenerReportesRestauranteQuery;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../../Support/CocinaFixtures.php';

function pedidoOptimizacionRestaurante(EstadoPedido $estado = EstadoPedido::ABIERTO, float $subtotal = 10): Pedido
{
    return Pedido::withoutEvents(fn () => Pedido::query()->create(['codigo' => 'OPT-'.str()->random(8), 'estado' => $estado, 'subtotal' => $subtotal]));
}

function accesoOptimizacionRestaurante(): Espacio
{
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::actingAs($user);

    return Espacio::query()->create(['nombre' => 'Restaurante optimizacion', 'codigo' => 'OPT-REST', 'tipo' => 'restaurante', 'estado' => 1]);
}

function productoOptimizacionRestaurante(): Producto
{
    $tipo = CatalogoTipo::query()->create(['codigo' => 'OPT-TIPO', 'nombre' => 'Catalogo prueba', 'estado' => 1]);
    $cat = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'codigo' => 'OPT-CAT', 'nombre' => 'Bebidas', 'estado' => 1]);

    return Producto::query()->create(['codigo' => 'OPT-PRD', 'nombre' => 'Aceite de prueba', 'categoria_id' => $cat->id, 'unidad_medida_id' => $cat->id, 'tipo' => 1, 'estado' => 1]);
}

test('cocina filtra partidas por area en SQL y conserva los pedidos prepagados', function (): void {
    $pedido = pedidoOptimizacionRestaurante(EstadoPedido::PAGADO);
    $bar = $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'area_cocina' => 'bar', 'estado' => 2]);
    $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'area_cocina' => 'cocina', 'estado' => 2]);
    $pedidos = app(ObtenerPedidosCocinaQuery::class)->ejecutar('bar');
    expect($pedidos->pluck('id')->all())->toBe([$pedido->id])->and($pedidos->first()->items->pluck('id')->all())->toBe([$bar->id]);
    expect(app(ObtenerPedidosCocinaQuery::class)->ejecutar('parrilla'))->toHaveCount(0);
});

test('los cancelados no aparecen en cocina ni en despacho aunque conserven partidas pendientes', function (): void {
    $cancelado = pedidoOptimizacionRestaurante(EstadoPedido::CANCELADO);
    $cancelado->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => 1]);
    expect(app(ObtenerPedidosCocinaQuery::class)->ejecutar())->toHaveCount(0);
    $pantalla = app(ObtenerPedidosPantallaQuery::class)->ejecutar();
    expect($pantalla['enPreparacion'])->toHaveCount(0)->and($pantalla['listos'])->toHaveCount(0);
});

test('los bloqueados por stock siguen visibles y no se anuncian como listos', function (): void {
    $pedido = pedidoOptimizacionRestaurante();
    $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => EstadoItemPedido::BLOQUEADO_STOCK]);
    $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => EstadoItemPedido::LISTO]);
    expect(app(ObtenerPedidosCocinaQuery::class)->ejecutar()->pluck('id')->all())->toBe([$pedido->id]);
    $pantalla = app(ObtenerPedidosPantallaQuery::class)->ejecutar();
    expect($pantalla['enPreparacion'])->toHaveCount(1)->and($pantalla['listos'])->toHaveCount(0);
});

test('la pantalla de despacho carga tambien productos y variantes sin consultas por partida', function (): void {
    $producto = productoOptimizacionRestaurante();
    $pedido = pedidoOptimizacionRestaurante();
    $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => 1, 'producto_id' => $producto->id, 'tipo_item' => 'producto']);
    $item = app(ObtenerPedidosPantallaQuery::class)->ejecutar()['enPreparacion']->first()->items->first();
    expect($item->relationLoaded('producto'))->toBeTrue()->and($item->relationLoaded('variante'))->toBeTrue()->and($item->producto->id)->toBe($producto->id);
});

test('el mapa reconoce la comanda lista como activa', function (): void {
    $restaurante = accesoOptimizacionRestaurante();
    $mesa = Espacio::query()->create(['nombre' => 'Mesa lista', 'codigo' => 'OPT-MESA', 'tipo' => 'mesa', 'estado' => 5, 'padre_id' => $restaurante->id]);
    $pedido = pedidoOptimizacionRestaurante(EstadoPedido::LISTO);
    $pedido->update(['mesa_id' => $mesa->id]);
    $mapa = app(ObtenerMapaMesasQuery::class)->ejecutar();
    expect($mapa['mesas']->firstWhere('id', $mesa->id)->cuentas_activas_count)->toBe(1)
        ->and($mapa['mesas']->firstWhere('id', $mesa->id)->pedido_abierto_id)->toBe($pedido->id);
});

test('cada refresco de cocina ejecuta una sola consulta de seleccion de comandas', function (): void {
    accesoOptimizacionRestaurante();
    $pedido = pedidoOptimizacionRestaurante();
    $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => 1]);
    $page = Livewire::test(CocinaPedidos::class)->assertSuccessful();
    DB::enableQueryLog();
    DB::flushQueryLog();
    $page->call('cargarPedidos')->assertSuccessful();
    $queries = collect(DB::getQueryLog())->filter(fn (array $q): bool => str_contains($q['query'], 'from "pedidos"') && str_contains($q['query'], 'exists ('));
    DB::disableQueryLog();
    expect($queries)->toHaveCount(1);
});

test('cocina avisa de la primera comanda nueva despues de estar vacia', function (): void {
    accesoOptimizacionRestaurante();
    $page = Livewire::test(CocinaPedidos::class)->assertNotDispatched('nuevo-pedido-audio');
    $pedido = pedidoOptimizacionRestaurante();
    $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => 1]);
    $page->call('cargarPedidos')->assertDispatched('nuevo-pedido-audio')
        ->call('cargarPedidos')->assertNotDispatched('nuevo-pedido-audio');
});

test('despacho anuncia una comanda lista una vez y no cada cuatro segundos', function (): void {
    accesoOptimizacionRestaurante();
    $pedido = pedidoOptimizacionRestaurante();
    $item = $pedido->items()->create(['cantidad' => 1, 'precio_unitario' => 10, 'estado' => 2]);
    $page = Livewire::test(PantallaPedidos::class)->assertNotDispatched('pedido-listo-audio');
    $item->update(['estado' => 3]);
    $page->call('cargarPedidos')->assertDispatched('pedido-listo-audio')
        ->call('cargarPedidos')->assertNotDispatched('pedido-listo-audio');
});

test('reportes agregan articulos y excluyen cancelaciones anulaciones sin borrar historicos', function (): void {
    $producto = productoOptimizacionRestaurante();
    $plato = Plato::query()->create(['codigo' => 'OPT-PL', 'nombre' => 'Platillo retirado', 'categoria_id' => $producto->categoria_id, 'estado' => 1]);
    $pedido = pedidoOptimizacionRestaurante(EstadoPedido::PAGADO, 30);
    foreach ([1, 2] as $cantidad) {
        $pedido->items()->create(['plato_id' => $plato->id, 'cantidad' => $cantidad, 'precio_unitario' => 10, 'estado' => 4]);
    }
    $pedido->items()->create(['plato_id' => $plato->id, 'cantidad' => 1, 'precio_unitario' => 1000, 'estado' => 5]);
    $cancelado = pedidoOptimizacionRestaurante(EstadoPedido::CANCELADO, 9000);
    $cancelado->items()->create(['plato_id' => $plato->id, 'cantidad' => 1, 'precio_unitario' => 9000, 'estado' => 1]);
    $plato->delete();
    $fecha = today()->toDateString();
    expect(app(ObtenerReportesRestauranteQuery::class)->obtenerItemsPorRango($fecha, $fecha))->toHaveCount(1);
    $report = app(GenerarReporteRestaurante::class)->ejecutar($fecha, $fecha);
    expect($report['resumen']['total_facturado'])->toBe(30.0)->and($report['topPlatos'][0]['cantidad'])->toBe(3.0)
        ->and($report['topPlatos'][0]['plato'])->toBe('Platillo retirado');
});

test('los reportes distinguen las presentaciones del mismo producto', function (): void {
    $producto = productoOptimizacionRestaurante();
    $pedido = pedidoOptimizacionRestaurante(EstadoPedido::PAGADO, 30);
    foreach (['1 litro', '5 litros'] as $i => $nombre) {
        $variante = ProductoVariante::query()->create(['producto_id' => $producto->id, 'codigo' => 'OPT-V-'.$i, 'nombre_variante' => $nombre, 'estado' => 1]);
        $pedido->items()->create(['producto_id' => $producto->id, 'producto_variante_id' => $variante->id, 'tipo_item' => 'producto', 'cantidad' => 1, 'precio_unitario' => 10 + $i * 10, 'estado' => 4]);
    }
    $fecha = today()->toDateString();
    $report = app(GenerarReporteRestaurante::class)->ejecutar($fecha, $fecha);
    expect($report['topPlatos'])->toHaveCount(2)->and(array_column($report['topPlatos'], 'plato'))->toContain('Aceite de prueba / 1 litro', 'Aceite de prueba / 5 litros');
});

test('los sustitutos se consultan por paginas y la busqueda encuentra opciones fuera de la primera lista', function (): void {
    $producto = productoOptimizacionRestaurante();
    $variantes = [];
    for ($i = 0; $i < 55; $i++) {
        $variantes[] = ProductoVariante::query()->create(['producto_id' => $producto->id, 'codigo' => 'OPT-V-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT), 'nombre_variante' => 'Presentacion '.$i, 'estado' => 1]);
    }
    $q = app(ObtenerOpcionesSustitutosQuery::class);
    expect($q->buscar($variantes[0]->id))->toHaveCount(50)
        ->and($q->buscar($variantes[0]->id, 'OPT-V-054'))->toHaveKey($variantes[54]->id)
        ->and($q->etiqueta($variantes[0]->id, $variantes[0]->id))->toBeNull();
});

test('el area asignada a la partida prevalece sobre la del platillo y no duplica el trabajo', function (): void {
    $producto = productoOptimizacionRestaurante();
    $plato = Plato::query()->create(['codigo' => 'OPT-AREA', 'nombre' => 'Especial', 'categoria_id' => $producto->categoria_id, 'area_cocina' => 'cocina', 'estado' => 1]);
    $pedido = pedidoOptimizacionRestaurante();
    $asignado = $pedido->items()->create(['plato_id' => $plato->id, 'cantidad' => 1, 'precio_unitario' => 10, 'area_cocina' => 'bar', 'estado' => 1]);
    $heredado = $pedido->items()->create(['plato_id' => $plato->id, 'cantidad' => 1, 'precio_unitario' => 10, 'estado' => 1]);
    expect(app(ObtenerPedidosCocinaQuery::class)->ejecutar('bar')->first()->items->pluck('id')->all())->toBe([$asignado->id])
        ->and(app(ObtenerPedidosCocinaQuery::class)->ejecutar('cocina')->first()->items->pluck('id')->all())->toBe([$heredado->id]);
});

test('obtener la receta no vuelve a consultar existencias que sus consumidores ya validan', function (): void {
    $f = fixtureCorreccionesCocina();
    [$pedido, $item] = pedidoCorreccionesCocina($f);
    DB::enableQueryLog();
    DB::flushQueryLog();
    $data = app(ObtenerIngredientesPedidoQuery::class)->ejecutar($item);
    $lecturasStock = collect(DB::getQueryLog())->filter(fn (array $q): bool => str_contains($q['query'], 'from "stocks"'));
    DB::disableQueryLog();
    expect($data['ingredientes'])->not->toBeEmpty()->and($lecturasStock)->toHaveCount(0)
        ->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});

test('el listado distingue el consumo del pedido del total de una cuenta compartida', function (): void {
    accesoOptimizacionRestaurante();
    $cuenta = Cuenta::query()->create(['numero_cuenta' => 'OPT-CTA', 'abierta_at' => now(), 'estado' => 2, 'total' => 150, 'saldo' => 150]);
    $pedido = pedidoOptimizacionRestaurante(EstadoPedido::ABIERTO, 10);
    $pedido->update(['cuenta_id' => $cuenta->id]);
    $page = Livewire::test(ListPedidos::class)
        ->assertSuccessful()->assertTableColumnStateSet('subtotal', 10, record: $pedido)
        ->assertTableColumnDoesNotExist('total');
    expect($page->instance()->getTable()->getColumn('cuenta.total')->isToggledHiddenByDefault())->toBeTrue();
});
