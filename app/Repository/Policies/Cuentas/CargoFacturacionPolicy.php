<?php

declare(strict_types=1);

namespace App\Repository\Policies\Cuentas;

use App\Repository\Models\Cuentas\CargoFacturacion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CargoFacturacionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CargoFacturacion');
    }

    public function view(AuthUser $authUser, CargoFacturacion $cargoFacturacion): bool
    {
        return $authUser->can('View:CargoFacturacion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CargoFacturacion');
    }

    public function update(AuthUser $authUser, CargoFacturacion $cargoFacturacion): bool
    {
        return $authUser->can('Update:CargoFacturacion');
    }

    public function delete(AuthUser $authUser, CargoFacturacion $cargoFacturacion): bool
    {
        return $authUser->can('Delete:CargoFacturacion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CargoFacturacion');
    }

    public function restore(AuthUser $authUser, CargoFacturacion $cargoFacturacion): bool
    {
        return $authUser->can('Restore:CargoFacturacion');
    }

    public function forceDelete(AuthUser $authUser, CargoFacturacion $cargoFacturacion): bool
    {
        return $authUser->can('ForceDelete:CargoFacturacion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CargoFacturacion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CargoFacturacion');
    }

    public function replicate(AuthUser $authUser, CargoFacturacion $cargoFacturacion): bool
    {
        return $authUser->can('Replicate:CargoFacturacion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CargoFacturacion');
    }
}
