<?php

declare(strict_types=1);

namespace App\Repository\Policies\Catalogos;

use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CatalogoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Catalogo');
    }

    public function view(AuthUser $authUser, Catalogo $catalogo): bool
    {
        return $authUser->can('View:Catalogo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Catalogo');
    }

    public function update(AuthUser $authUser, Catalogo $catalogo): bool
    {
        return $authUser->can('Update:Catalogo');
    }

    public function delete(AuthUser $authUser, Catalogo $catalogo): bool
    {
        return $authUser->can('Delete:Catalogo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Catalogo');
    }

    public function restore(AuthUser $authUser, Catalogo $catalogo): bool
    {
        return $authUser->can('Restore:Catalogo');
    }

    public function forceDelete(AuthUser $authUser, Catalogo $catalogo): bool
    {
        return $authUser->can('ForceDelete:Catalogo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Catalogo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Catalogo');
    }

    public function replicate(AuthUser $authUser, Catalogo $catalogo): bool
    {
        return $authUser->can('Replicate:Catalogo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Catalogo');
    }
}
