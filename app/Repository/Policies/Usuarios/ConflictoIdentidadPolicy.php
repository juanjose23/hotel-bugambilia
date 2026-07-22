<?php

declare(strict_types=1);

namespace App\Repository\Policies\Usuarios;

use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ConflictoIdentidadPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ConflictoIdentidad');
    }

    public function view(AuthUser $authUser, ConflictoIdentidad $conflictoIdentidad): bool
    {
        return $authUser->can('View:ConflictoIdentidad');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ConflictoIdentidad');
    }

    public function update(AuthUser $authUser, ConflictoIdentidad $conflictoIdentidad): bool
    {
        return $authUser->can('Update:ConflictoIdentidad');
    }

    public function delete(AuthUser $authUser, ConflictoIdentidad $conflictoIdentidad): bool
    {
        return $authUser->can('Delete:ConflictoIdentidad');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ConflictoIdentidad');
    }

    public function restore(AuthUser $authUser, ConflictoIdentidad $conflictoIdentidad): bool
    {
        return $authUser->can('Restore:ConflictoIdentidad');
    }

    public function forceDelete(AuthUser $authUser, ConflictoIdentidad $conflictoIdentidad): bool
    {
        return $authUser->can('ForceDelete:ConflictoIdentidad');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ConflictoIdentidad');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ConflictoIdentidad');
    }

    public function replicate(AuthUser $authUser, ConflictoIdentidad $conflictoIdentidad): bool
    {
        return $authUser->can('Replicate:ConflictoIdentidad');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ConflictoIdentidad');
    }
}
