<?php

declare(strict_types=1);

use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\Tesoreria\FondoTesoreria;
use App\Repository\Models\User;
use Database\Seeders\Tesoreria\TesoreriaRolesSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('prepara perfiles sin usuarios saldos ni pérdida de permisos existentes', function () {
    $rol = Role::create(['name' => 'recepcionista', 'guard_name' => 'web']);
    $previo = Permission::create(['name' => 'Permiso:Existente', 'guard_name' => 'web']);
    $rol->givePermissionTo($previo);
    $usuarios = User::count();
    $this->seed(TesoreriaRolesSeeder::class);
    expect($rol->refresh()->hasPermissionTo('Permiso:Existente'))->toBeTrue()->and($rol->hasPermissionTo('Operar:Caja'))->toBeTrue()->and($rol->hasPermissionTo('Aprobar:Devolucion'))->toBeFalse()->and(User::count())->toBe($usuarios);
    expect(FondoTesoreria::count())->toBe(0)->and(Caja::count())->toBe(0);
});

test('preparar roles dos veces es idempotente y separa operación revisión y auditoría', function () {
    $this->seed(TesoreriaRolesSeeder::class);
    $roles = Role::count();
    $permisos = Permission::count();
    $this->seed(TesoreriaRolesSeeder::class);
    expect(Role::count())->toBe($roles)->and(Permission::count())->toBe($permisos);
    $cajero = Role::findByName('cajero');
    $supervisor = Role::findByName('supervisor_caja');
    $auditor = Role::findByName('auditor_financiero');
    $tesoreria = Role::findByName('tesoreria');
    expect($cajero->hasPermissionTo('Operar:Caja'))->toBeTrue()->and($cajero->hasPermissionTo('Conciliar:Banco'))->toBeFalse()->and($supervisor->hasPermissionTo('Aprobar:Devolucion'))->toBeTrue()->and($supervisor->hasPermissionTo('Administrar:Fondos'))->toBeFalse()->and($auditor->hasPermissionTo('Auditar:Fondos'))->toBeTrue()->and($auditor->hasPermissionTo('Revisar:Comprobante'))->toBeFalse()->and($tesoreria->hasPermissionTo('Conciliar:Banco'))->toBeTrue();
});
