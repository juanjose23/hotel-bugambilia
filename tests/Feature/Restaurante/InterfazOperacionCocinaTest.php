<?php

declare(strict_types=1);

use App\Filament\Pages\Restaurante\BuffetsCocina;
use App\Filament\Pages\Restaurante\ControlCocina;
use App\Filament\Pages\Restaurante\MateriaPrimaCocina;
use App\Interactors\Restaurante\Cocina\GestionarBuffet;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\ControlOperacionCocinaQuery;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/CocinaFixtures.php';

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('el panel de cocina rechaza usuarios sin permiso', function () {
    Gate::before(fn () => false);
    Livewire::test(ControlCocina::class)->assertForbidden();
});

test('el panel muestra existencias por unidad y avisa faltantes', function () {
    Gate::before(fn () => true);
    $f = fixtureCorreccionesCocina();
    $f['stockOrigen']->update(['cantidad_ideal' => 20]);
    Livewire::test(ControlCocina::class)->assertSuccessful()->assertSee('Tu cocina, bajo control.')->assertSee($f['productoOrigen']->nombre)->assertSee('Reponer hasta');
});

test('las opciones de preparación corresponden a la bodega seleccionada', function () {
    $f = fixtureCorreccionesCocina();
    $otra = Ubicacion::create(['nombre' => 'Bar sin insumos', 'tipo' => 'bar', 'estado' => 1, 'orden' => 99]);
    $q = app(ControlOperacionCocinaQuery::class);
    expect($q->productos($otra->id))->toBe([])->and($q->productos($f['cocina']->id))->toHaveKey($f['productoOrigen']->id)
        ->and($q->variantes($f['productoOrigen']->id, $otra->id))->toBe([]);
});

test('el asistente guarda una preparación y actualiza existencias', function () {
    Gate::before(fn () => true);
    $f = fixtureCorreccionesCocina();
    Espacio::create(['nombre' => 'Restaurante prueba', 'codigo' => 'UI-'.bin2hex(random_bytes(4)), 'tipo' => 'restaurante', 'estado' => 1]);
    $unidad = Catalogo::create(['catalogo_tipo_id' => $f['tipo']->id, 'nombre' => 'Unidad', 'codigo' => 'und-'.bin2hex(random_bytes(4)), 'estado' => 1]);
    $f['varianteDestino']->update(['unidad_medida_id' => $unidad->id, 'peso' => 200]);
    $data = datosTransformacionCocina($f, 4);
    Livewire::test(MateriaPrimaCocina::class)->set('data', $data)->call('guardar')->assertHasNoFormErrors()->assertRedirect(MateriaPrimaCocina::getUrl());
    expect((float) $f['stockOrigen']->refresh()->cantidad_actual)->toBe(9.0);
    expect((float) Stock::query()->where('producto_variante_id', $f['varianteDestino']->id)->sum('cantidad_actual'))->toBe(4.0);
});

test('el cierre de buffet exige medir sobrantes y desperdicios explícitamente', function () {
    Gate::before(fn () => true);
    $f = fixtureCorreccionesCocina();
    $m = Moneda::firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);
    $b = app(GestionarBuffet::class)->crear(['nombre' => 'Buffet para medir', 'fecha_servicio' => today()->toDateString(), 'modalidad' => 'mixto', 'moneda_id' => $m->id, 'precio_persona' => 100, 'precio_paquete' => 1000, 'comensales_previstos' => 10]);
    app(GestionarBuffet::class)->abastecer($b->id, $f['stockOrigen']->id, 2);
    $page = Livewire::test(BuffetsCocina::class)->mountAction(TestAction::make('cerrar')->table($b));
    $rows = $page->get('mountedActions.0.data.mediciones');
    $key = array_key_first($rows);
    $page->callMountedAction()->assertHasFormErrors(["mediciones.$key.sobrante" => 'required', "mediciones.$key.merma" => 'required']);
    expect($b->refresh()->estado)->toBe('abierto');
    $page->fillForm(['comensales' => 10, 'mediciones' => [$key => ['stock_id' => $b->existencias()->firstOrFail()->id, 'sobrante' => 0, 'merma' => 0]]])->callMountedAction()->assertHasNoFormErrors();
    expect($b->refresh()->estado)->toBe('cerrado');
});
