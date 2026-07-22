<?php

declare(strict_types=1);

namespace App\Repository\Policies\Compras;

use App\Repository\Models\Compras\Solicitud;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SolicitudPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Solicitud');
    }

    public function view(AuthUser $authUser, Solicitud $solicitud): bool
    {
        return $authUser->can('View:Solicitud');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Solicitud');
    }

    public function update(AuthUser $authUser, Solicitud $solicitud): bool
    {
        return $authUser->can('Update:Solicitud');
    }

    public function delete(AuthUser $authUser, Solicitud $solicitud): bool
    {
        return $authUser->can('Delete:Solicitud');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Solicitud');
    }

    public function restore(AuthUser $authUser, Solicitud $solicitud): bool
    {
        return $authUser->can('Restore:Solicitud');
    }

    public function forceDelete(AuthUser $authUser, Solicitud $solicitud): bool
    {
        return $authUser->can('ForceDelete:Solicitud');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Solicitud');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Solicitud');
    }

    public function replicate(AuthUser $authUser, Solicitud $solicitud): bool
    {
        return $authUser->can('Replicate:Solicitud');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Solicitud');
    }
}
