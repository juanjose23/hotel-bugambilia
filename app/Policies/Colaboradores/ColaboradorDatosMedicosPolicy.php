<?php

declare(strict_types=1);

namespace App\Policies\Colaboradores;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Colaboradores\ColaboradorDatosMedicos;
use Illuminate\Auth\Access\HandlesAuthorization;

class ColaboradorDatosMedicosPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ColaboradorDatosMedicos');
    }

    public function view(AuthUser $authUser, ColaboradorDatosMedicos $colaboradorDatosMedicos): bool
    {
        return $authUser->can('View:ColaboradorDatosMedicos');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ColaboradorDatosMedicos');
    }

    public function update(AuthUser $authUser, ColaboradorDatosMedicos $colaboradorDatosMedicos): bool
    {
        return $authUser->can('Update:ColaboradorDatosMedicos');
    }

    public function delete(AuthUser $authUser, ColaboradorDatosMedicos $colaboradorDatosMedicos): bool
    {
        return $authUser->can('Delete:ColaboradorDatosMedicos');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ColaboradorDatosMedicos');
    }

    public function restore(AuthUser $authUser, ColaboradorDatosMedicos $colaboradorDatosMedicos): bool
    {
        return $authUser->can('Restore:ColaboradorDatosMedicos');
    }

    public function forceDelete(AuthUser $authUser, ColaboradorDatosMedicos $colaboradorDatosMedicos): bool
    {
        return $authUser->can('ForceDelete:ColaboradorDatosMedicos');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ColaboradorDatosMedicos');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ColaboradorDatosMedicos');
    }

    public function replicate(AuthUser $authUser, ColaboradorDatosMedicos $colaboradorDatosMedicos): bool
    {
        return $authUser->can('Replicate:ColaboradorDatosMedicos');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ColaboradorDatosMedicos');
    }

}