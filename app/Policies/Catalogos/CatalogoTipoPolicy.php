<?php

declare(strict_types=1);

namespace App\Policies\Catalogos;

use App\Models\Catalogos\CatalogoTipo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CatalogoTipoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CatalogoTipo');
    }

    public function view(AuthUser $authUser, CatalogoTipo $catalogoTipo): bool
    {
        return $authUser->can('View:CatalogoTipo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CatalogoTipo');
    }

    public function update(AuthUser $authUser, CatalogoTipo $catalogoTipo): bool
    {
        return $authUser->can('Update:CatalogoTipo');
    }

    public function delete(AuthUser $authUser, CatalogoTipo $catalogoTipo): bool
    {
        return $authUser->can('Delete:CatalogoTipo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CatalogoTipo');
    }

    public function restore(AuthUser $authUser, CatalogoTipo $catalogoTipo): bool
    {
        return $authUser->can('Restore:CatalogoTipo');
    }

    public function forceDelete(AuthUser $authUser, CatalogoTipo $catalogoTipo): bool
    {
        return $authUser->can('ForceDelete:CatalogoTipo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CatalogoTipo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CatalogoTipo');
    }

    public function replicate(AuthUser $authUser, CatalogoTipo $catalogoTipo): bool
    {
        return $authUser->can('Replicate:CatalogoTipo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CatalogoTipo');
    }
}
