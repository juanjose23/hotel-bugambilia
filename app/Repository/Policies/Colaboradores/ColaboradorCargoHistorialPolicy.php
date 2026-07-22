<?php

declare(strict_types=1);

namespace App\Repository\Policies\Colaboradores;

use App\Repository\Models\Colaboradores\ColaboradorCargoHistorial;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ColaboradorCargoHistorialPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ColaboradorCargoHistorial');
    }

    public function view(AuthUser $authUser, ColaboradorCargoHistorial $colaboradorCargoHistorial): bool
    {
        return $authUser->can('View:ColaboradorCargoHistorial');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ColaboradorCargoHistorial');
    }

    public function update(AuthUser $authUser, ColaboradorCargoHistorial $colaboradorCargoHistorial): bool
    {
        return $authUser->can('Update:ColaboradorCargoHistorial');
    }

    public function delete(AuthUser $authUser, ColaboradorCargoHistorial $colaboradorCargoHistorial): bool
    {
        return $authUser->can('Delete:ColaboradorCargoHistorial');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ColaboradorCargoHistorial');
    }

    public function restore(AuthUser $authUser, ColaboradorCargoHistorial $colaboradorCargoHistorial): bool
    {
        return $authUser->can('Restore:ColaboradorCargoHistorial');
    }

    public function forceDelete(AuthUser $authUser, ColaboradorCargoHistorial $colaboradorCargoHistorial): bool
    {
        return $authUser->can('ForceDelete:ColaboradorCargoHistorial');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ColaboradorCargoHistorial');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ColaboradorCargoHistorial');
    }

    public function replicate(AuthUser $authUser, ColaboradorCargoHistorial $colaboradorCargoHistorial): bool
    {
        return $authUser->can('Replicate:ColaboradorCargoHistorial');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ColaboradorCargoHistorial');
    }
}
