<?php

declare(strict_types=1);

namespace App\Policies\Compras;

use App\Models\Compras\DevolucionCompra;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DevolucionCompraPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DevolucionCompra');
    }

    public function view(AuthUser $authUser, DevolucionCompra $devolucionCompra): bool
    {
        return $authUser->can('View:DevolucionCompra');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DevolucionCompra');
    }

    public function update(AuthUser $authUser, DevolucionCompra $devolucionCompra): bool
    {
        return $authUser->can('Update:DevolucionCompra');
    }

    public function delete(AuthUser $authUser, DevolucionCompra $devolucionCompra): bool
    {
        return $authUser->can('Delete:DevolucionCompra');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DevolucionCompra');
    }

    public function restore(AuthUser $authUser, DevolucionCompra $devolucionCompra): bool
    {
        return $authUser->can('Restore:DevolucionCompra');
    }

    public function forceDelete(AuthUser $authUser, DevolucionCompra $devolucionCompra): bool
    {
        return $authUser->can('ForceDelete:DevolucionCompra');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DevolucionCompra');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DevolucionCompra');
    }

    public function replicate(AuthUser $authUser, DevolucionCompra $devolucionCompra): bool
    {
        return $authUser->can('Replicate:DevolucionCompra');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DevolucionCompra');
    }
}
