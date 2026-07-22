<?php

declare(strict_types=1);

namespace App\Repository\Policies\Monedas;

use App\Repository\Models\Monedas\Moneda;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MonedaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Moneda');
    }

    public function view(AuthUser $authUser, Moneda $moneda): bool
    {
        return $authUser->can('View:Moneda');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Moneda');
    }

    public function update(AuthUser $authUser, Moneda $moneda): bool
    {
        return $authUser->can('Update:Moneda');
    }

    public function delete(AuthUser $authUser, Moneda $moneda): bool
    {
        return $authUser->can('Delete:Moneda');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Moneda');
    }

    public function restore(AuthUser $authUser, Moneda $moneda): bool
    {
        return $authUser->can('Restore:Moneda');
    }

    public function forceDelete(AuthUser $authUser, Moneda $moneda): bool
    {
        return $authUser->can('ForceDelete:Moneda');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Moneda');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Moneda');
    }

    public function replicate(AuthUser $authUser, Moneda $moneda): bool
    {
        return $authUser->can('Replicate:Moneda');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Moneda');
    }
}
