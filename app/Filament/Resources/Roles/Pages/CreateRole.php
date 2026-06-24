<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\UseCases\Roles\Mutations\SincronizarPermisosRole;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Override;
use Spatie\Permission\Models\Role;

class CreateRole extends CreateRecord
{
    /** @var Collection<int, string> */
    public Collection $permissions;

    protected static string $resource = RoleResource::class;

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $collected = collect($data)
            ->filter(fn (mixed $permission, string $key): bool => ! in_array($key, ['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()], true))
            ->values()
            ->flatten()
            ->unique();
        /** @var Collection<int, string> $collected */
        $this->permissions = $collected;

        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'guard_name']);
    }

    protected function afterCreate(): void
    {
        $role = $this->record;
        if ($role instanceof Role) {
            app(SincronizarPermisosRole::class)->execute(
                $role,
                $this->data ?? [],
                $this->permissions,
            );
        }
    }
}
