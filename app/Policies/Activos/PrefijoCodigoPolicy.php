<?php

declare(strict_types=1);

namespace App\Policies\Activos;

use App\Models\Activos\PrefijoCodigo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PrefijoCodigoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PrefijoCodigo');
    }

    public function view(AuthUser $authUser, PrefijoCodigo $prefijoCodigo): bool
    {
        return $authUser->can('View:PrefijoCodigo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PrefijoCodigo');
    }

    public function update(AuthUser $authUser, PrefijoCodigo $prefijoCodigo): bool
    {
        return $authUser->can('Update:PrefijoCodigo');
    }

    public function delete(AuthUser $authUser, PrefijoCodigo $prefijoCodigo): bool
    {
        return $authUser->can('Delete:PrefijoCodigo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PrefijoCodigo');
    }

    public function restore(AuthUser $authUser, PrefijoCodigo $prefijoCodigo): bool
    {
        return $authUser->can('Restore:PrefijoCodigo');
    }

    public function forceDelete(AuthUser $authUser, PrefijoCodigo $prefijoCodigo): bool
    {
        return $authUser->can('ForceDelete:PrefijoCodigo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PrefijoCodigo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PrefijoCodigo');
    }

    public function replicate(AuthUser $authUser, PrefijoCodigo $prefijoCodigo): bool
    {
        return $authUser->can('Replicate:PrefijoCodigo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PrefijoCodigo');
    }
}
