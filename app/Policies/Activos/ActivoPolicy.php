<?php

declare(strict_types=1);

namespace App\Policies\Activos;

use App\Models\Activos\Activo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ActivoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Activo');
    }

    public function view(AuthUser $authUser, Activo $activo): bool
    {
        return $authUser->can('View:Activo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Activo');
    }

    public function update(AuthUser $authUser, Activo $activo): bool
    {
        return $authUser->can('Update:Activo');
    }

    public function delete(AuthUser $authUser, Activo $activo): bool
    {
        return $authUser->can('Delete:Activo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Activo');
    }

    public function restore(AuthUser $authUser, Activo $activo): bool
    {
        return $authUser->can('Restore:Activo');
    }

    public function forceDelete(AuthUser $authUser, Activo $activo): bool
    {
        return $authUser->can('ForceDelete:Activo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Activo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Activo');
    }

    public function replicate(AuthUser $authUser, Activo $activo): bool
    {
        return $authUser->can('Replicate:Activo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Activo');
    }
}
