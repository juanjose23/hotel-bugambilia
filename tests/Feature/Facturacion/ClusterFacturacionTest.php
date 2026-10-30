<?php

declare(strict_types=1);

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Pages\Tesoreria\ComprobantesTransferencia;
use App\Filament\Pages\Tesoreria\MiTurnoCaja;
use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Resources\Cuentas\CuentaResource\Pages\ViewCuenta;
use App\Filament\Resources\Facturacion\FacturaResource\FacturaResource;
use App\Filament\Resources\Facturacion\FacturaSerieResource\Pages\ListFacturaSeries;
use App\Repository\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function usuarioClusterFacturacion(bool $superAdmin = true): User
{
    config(['tesoreria.habilitado' => true]);
    $user = User::factory()->create(['is_admin' => true]);
    if ($superAdmin) {
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));
    }
    test()->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    return $user;
}

test('el menu principal contiene un solo acceso para los 21 componentes financieros', function (): void {
    usuarioClusterFacturacion();
    $grupo = collect(Filament::getNavigation())->first(fn ($g): bool => $g->getLabel() === 'Caja & Facturación');
    expect($grupo)->not->toBeNull()->and(collect($grupo->getItems())->map(fn ($i): string => $i->getLabel())->values()->all())->toBe(['Facturación y caja']);
    expect(FacturacionConfigCluster::getClusteredComponents())->toHaveCount(21);
});

test('el inicio reune cobros caja bancos y configuracion y sus enlaces son canonicos', function (): void {
    usuarioClusterFacturacion();
    $this->get(FacturacionConfigCluster::getUrl())->assertOk()
        ->assertSee('Cobros y documentos')->assertSee('Caja y cierres')
        ->assertSee('Fondos y bancos')->assertSee('Configuración fiscal')
        ->assertSee(CuentaResource::getUrl(), false)->assertSee(FacturaResource::getUrl(), false)
        ->assertSee(MiTurnoCaja::getUrl(), false);
    expect(CuentaResource::getUrl())->toContain('/admin/facturacion/')
        ->and(MiTurnoCaja::getUrl())->toContain('/admin/facturacion/');
});

test('cada usuario ve solamente las opciones financieras que puede utilizar', function (): void {
    $user = usuarioClusterFacturacion(false);
    Permission::findOrCreate('Operar:Caja', 'web');
    $user->givePermissionTo('Operar:Caja');
    $this->get(FacturacionConfigCluster::getUrl())->assertOk()->assertSee('Mi turno de caja')
        ->assertDontSee('Autorizaciones DGI')->assertDontSee('Fondos y bancos');
    expect(FacturacionConfigCluster::canAccess())->toBeTrue();
});

test('un usuario sin opciones financieras no puede abrir el cluster', function (): void {
    usuarioClusterFacturacion(false);
    expect(FacturacionConfigCluster::canAccess())->toBeFalse();
    $this->get(FacturacionConfigCluster::getUrl())->assertForbidden();
});

test('los accesos anteriores redirigen sin perder el documento ni la autenticacion', function (string $anterior, string $componente, array $parametros): void {
    usuarioClusterFacturacion();
    $this->get($anterior)->assertRedirect($componente::getUrl($parametros));
})->with([
    'comprobantes' => ['/admin/tesoreria/comprobantes', ComprobantesTransferencia::class, []],
    'documento de cuenta' => ['/admin/cuentas/42', ViewCuenta::class, ['record' => 42]],
    'series' => ['/admin/facturacion-config/facturacion/series', ListFacturaSeries::class, []],
    'inicio antiguo' => ['/admin/facturacion-config', FacturacionConfigCluster::class, []],
]);

test('los accesos anteriores mantienen la proteccion de inicio de sesion', function (): void {
    $this->get('/admin/tesoreria/comprobantes')->assertRedirect();
    expect(auth()->check())->toBeFalse();
});

test('los accesos guardados conservan los filtros de busqueda', function (): void {
    usuarioClusterFacturacion();
    $this->get('/admin/cuentas?tableSearch=RES-001')->assertRedirect(CuentaResource::getUrl('index', ['tableSearch' => 'RES-001']));
});
