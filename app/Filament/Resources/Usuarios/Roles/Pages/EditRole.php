<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Roles\Pages;

use App\Filament\Resources\Usuarios\Roles\RoleResource;
use App\Interactors\Roles\SincronizarPermisosRole;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Override;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected SincronizarPermisosRole $sincronizarPermisosRole;

    public function boot(SincronizarPermisosRole $sincronizarPermisosRole): void
    {
        $this->sincronizarPermisosRole = $sincronizarPermisosRole;
    }

    /** @var Collection<int, string> */
    public Collection $permissions;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        $role = $this->getRecord();
        if ($role instanceof Role) {
            $this->sincronizarPermisosRole->execute(
                $role,
                $this->data ?? [],
                $this->permissions,
            );
        }
    }
}
