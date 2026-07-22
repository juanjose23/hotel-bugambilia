<?php

declare(strict_types=1);

namespace App\Repository\Policies\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LimpiezaEjecucionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LimpiezaEjecucion');
    }

    public function view(AuthUser $authUser, LimpiezaEjecucion $limpiezaEjecucion): bool
    {
        return $authUser->can('View:LimpiezaEjecucion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LimpiezaEjecucion');
    }

    public function update(AuthUser $authUser, LimpiezaEjecucion $limpiezaEjecucion): bool
    {
        return $authUser->can('Update:LimpiezaEjecucion');
    }

    public function delete(AuthUser $authUser, LimpiezaEjecucion $limpiezaEjecucion): bool
    {
        return $authUser->can('Delete:LimpiezaEjecucion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LimpiezaEjecucion');
    }

    public function restore(AuthUser $authUser, LimpiezaEjecucion $limpiezaEjecucion): bool
    {
        return $authUser->can('Restore:LimpiezaEjecucion');
    }

    public function forceDelete(AuthUser $authUser, LimpiezaEjecucion $limpiezaEjecucion): bool
    {
        return $authUser->can('ForceDelete:LimpiezaEjecucion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LimpiezaEjecucion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LimpiezaEjecucion');
    }

    public function replicate(AuthUser $authUser, LimpiezaEjecucion $limpiezaEjecucion): bool
    {
        return $authUser->can('Replicate:LimpiezaEjecucion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LimpiezaEjecucion');
    }
}
