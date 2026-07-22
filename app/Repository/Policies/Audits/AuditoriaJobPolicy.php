<?php

declare(strict_types=1);

namespace App\Repository\Policies\Audits;

use App\Repository\Models\Audits\AuditoriaJob;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AuditoriaJobPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AuditoriaJob');
    }

    public function view(AuthUser $authUser, AuditoriaJob $auditoriaJob): bool
    {
        return $authUser->can('View:AuditoriaJob');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AuditoriaJob');
    }

    public function update(AuthUser $authUser, AuditoriaJob $auditoriaJob): bool
    {
        return $authUser->can('Update:AuditoriaJob');
    }

    public function delete(AuthUser $authUser, AuditoriaJob $auditoriaJob): bool
    {
        return $authUser->can('Delete:AuditoriaJob');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AuditoriaJob');
    }

    public function restore(AuthUser $authUser, AuditoriaJob $auditoriaJob): bool
    {
        return $authUser->can('Restore:AuditoriaJob');
    }

    public function forceDelete(AuthUser $authUser, AuditoriaJob $auditoriaJob): bool
    {
        return $authUser->can('ForceDelete:AuditoriaJob');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AuditoriaJob');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AuditoriaJob');
    }

    public function replicate(AuthUser $authUser, AuditoriaJob $auditoriaJob): bool
    {
        return $authUser->can('Replicate:AuditoriaJob');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AuditoriaJob');
    }
}
