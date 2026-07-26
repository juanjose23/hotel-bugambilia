<?php

declare(strict_types=1);

namespace App\Repository\Policies\Estancias;

use App\Repository\Models\Estancias\Estancia;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EstanciaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Estancia');
    }

    public function view(AuthUser $authUser, Estancia $estancia): bool
    {
        return $authUser->can('View:Estancia');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Estancia');
    }

    public function update(AuthUser $authUser, Estancia $estancia): bool
    {
        return $authUser->can('Update:Estancia');
    }

    public function delete(AuthUser $authUser, Estancia $estancia): bool
    {
        return $authUser->can('Delete:Estancia');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Estancia');
    }

    public function restore(AuthUser $authUser, Estancia $estancia): bool
    {
        return $authUser->can('Restore:Estancia');
    }

    public function forceDelete(AuthUser $authUser, Estancia $estancia): bool
    {
        return $authUser->can('ForceDelete:Estancia');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Estancia');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Estancia');
    }

    public function replicate(AuthUser $authUser, Estancia $estancia): bool
    {
        return $authUser->can('Replicate:Estancia');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Estancia');
    }
}
