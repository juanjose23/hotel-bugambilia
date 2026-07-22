<?php

declare(strict_types=1);

namespace App\Repository\Policies\Colaboradores;

use App\Repository\Models\Colaboradores\ColaboradorDocumento;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ColaboradorDocumentoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ColaboradorDocumento');
    }

    public function view(AuthUser $authUser, ColaboradorDocumento $colaboradorDocumento): bool
    {
        return $authUser->can('View:ColaboradorDocumento');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ColaboradorDocumento');
    }

    public function update(AuthUser $authUser, ColaboradorDocumento $colaboradorDocumento): bool
    {
        return $authUser->can('Update:ColaboradorDocumento');
    }

    public function delete(AuthUser $authUser, ColaboradorDocumento $colaboradorDocumento): bool
    {
        return $authUser->can('Delete:ColaboradorDocumento');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ColaboradorDocumento');
    }

    public function restore(AuthUser $authUser, ColaboradorDocumento $colaboradorDocumento): bool
    {
        return $authUser->can('Restore:ColaboradorDocumento');
    }

    public function forceDelete(AuthUser $authUser, ColaboradorDocumento $colaboradorDocumento): bool
    {
        return $authUser->can('ForceDelete:ColaboradorDocumento');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ColaboradorDocumento');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ColaboradorDocumento');
    }

    public function replicate(AuthUser $authUser, ColaboradorDocumento $colaboradorDocumento): bool
    {
        return $authUser->can('Replicate:ColaboradorDocumento');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ColaboradorDocumento');
    }
}
