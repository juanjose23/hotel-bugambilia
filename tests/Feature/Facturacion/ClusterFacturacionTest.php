<?php

declare(strict_types=1);

use App\Filament\Clusters\Cajas\CajasCluster;
use App\Filament\Clusters\Facturacion\FacturacionCluster;
use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Clusters\Tesoreria\TesoreriaCluster;
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

test('el menu principal organiza los 21 componentes financieros en 4 clusters especializados', function (): void {
    usuarioClusterFacturacion();
    $grupo = collect(Filament::getNavigation())->first(fn ($g): bool => $g->getLabel() === 'Caja & Facturación');
    expect($grupo)->not->toBeNull()->and(collect($grupo->getItems())->map(fn ($i): string => $i->getLabel())->values()->all())->toBe([
        'Cajas y Turnos',
        'Facturación y Cobros',
        'Tesorería y Bancos',
        'Configuración Fiscal',
    ]);
    expect(CajasCluster::getClusteredComponents())->toHaveCount(5);
    expect(FacturacionCluster::getClusteredComponents())->toHaveCount(5);
    expect(TesoreriaCluster::getClusteredComponents())->toHaveCount(6);
    expect(FacturacionConfigCluster::getClusteredComponents())->toHaveCount(5);
});

test('cada cluster reune sus secciones y sus enlaces son canonicos', function (): void {
    usuarioClusterFacturacion();

    $this->get(CajasCluster::getUrl())->assertOk()
        ->assertSee('Operaciones de caja')->assertSee('Control y cierres')
        ->assertSee(MiTurnoCaja::getUrl(), false);

    $this->get(FacturacionCluster::getUrl())->assertOk()
        ->assertSee('Documentos y ventas')->assertSee('Cobros y pagos')
        ->assertSee(CuentaResource::getUrl(), false)->assertSee(FacturaResource::getUrl(), false);

    $this->get(TesoreriaCluster::getUrl())->assertOk()
        ->assertSee('Fondos y cuentas')->assertSee('Conciliaciones');

    $this->get(FacturacionConfigCluster::getUrl())->assertOk()
        ->assertSee('Resoluciones y folios')->assertSee('Reglas y pasarelas');

    expect(CuentaResource::getUrl())->toContain('/admin/facturacion/')
        ->and(MiTurnoCaja::getUrl())->toContain('/admin/cajas/');
});

test('cada usuario ve solamente los clusters y opciones financieras que puede utilizar', function (): void {
    $user = usuarioClusterFacturacion(false);
    Permission::findOrCreate('Operar:Caja', 'web');
    $user->givePermissionTo('Operar:Caja');

    expect(CajasCluster::canAccess())->toBeTrue()
        ->and(FacturacionConfigCluster::canAccess())->toBeFalse();

    $this->get(CajasCluster::getUrl())->assertOk()->assertSee('Mi turno de caja');
    $this->get(FacturacionConfigCluster::getUrl())->assertForbidden();
});

test('un usuario sin opciones financieras no puede abrir los clusters', function (): void {
    usuarioClusterFacturacion(false);
    expect(CajasCluster::canAccess())->toBeFalse()
        ->and(FacturacionCluster::canAccess())->toBeFalse()
        ->and(TesoreriaCluster::canAccess())->toBeFalse()
        ->and(FacturacionConfigCluster::canAccess())->toBeFalse();

    $this->get(CajasCluster::getUrl())->assertForbidden();
    $this->get(FacturacionCluster::getUrl())->assertForbidden();
    $this->get(TesoreriaCluster::getUrl())->assertForbidden();
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
