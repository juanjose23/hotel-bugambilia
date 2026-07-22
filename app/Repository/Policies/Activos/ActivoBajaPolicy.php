<?php

declare(strict_types=1);

namespace App\Repository\Policies\Activos;

use App\Repository\Models\Activos\ActivoBaja;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ActivoBajaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ActivoBaja');
    }

    public function view(AuthUser $authUser, ActivoBaja $activoBaja): bool
    {
        return $authUser->can('View:ActivoBaja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ActivoBaja');
    }

    public function update(AuthUser $authUser, ActivoBaja $activoBaja): bool
    {
        return $authUser->can('Update:ActivoBaja');
    }

    public function delete(AuthUser $authUser, ActivoBaja $activoBaja): bool
    {
        return $authUser->can('Delete:ActivoBaja');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ActivoBaja');
    }

    public function restore(AuthUser $authUser, ActivoBaja $activoBaja): bool
    {
        return $authUser->can('Restore:ActivoBaja');
    }

    public function forceDelete(AuthUser $authUser, ActivoBaja $activoBaja): bool
    {
        return $authUser->can('ForceDelete:ActivoBaja');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ActivoBaja');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ActivoBaja');
    }

    public function replicate(AuthUser $authUser, ActivoBaja $activoBaja): bool
    {
        return $authUser->can('Replicate:ActivoBaja');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ActivoBaja');
    }
}
