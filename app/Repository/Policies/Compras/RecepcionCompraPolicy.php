<?php

declare(strict_types=1);

namespace App\Repository\Policies\Compras;

use App\Repository\Models\Compras\RecepcionCompra;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RecepcionCompraPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RecepcionCompra');
    }

    public function view(AuthUser $authUser, RecepcionCompra $recepcionCompra): bool
    {
        return $authUser->can('View:RecepcionCompra');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RecepcionCompra');
    }

    public function update(AuthUser $authUser, RecepcionCompra $recepcionCompra): bool
    {
        return $authUser->can('Update:RecepcionCompra');
    }

    public function delete(AuthUser $authUser, RecepcionCompra $recepcionCompra): bool
    {
        return $authUser->can('Delete:RecepcionCompra');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RecepcionCompra');
    }

    public function restore(AuthUser $authUser, RecepcionCompra $recepcionCompra): bool
    {
        return $authUser->can('Restore:RecepcionCompra');
    }

    public function forceDelete(AuthUser $authUser, RecepcionCompra $recepcionCompra): bool
    {
        return $authUser->can('ForceDelete:RecepcionCompra');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RecepcionCompra');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RecepcionCompra');
    }

    public function replicate(AuthUser $authUser, RecepcionCompra $recepcionCompra): bool
    {
        return $authUser->can('Replicate:RecepcionCompra');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RecepcionCompra');
    }
}
