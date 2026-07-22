<?php

declare(strict_types=1);

namespace App\Repository\Policies\Promociones;

use App\Repository\Models\Promociones\Promocion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PromocionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Promocion');
    }

    public function view(AuthUser $authUser, Promocion $promocion): bool
    {
        return $authUser->can('View:Promocion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Promocion');
    }

    public function update(AuthUser $authUser, Promocion $promocion): bool
    {
        return $authUser->can('Update:Promocion');
    }

    public function delete(AuthUser $authUser, Promocion $promocion): bool
    {
        return $authUser->can('Delete:Promocion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Promocion');
    }

    public function restore(AuthUser $authUser, Promocion $promocion): bool
    {
        return $authUser->can('Restore:Promocion');
    }

    public function forceDelete(AuthUser $authUser, Promocion $promocion): bool
    {
        return $authUser->can('ForceDelete:Promocion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Promocion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Promocion');
    }

    public function replicate(AuthUser $authUser, Promocion $promocion): bool
    {
        return $authUser->can('Replicate:Promocion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Promocion');
    }
}
