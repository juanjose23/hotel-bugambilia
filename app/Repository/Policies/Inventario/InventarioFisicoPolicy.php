<?php

declare(strict_types=1);

namespace App\Repository\Policies\Inventario;

use App\Repository\Models\Inventario\InventarioFisico;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InventarioFisicoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InventarioFisico');
    }

    public function view(AuthUser $authUser, InventarioFisico $inventarioFisico): bool
    {
        return $authUser->can('View:InventarioFisico');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InventarioFisico');
    }

    public function update(AuthUser $authUser, InventarioFisico $inventarioFisico): bool
    {
        return $authUser->can('Update:InventarioFisico');
    }

    public function delete(AuthUser $authUser, InventarioFisico $inventarioFisico): bool
    {
        return $authUser->can('Delete:InventarioFisico');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InventarioFisico');
    }

    public function restore(AuthUser $authUser, InventarioFisico $inventarioFisico): bool
    {
        return $authUser->can('Restore:InventarioFisico');
    }

    public function forceDelete(AuthUser $authUser, InventarioFisico $inventarioFisico): bool
    {
        return $authUser->can('ForceDelete:InventarioFisico');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InventarioFisico');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InventarioFisico');
    }

    public function replicate(AuthUser $authUser, InventarioFisico $inventarioFisico): bool
    {
        return $authUser->can('Replicate:InventarioFisico');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InventarioFisico');
    }
}
