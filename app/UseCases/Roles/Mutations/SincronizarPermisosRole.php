<?php

namespace App\UseCases\Roles\Mutations;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Collection;

class SincronizarPermisosRole
{
    /**
     * @param  array<string, mixed>  $formData
     * @param  Collection<int, string>  $permissions
     */
    public function execute(mixed $role, array $formData, Collection $permissions): void
    {
        $permissionModels = collect();

        $permissions->each(function (string $permission) use ($permissionModels, $formData): void {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $formData['guard_name'],
            ]));
        });

        $role->syncPermissions($permissionModels);
    }
}
