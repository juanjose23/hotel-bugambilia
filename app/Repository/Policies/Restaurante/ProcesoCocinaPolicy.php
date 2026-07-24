<?php

declare(strict_types=1);

namespace App\Repository\Policies\Restaurante;

use App\Repository\Models\Restaurante\ProcesoCocina;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProcesoCocinaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProcesoCocina');
    }

    public function view(AuthUser $authUser, ProcesoCocina $procesoCocina): bool
    {
        return $authUser->can('View:ProcesoCocina');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProcesoCocina');
    }

    public function update(AuthUser $authUser, ProcesoCocina $procesoCocina): bool
    {
        return $authUser->can('Update:ProcesoCocina');
    }

    public function delete(AuthUser $authUser, ProcesoCocina $procesoCocina): bool
    {
        return $authUser->can('Delete:ProcesoCocina');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProcesoCocina');
    }

    public function restore(AuthUser $authUser, ProcesoCocina $procesoCocina): bool
    {
        return $authUser->can('Restore:ProcesoCocina');
    }

    public function forceDelete(AuthUser $authUser, ProcesoCocina $procesoCocina): bool
    {
        return $authUser->can('ForceDelete:ProcesoCocina');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProcesoCocina');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProcesoCocina');
    }

    public function replicate(AuthUser $authUser, ProcesoCocina $procesoCocina): bool
    {
        return $authUser->can('Replicate:ProcesoCocina');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProcesoCocina');
    }
}
