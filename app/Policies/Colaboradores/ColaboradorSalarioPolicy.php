<?php

declare(strict_types=1);

namespace App\Policies\Colaboradores;

use App\Models\Colaboradores\ColaboradorSalario;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ColaboradorSalarioPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ColaboradorSalario');
    }

    public function view(AuthUser $authUser, ColaboradorSalario $colaboradorSalario): bool
    {
        return $authUser->can('View:ColaboradorSalario');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ColaboradorSalario');
    }

    public function update(AuthUser $authUser, ColaboradorSalario $colaboradorSalario): bool
    {
        return $authUser->can('Update:ColaboradorSalario');
    }

    public function delete(AuthUser $authUser, ColaboradorSalario $colaboradorSalario): bool
    {
        return $authUser->can('Delete:ColaboradorSalario');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ColaboradorSalario');
    }

    public function restore(AuthUser $authUser, ColaboradorSalario $colaboradorSalario): bool
    {
        return $authUser->can('Restore:ColaboradorSalario');
    }

    public function forceDelete(AuthUser $authUser, ColaboradorSalario $colaboradorSalario): bool
    {
        return $authUser->can('ForceDelete:ColaboradorSalario');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ColaboradorSalario');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ColaboradorSalario');
    }

    public function replicate(AuthUser $authUser, ColaboradorSalario $colaboradorSalario): bool
    {
        return $authUser->can('Replicate:ColaboradorSalario');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ColaboradorSalario');
    }
}
