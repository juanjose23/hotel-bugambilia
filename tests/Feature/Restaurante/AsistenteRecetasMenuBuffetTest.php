<?php

declare(strict_types=1);

use App\Filament\Pages\Restaurante\BuffetsCocina;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages\CreateProcesoCocina;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages\EditProcesoCocina;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages\ListProcesosCocina;
use App\Interactors\Restaurante\Cocina\ConfirmarProduccionCocina;
use App\Interactors\Restaurante\Cocina\GestionarBuffet;
use App\Interactors\Restaurante\Cocina\OrganizarServicioBuffet;
use App\Interactors\Restaurante\Cocina\ProcesarProcesoCocina;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\GuiaRecetaCocinaQuery;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/CocinaFixtures.php';

function datosServicioGuiado(): array
{
    $moneda = Moneda::firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);

    return ['nombre' => 'Buffet con menú', 'fecha_servicio' => today()->toDateString(), 'modalidad' => 'mixto', 'moneda_id' => $moneda->id, 'precio_persona' => 100, 'precio_paquete' => 1000, 'comensales_previstos' => 10];
}

function menuServicioGuiado(array $f, int $platoId): array
{
    return [['plato_id' => $platoId, 'producto_variante_id' => $f['varianteDestino']->id, 'porciones_previstas' => 10]];
}

test('la guía escala la receta y muestra los pasos registrados y faltantes', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $item->plato->update(['instrucciones_preparacion' => "Pesar el insumo\nSeguir la ficha aprobada\nContar el resultado"]);
    $guia = app(GuiaRecetaCocinaQuery::class)->ejecutar($item->plato_id, 12, $f['cocina']->id);
    expect($guia['ingredientes'][0]['necesaria'])->toBe(12.0)->and($guia['ingredientes'][0]['disponible'])->toBe(10.0)->and($guia['ingredientes'][0]['faltante'])->toBe(2.0)->and($guia['pasos'])->toHaveCount(3);
});

test('la planificación genera ingredientes desde la receta sin consumir existencias', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $plan = app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'cantidad_platos' => 2, 'items' => [['producto_destino_id' => $f['productoMerma']->id, 'cantidad' => 999, 'costo_asignado' => 999]]]);
    expect($plan->items()->count())->toBe(1)->and((float) $plan->items()->firstOrFail()->cantidad)->toBe(2.0)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});

test('el servidor rechaza una receta intercambiada y porciones fraccionarias', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    expect(fn () => app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'producto_origen_id' => $f['productoMerma']->id, 'cantidad_platos' => 1]))->toThrow(DomainException::class);
    expect(fn () => app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'cantidad_platos' => 1.5]))->toThrow(DomainException::class);
    expect(ProcesoCocina::count())->toBe(0);
});

test('el asistente de recetas guarda un borrador y lleva a confirmar la preparación', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    Espacio::create(['nombre' => 'Restaurante guía', 'codigo' => 'GUIA-'.bin2hex(random_bytes(4)), 'tipo' => 'restaurante', 'estado' => 1]);
    Livewire::test(CreateProcesoCocina::class)->set('data.plato_id', $item->plato_id)->set('data.producto_origen_id', $item->plato->producto_receta_id)->set('data.cantidad_platos', 2)->call('create')->assertHasNoFormErrors()->assertRedirect();
    expect(ProcesoCocina::where('plato_id', $item->plato_id)->firstOrFail()->tipo_registro)->toBe('borrador')->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});

test('autoservicio y mesero comparten menú y conservan la modalidad de cobro', function (string $servicio) {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $buffet = app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), $servicio, menuServicioGuiado($f, $item->plato_id));
    expect($buffet->tipo_servicio)->toBe($servicio)->and($buffet->modalidad)->toBe('mixto')->and($buffet->menu()->count())->toBe(1)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
})->with(['autoservicio', 'mesero', 'mixto']);

test('la reposición acepta solo el producto terminado del menú y no descuenta otra vez ingredientes', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $b = app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), 'autoservicio', menuServicioGuiado($f, $item->plato_id));
    expect(fn () => app(GestionarBuffet::class)->abastecer($b->id, $f['stockOrigen']->id, 1))->toThrow(DomainException::class);
    $terminado = Stock::create(['stockable_type' => $f['stockOrigen']->stockable_type, 'stockable_id' => $f['cocina']->id, 'producto_variante_id' => $f['varianteDestino']->id, 'cantidad_actual' => 3, 'cantidad_ideal' => 0]);
    app(GestionarBuffet::class)->abastecer($b->id, $terminado->id, 1);
    expect((float) $terminado->refresh()->cantidad_actual)->toBe(2.0)->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
    $stock = $b->existencias()->firstOrFail();
    app(GestionarBuffet::class)->cerrar($b->id, 10, [['stock_id' => $stock->id, 'sobrante' => .2, 'merma' => .1]]);
    expect($b->refresh()->estado)->toBe('cerrado')->and((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0);
});

test('un menú inválido revierte la creación completa del buffet', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $menu = menuServicioGuiado($f, $item->plato_id);
    $menu[] = $menu[0];
    expect(fn () => app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), 'mesero', $menu))->toThrow(DomainException::class);
    expect(Buffet::count())->toBe(0);
    $menu[0]['producto_variante_id'] = $f['varianteOrigen']->id;
    expect(fn () => app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), 'mesero', [$menu[0]]))->toThrow(DomainException::class);
    expect(Buffet::count())->toBe(0);
});

test('la guía de buffet explica quién sirve y muestra los platillos seleccionados', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $anterior = app(GestionarBuffet::class)->crear(datosServicioGuiado());
    Livewire::test(BuffetsCocina::class)->assertActionHidden(TestAction::make('abastecer')->table($anterior))->assertActionVisible(TestAction::make('organizarMenu')->table($anterior));
    $b = app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), 'mesero', menuServicioGuiado($f, $item->plato_id));
    Livewire::test(BuffetsCocina::class)->assertActionVisible(TestAction::make('abastecer')->table($b))->mountAction(TestAction::make('verMenu')->table($b))->call('forceRender')->assertSee('El mesero sirve las porciones')->assertSee($item->plato->nombre);
});

test('el formulario de buffet guarda platillos y quién sirve separados del precio', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $datos = datosServicioGuiado() + ['tipo_servicio' => 'autoservicio', 'menu' => menuServicioGuiado($f, $item->plato_id)];
    Livewire::test(BuffetsCocina::class)->mountAction(TestAction::make('crear')->table())->fillForm($datos)->callMountedAction()->assertHasNoFormErrors();
    $buffet = Buffet::firstOrFail();
    expect($buffet->tipo_servicio)->toBe('autoservicio')->and($buffet->modalidad)->toBe('mixto')->and($buffet->menu()->firstOrFail()->plato_id)->toBe($item->plato_id);
});

test('el menú no pierde platillos que ya tienen alimentos enviados', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    $buffet = app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), 'mesero', menuServicioGuiado($f, $item->plato_id));
    $stock = Stock::create(['stockable_type' => $f['stockOrigen']->stockable_type, 'stockable_id' => $f['cocina']->id, 'producto_variante_id' => $f['varianteDestino']->id, 'cantidad_actual' => 3, 'cantidad_ideal' => 0]);
    app(GestionarBuffet::class)->abastecer($buffet->id, $stock->id, 1);
    $otro = menuServicioGuiado($f, $item->plato_id);
    $otro[0]['producto_variante_id'] = $f['varianteMerma']->id;
    expect(fn () => app(OrganizarServicioBuffet::class)->configurar($buffet->id, 'autoservicio', $otro))->toThrow(DomainException::class);
    expect($buffet->menu()->firstOrFail()->producto_variante_id)->toBe($f['varianteDestino']->id)->and($buffet->refresh()->tipo_servicio)->toBe('mesero');
});

test('cambiar porciones exige guardar el plan antes de confirmar', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    Espacio::create(['nombre' => 'Restaurante guía', 'codigo' => 'GUIA-'.bin2hex(random_bytes(4)), 'tipo' => 'restaurante', 'estado' => 1]);
    $plan = app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'cantidad_platos' => 1]);
    Livewire::test(EditProcesoCocina::class, ['record' => $plan->id])->set('data.cantidad_platos', 2)->mountAction('confirmarProduccion')->assertNotified('Guarda primero los cambios de receta o porciones');
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(10.0)->and($plan->refresh()->confirmado_at)->toBeNull();
});

test('la migración protege instrucciones y menús registrados al intentar revertir', function () {
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    app(OrganizarServicioBuffet::class)->crear(datosServicioGuiado(), 'mesero', menuServicioGuiado($f, $item->plato_id));
    $migration = require base_path('database/migrations/Restaurante/2026_10_29_000001_add_guided_recipes_and_buffet_menu.php');
    expect(fn () => $migration->down())->toThrow(RuntimeException::class);
    expect(Schema::hasTable('restaurante_buffet_platos'))->toBeTrue();
});

test('la preparación confirmada se consulta sin ofrecer guardar otra planificación', function () {
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $f = fixtureCorreccionesCocina();
    [, $item] = pedidoCorreccionesCocina($f);
    Espacio::create(['nombre' => 'Restaurante guía', 'codigo' => 'GUIA-'.bin2hex(random_bytes(4)), 'tipo' => 'restaurante', 'estado' => 1]);
    $plan = app(ProcesarProcesoCocina::class)->guardar(null, ['plato_id' => $item->plato_id, 'cantidad_platos' => 1]);
    app(ConfirmarProduccionCocina::class)->ejecutar($plan->id, $f['varianteDestino']->id, $f['cocina']->id, .8, today()->addDay()->toDateString());
    $page = Livewire::test(EditProcesoCocina::class, ['record' => $plan->id])->assertSuccessful()->assertFormFieldIsDisabled('plato_id');
    $dom = new DOMDocument;
    @$dom->loadHTML($page->html());
    $botones = (new DOMXPath($dom))->query('//button[normalize-space(.) = "Guardar planificación"]');
    expect($botones->length)->toBe(0);
    Livewire::test(ListProcesosCocina::class)->assertSee('Preparación terminada')->assertSee('Producto terminado');
});
