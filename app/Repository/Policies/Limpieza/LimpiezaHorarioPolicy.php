<?php

declare(strict_types=1);

namespace App\Repository\Policies\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaHorario;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LimpiezaHorarioPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LimpiezaHorario');
    }

    public function view(AuthUser $authUser, LimpiezaHorario $limpiezaHorario): bool
    {
        return $authUser->can('View:LimpiezaHorario');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LimpiezaHorario');
    }

    public function update(AuthUser $authUser, LimpiezaHorario $limpiezaHorario): bool
    {
        return $authUser->can('Update:LimpiezaHorario');
    }

    public function delete(AuthUser $authUser, LimpiezaHorario $limpiezaHorario): bool
    {
        return $authUser->can('Delete:LimpiezaHorario');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LimpiezaHorario');
    }

    public function restore(AuthUser $authUser, LimpiezaHorario $limpiezaHorario): bool
    {
        return $authUser->can('Restore:LimpiezaHorario');
    }

    public function forceDelete(AuthUser $authUser, LimpiezaHorario $limpiezaHorario): bool
    {
        return $authUser->can('ForceDelete:LimpiezaHorario');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LimpiezaHorario');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LimpiezaHorario');
    }

    public function replicate(AuthUser $authUser, LimpiezaHorario $limpiezaHorario): bool
    {
        return $authUser->can('Replicate:LimpiezaHorario');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LimpiezaHorario');
    }
}
