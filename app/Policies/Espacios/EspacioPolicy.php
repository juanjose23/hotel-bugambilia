<?php

declare(strict_types=1);

namespace App\Policies\Espacios;

use App\Models\Espacios\Espacio;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EspacioPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Espacio');
    }

    public function view(AuthUser $authUser, Espacio $espacio): bool
    {
        return $authUser->can('View:Espacio');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Espacio');
    }

    public function update(AuthUser $authUser, Espacio $espacio): bool
    {
        return $authUser->can('Update:Espacio');
    }

    public function delete(AuthUser $authUser, Espacio $espacio): bool
    {
        return $authUser->can('Delete:Espacio');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Espacio');
    }

    public function restore(AuthUser $authUser, Espacio $espacio): bool
    {
        return $authUser->can('Restore:Espacio');
    }

    public function forceDelete(AuthUser $authUser, Espacio $espacio): bool
    {
        return $authUser->can('ForceDelete:Espacio');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Espacio');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Espacio');
    }

    public function replicate(AuthUser $authUser, Espacio $espacio): bool
    {
        return $authUser->can('Replicate:Espacio');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Espacio');
    }
}
