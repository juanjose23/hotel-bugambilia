<?php

declare(strict_types=1);

namespace App\Repository\Policies\Limpieza;

use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SolicitudLimpiezaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SolicitudLimpieza');
    }

    public function view(AuthUser $authUser, SolicitudLimpieza $solicitudLimpieza): bool
    {
        return $authUser->can('View:SolicitudLimpieza');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SolicitudLimpieza');
    }

    public function update(AuthUser $authUser, SolicitudLimpieza $solicitudLimpieza): bool
    {
        return $authUser->can('Update:SolicitudLimpieza');
    }

    public function delete(AuthUser $authUser, SolicitudLimpieza $solicitudLimpieza): bool
    {
        return $authUser->can('Delete:SolicitudLimpieza');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SolicitudLimpieza');
    }

    public function restore(AuthUser $authUser, SolicitudLimpieza $solicitudLimpieza): bool
    {
        return $authUser->can('Restore:SolicitudLimpieza');
    }

    public function forceDelete(AuthUser $authUser, SolicitudLimpieza $solicitudLimpieza): bool
    {
        return $authUser->can('ForceDelete:SolicitudLimpieza');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SolicitudLimpieza');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SolicitudLimpieza');
    }

    public function replicate(AuthUser $authUser, SolicitudLimpieza $solicitudLimpieza): bool
    {
        return $authUser->can('Replicate:SolicitudLimpieza');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SolicitudLimpieza');
    }
}
