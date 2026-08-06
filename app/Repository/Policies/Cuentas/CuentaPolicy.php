<?php

declare(strict_types=1);

namespace App\Repository\Policies\Cuentas;

use App\Repository\Models\Cuentas\Cuenta;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CuentaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Cuenta');
    }

    public function view(AuthUser $authUser, Cuenta $cuenta): bool
    {
        return $authUser->can('View:Cuenta');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Cuenta');
    }

    public function update(AuthUser $authUser, Cuenta $cuenta): bool
    {
        return $authUser->can('Update:Cuenta');
    }

    public function delete(AuthUser $authUser, Cuenta $cuenta): bool
    {
        return $authUser->can('Delete:Cuenta');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Cuenta');
    }

    public function restore(AuthUser $authUser, Cuenta $cuenta): bool
    {
        return $authUser->can('Restore:Cuenta');
    }

    public function forceDelete(AuthUser $authUser, Cuenta $cuenta): bool
    {
        return $authUser->can('ForceDelete:Cuenta');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Cuenta');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Cuenta');
    }

    public function replicate(AuthUser $authUser, Cuenta $cuenta): bool
    {
        return $authUser->can('Replicate:Cuenta');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Cuenta');
    }
}
