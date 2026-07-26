<?php

declare(strict_types=1);

namespace App\Repository\Policies\Restaurante;

use App\Repository\Models\Restaurante\AuditoriaRestaurante;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AuditoriaRestaurantePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AuditoriaRestaurante');
    }

    public function view(AuthUser $authUser, AuditoriaRestaurante $auditoriaRestaurante): bool
    {
        return $authUser->can('View:AuditoriaRestaurante');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AuditoriaRestaurante');
    }

    public function update(AuthUser $authUser, AuditoriaRestaurante $auditoriaRestaurante): bool
    {
        return $authUser->can('Update:AuditoriaRestaurante');
    }

    public function delete(AuthUser $authUser, AuditoriaRestaurante $auditoriaRestaurante): bool
    {
        return $authUser->can('Delete:AuditoriaRestaurante');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AuditoriaRestaurante');
    }

    public function restore(AuthUser $authUser, AuditoriaRestaurante $auditoriaRestaurante): bool
    {
        return $authUser->can('Restore:AuditoriaRestaurante');
    }

    public function forceDelete(AuthUser $authUser, AuditoriaRestaurante $auditoriaRestaurante): bool
    {
        return $authUser->can('ForceDelete:AuditoriaRestaurante');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AuditoriaRestaurante');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AuditoriaRestaurante');
    }

    public function replicate(AuthUser $authUser, AuditoriaRestaurante $auditoriaRestaurante): bool
    {
        return $authUser->can('Replicate:AuditoriaRestaurante');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AuditoriaRestaurante');
    }
}
