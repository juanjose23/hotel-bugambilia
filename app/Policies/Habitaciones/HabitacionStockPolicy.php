<?php

declare(strict_types=1);

namespace App\Policies\Habitaciones;

use App\Models\Habitaciones\HabitacionStock;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HabitacionStockPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HabitacionStock');
    }

    public function view(AuthUser $authUser, HabitacionStock $habitacionStock): bool
    {
        return $authUser->can('View:HabitacionStock');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HabitacionStock');
    }

    public function update(AuthUser $authUser, HabitacionStock $habitacionStock): bool
    {
        return $authUser->can('Update:HabitacionStock');
    }

    public function delete(AuthUser $authUser, HabitacionStock $habitacionStock): bool
    {
        return $authUser->can('Delete:HabitacionStock');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HabitacionStock');
    }

    public function restore(AuthUser $authUser, HabitacionStock $habitacionStock): bool
    {
        return $authUser->can('Restore:HabitacionStock');
    }

    public function forceDelete(AuthUser $authUser, HabitacionStock $habitacionStock): bool
    {
        return $authUser->can('ForceDelete:HabitacionStock');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HabitacionStock');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HabitacionStock');
    }

    public function replicate(AuthUser $authUser, HabitacionStock $habitacionStock): bool
    {
        return $authUser->can('Replicate:HabitacionStock');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HabitacionStock');
    }
}
