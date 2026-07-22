<?php

declare(strict_types=1);

namespace App\Repository\Policies\Activos;

use App\Repository\Models\Activos\ActPlanMantenimiento;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ActPlanMantenimientoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ActPlanMantenimiento');
    }

    public function view(AuthUser $authUser, ActPlanMantenimiento $actPlanMantenimiento): bool
    {
        return $authUser->can('View:ActPlanMantenimiento');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ActPlanMantenimiento');
    }

    public function update(AuthUser $authUser, ActPlanMantenimiento $actPlanMantenimiento): bool
    {
        return $authUser->can('Update:ActPlanMantenimiento');
    }

    public function delete(AuthUser $authUser, ActPlanMantenimiento $actPlanMantenimiento): bool
    {
        return $authUser->can('Delete:ActPlanMantenimiento');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ActPlanMantenimiento');
    }

    public function restore(AuthUser $authUser, ActPlanMantenimiento $actPlanMantenimiento): bool
    {
        return $authUser->can('Restore:ActPlanMantenimiento');
    }

    public function forceDelete(AuthUser $authUser, ActPlanMantenimiento $actPlanMantenimiento): bool
    {
        return $authUser->can('ForceDelete:ActPlanMantenimiento');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ActPlanMantenimiento');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ActPlanMantenimiento');
    }

    public function replicate(AuthUser $authUser, ActPlanMantenimiento $actPlanMantenimiento): bool
    {
        return $authUser->can('Replicate:ActPlanMantenimiento');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ActPlanMantenimiento');
    }
}
