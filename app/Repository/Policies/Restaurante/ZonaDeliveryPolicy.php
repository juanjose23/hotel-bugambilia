<?php

declare(strict_types=1);

namespace App\Repository\Policies\Restaurante;

use App\Repository\Models\Restaurante\ZonaDelivery;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ZonaDeliveryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ZonaDelivery');
    }

    public function view(AuthUser $authUser, ZonaDelivery $zonaDelivery): bool
    {
        return $authUser->can('View:ZonaDelivery');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ZonaDelivery');
    }

    public function update(AuthUser $authUser, ZonaDelivery $zonaDelivery): bool
    {
        return $authUser->can('Update:ZonaDelivery');
    }

    public function delete(AuthUser $authUser, ZonaDelivery $zonaDelivery): bool
    {
        return $authUser->can('Delete:ZonaDelivery');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ZonaDelivery');
    }

    public function restore(AuthUser $authUser, ZonaDelivery $zonaDelivery): bool
    {
        return $authUser->can('Restore:ZonaDelivery');
    }

    public function forceDelete(AuthUser $authUser, ZonaDelivery $zonaDelivery): bool
    {
        return $authUser->can('ForceDelete:ZonaDelivery');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ZonaDelivery');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ZonaDelivery');
    }

    public function replicate(AuthUser $authUser, ZonaDelivery $zonaDelivery): bool
    {
        return $authUser->can('Replicate:ZonaDelivery');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ZonaDelivery');
    }
}
