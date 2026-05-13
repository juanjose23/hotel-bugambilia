<?php

declare(strict_types=1);

namespace App\Policies\Audits;

use App\Models\Audits\AuditoriaReporte;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AuditoriaReportePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AuditoriaReporte');
    }

    public function view(AuthUser $authUser, AuditoriaReporte $auditoriaReporte): bool
    {
        return $authUser->can('View:AuditoriaReporte');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AuditoriaReporte');
    }

    public function update(AuthUser $authUser, AuditoriaReporte $auditoriaReporte): bool
    {
        return $authUser->can('Update:AuditoriaReporte');
    }

    public function delete(AuthUser $authUser, AuditoriaReporte $auditoriaReporte): bool
    {
        return $authUser->can('Delete:AuditoriaReporte');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AuditoriaReporte');
    }

    public function restore(AuthUser $authUser, AuditoriaReporte $auditoriaReporte): bool
    {
        return $authUser->can('Restore:AuditoriaReporte');
    }

    public function forceDelete(AuthUser $authUser, AuditoriaReporte $auditoriaReporte): bool
    {
        return $authUser->can('ForceDelete:AuditoriaReporte');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AuditoriaReporte');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AuditoriaReporte');
    }

    public function replicate(AuthUser $authUser, AuditoriaReporte $auditoriaReporte): bool
    {
        return $authUser->can('Replicate:AuditoriaReporte');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AuditoriaReporte');
    }
}
