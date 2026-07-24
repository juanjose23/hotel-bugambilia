<?php

declare(strict_types=1);

namespace App\Repository\Policies\Restaurante;

use App\Repository\Models\Restaurante\Plato;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PlatoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Plato');
    }

    public function view(AuthUser $authUser, Plato $plato): bool
    {
        return $authUser->can('View:Plato');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Plato');
    }

    public function update(AuthUser $authUser, Plato $plato): bool
    {
        return $authUser->can('Update:Plato');
    }

    public function delete(AuthUser $authUser, Plato $plato): bool
    {
        return $authUser->can('Delete:Plato');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Plato');
    }

    public function restore(AuthUser $authUser, Plato $plato): bool
    {
        return $authUser->can('Restore:Plato');
    }

    public function forceDelete(AuthUser $authUser, Plato $plato): bool
    {
        return $authUser->can('ForceDelete:Plato');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Plato');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Plato');
    }

    public function replicate(AuthUser $authUser, Plato $plato): bool
    {
        return $authUser->can('Replicate:Plato');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Plato');
    }
}
