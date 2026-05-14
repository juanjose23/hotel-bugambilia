<?php

declare(strict_types=1);

namespace App\Policies\Colaboradores;

use App\Models\Colaboradores\ColaboradorContactoEmergencia;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ColaboradorContactoEmergenciaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ColaboradorContactoEmergencia');
    }

    public function view(AuthUser $authUser, ColaboradorContactoEmergencia $colaboradorContactoEmergencia): bool
    {
        return $authUser->can('View:ColaboradorContactoEmergencia');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ColaboradorContactoEmergencia');
    }

    public function update(AuthUser $authUser, ColaboradorContactoEmergencia $colaboradorContactoEmergencia): bool
    {
        return $authUser->can('Update:ColaboradorContactoEmergencia');
    }

    public function delete(AuthUser $authUser, ColaboradorContactoEmergencia $colaboradorContactoEmergencia): bool
    {
        return $authUser->can('Delete:ColaboradorContactoEmergencia');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ColaboradorContactoEmergencia');
    }

    public function restore(AuthUser $authUser, ColaboradorContactoEmergencia $colaboradorContactoEmergencia): bool
    {
        return $authUser->can('Restore:ColaboradorContactoEmergencia');
    }

    public function forceDelete(AuthUser $authUser, ColaboradorContactoEmergencia $colaboradorContactoEmergencia): bool
    {
        return $authUser->can('ForceDelete:ColaboradorContactoEmergencia');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ColaboradorContactoEmergencia');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ColaboradorContactoEmergencia');
    }

    public function replicate(AuthUser $authUser, ColaboradorContactoEmergencia $colaboradorContactoEmergencia): bool
    {
        return $authUser->can('Replicate:ColaboradorContactoEmergencia');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ColaboradorContactoEmergencia');
    }
}
