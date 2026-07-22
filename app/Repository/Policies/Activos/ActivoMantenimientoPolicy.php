<?php

declare(strict_types=1);

namespace App\Repository\Policies\Activos;

use App\Repository\Models\Activos\ActivoMantenimiento;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ActivoMantenimientoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ActivoMantenimiento');
    }

    public function view(AuthUser $authUser, ActivoMantenimiento $activoMantenimiento): bool
    {
        return $authUser->can('View:ActivoMantenimiento');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ActivoMantenimiento');
    }

    public function update(AuthUser $authUser, ActivoMantenimiento $activoMantenimiento): bool
    {
        return $authUser->can('Update:ActivoMantenimiento');
    }

    public function delete(AuthUser $authUser, ActivoMantenimiento $activoMantenimiento): bool
    {
        return $authUser->can('Delete:ActivoMantenimiento');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ActivoMantenimiento');
    }

    public function restore(AuthUser $authUser, ActivoMantenimiento $activoMantenimiento): bool
    {
        return $authUser->can('Restore:ActivoMantenimiento');
    }

    public function forceDelete(AuthUser $authUser, ActivoMantenimiento $activoMantenimiento): bool
    {
        return $authUser->can('ForceDelete:ActivoMantenimiento');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ActivoMantenimiento');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ActivoMantenimiento');
    }

    public function replicate(AuthUser $authUser, ActivoMantenimiento $activoMantenimiento): bool
    {
        return $authUser->can('Replicate:ActivoMantenimiento');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ActivoMantenimiento');
    }
}
