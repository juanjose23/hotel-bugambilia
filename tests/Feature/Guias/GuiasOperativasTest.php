<?php

declare(strict_types=1);

use App\Filament\Pages\Restaurante\BuffetsCocina;
use App\Filament\Pages\Tesoreria\MiTurnoCaja;
use App\Filament\Shared\Actions\VerGuiaAction;
use App\Filament\Support\Guias\CatalogoGuiasOperativas;
use App\Repository\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

function operadorGuias(): User
{
    config(['tesoreria.habilitado' => true]);
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));
    test()->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    return $user;
}

test('el catalogo solo resuelve los ambitos registrados y prioriza la pantalla concreta', function (): void {
    $catalogo = app(CatalogoGuiasOperativas::class);
    expect($catalogo->paraPagina(['PaginaInexistente']))->toBeNull()
        ->and($catalogo->paraPagina([MiTurnoCaja::class, BuffetsCocina::class])['id'])->toBe('caja-turno');
});

test('todas las guias apuntan a clases existentes y tienen pasos y dialogos coherentes', function (): void {
    $guias = config('guias_operativas.paginas');
    expect($guias)->toHaveCount(38);
    $ids = [];
    foreach ($guias as $clase => $guia) {
        expect(class_exists($clase))->toBeTrue();
        $ids[] = $guia['id'];
        foreach ([$guia, ...array_values($guia['dialogos'] ?? [])] as $recorrido) {
            expect($recorrido['titulo'])->toBeString()->not->toBeEmpty()
                ->and(count($recorrido['pasos']))->toBeGreaterThanOrEqual(3);
            foreach ($recorrido['pasos'] as $paso) {
                expect($paso['titulo'])->toBeString()->not->toBeEmpty()
                    ->and($paso['descripcion'])->toBeString()->not->toBeEmpty();
            }
        }
        foreach ($guia['dialogos'] ?? [] as $id => $dialogo) {
            expect($dialogo['id'])->toBe($id);
        }
    }
    expect(array_unique($ids))->toHaveCount(count($ids));
});

test('la ayuda del formulario es una accion local sin envio al servidor', function (): void {
    $accion = VerGuiaAction::make('caja-apertura');
    expect($accion->getAlpineClickHandler())->toContain('hotel:guia')
        ->and($accion->getExtraAttributes()['data-hotel-guide-dialog'])->toBe('caja-apertura')
        ->and($accion->getActionFunction())->toBeNull();
});

test('caja y buffet muestran su ayuda contextual a los usuarios autorizados', function (string $clase, string $id): void {
    operadorGuias();
    $this->get($clase::getUrl())->assertOk()->assertSee('Guía paso a paso')
        ->assertSee('data-hotel-guide', false)->assertSee($id);
})->with([
    'caja' => [MiTurnoCaja::class, 'caja-apertura'],
    'buffet' => [BuffetsCocina::class, 'buffet-crear'],
]);

test('la guia no permite el acceso a paginas financieras sin permiso', function (): void {
    config(['tesoreria.habilitado' => true]);
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user)->get(MiTurnoCaja::getUrl())->assertForbidden()->assertDontSee('data-hotel-guide', false);
});
