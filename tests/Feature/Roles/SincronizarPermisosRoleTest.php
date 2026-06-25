<?php

declare(strict_types=1);

use App\UseCases\Roles\Mutations\SincronizarPermisosRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
});

it('crea permisos que no existen y los sincroniza con el rol', function () {
    $permissions = collect(['create-users', 'edit-users']);

    app(SincronizarPermisosRole::class)->execute(
        role: $this->role,
        formData: ['guard_name' => 'web'],
        permissions: $permissions,
    );

    expect(Permission::where('name', 'create-users')->exists())->toBeTrue();
    expect(Permission::where('name', 'edit-users')->exists())->toBeTrue();
    expect($this->role->fresh()->hasPermissionTo('create-users'))->toBeTrue();
    expect($this->role->fresh()->hasPermissionTo('edit-users'))->toBeTrue();
});

it('reutiliza permisos existentes y los sincroniza con el rol', function () {
    Permission::create(['name' => 'view-reports', 'guard_name' => 'web']);

    $permissions = collect(['view-reports', 'delete-reports']);

    app(SincronizarPermisosRole::class)->execute(
        role: $this->role,
        formData: ['guard_name' => 'web'],
        permissions: $permissions,
    );

    expect(Permission::where('name', 'view-reports')->count())->toBe(1);
    expect(Permission::where('name', 'delete-reports')->exists())->toBeTrue();
    expect($this->role->fresh()->hasPermissionTo('view-reports'))->toBeTrue();
    expect($this->role->fresh()->hasPermissionTo('delete-reports'))->toBeTrue();
});

it('remueve permisos previos del rol al sincronizar con una lista diferente', function () {
    Permission::create(['name' => 'manage-users', 'guard_name' => 'web']);
    $this->role->givePermissionTo('manage-users');

    $permissions = collect(['manage-settings']);

    app(SincronizarPermisosRole::class)->execute(
        role: $this->role,
        formData: ['guard_name' => 'web'],
        permissions: $permissions,
    );

    expect($this->role->fresh()->hasPermissionTo('manage-users'))->toBeFalse();
    expect($this->role->fresh()->hasPermissionTo('manage-settings'))->toBeTrue();
});
