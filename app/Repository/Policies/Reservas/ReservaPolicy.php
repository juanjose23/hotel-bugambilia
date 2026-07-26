<?php

declare(strict_types=1);

namespace App\Repository\Policies\Reservas;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ReservaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reserva');
    }

    public function view(AuthUser $authUser, Reserva $reserva): bool
    {
        return $authUser->can('View:Reserva');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reserva');
    }

    public function update(AuthUser $authUser, Reserva $reserva): bool
    {
        return $authUser->can('Update:Reserva');
    }

    public function delete(AuthUser $authUser, Reserva $reserva): bool
    {
        return $authUser->can('Delete:Reserva');
    }

    public function cancel(AuthUser $authUser, Reserva $reserva): bool
    {
        return $reserva->cliente_id !== null && $reserva->cliente_id === $authUser->id;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Reserva');
    }

    public function restore(AuthUser $authUser, Reserva $reserva): bool
    {
        return $authUser->can('Restore:Reserva');
    }

    public function forceDelete(AuthUser $authUser, Reserva $reserva): bool
    {
        return $authUser->can('ForceDelete:Reserva');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reserva');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reserva');
    }

    public function replicate(AuthUser $authUser, Reserva $reserva): bool
    {
        return $authUser->can('Replicate:Reserva');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reserva');
    }
}
