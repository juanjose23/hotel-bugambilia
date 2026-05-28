<?php

declare(strict_types=1);

namespace App\Policies\Monedas;

use App\Models\Monedas\TasaCambio;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TasaCambioPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TasaCambio');
    }

    public function view(AuthUser $authUser, TasaCambio $tasaCambio): bool
    {
        return $authUser->can('View:TasaCambio');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TasaCambio');
    }

    public function update(AuthUser $authUser, TasaCambio $tasaCambio): bool
    {
        return $authUser->can('Update:TasaCambio');
    }

    public function delete(AuthUser $authUser, TasaCambio $tasaCambio): bool
    {
        return $authUser->can('Delete:TasaCambio');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TasaCambio');
    }

    public function restore(AuthUser $authUser, TasaCambio $tasaCambio): bool
    {
        return $authUser->can('Restore:TasaCambio');
    }

    public function forceDelete(AuthUser $authUser, TasaCambio $tasaCambio): bool
    {
        return $authUser->can('ForceDelete:TasaCambio');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TasaCambio');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TasaCambio');
    }

    public function replicate(AuthUser $authUser, TasaCambio $tasaCambio): bool
    {
        return $authUser->can('Replicate:TasaCambio');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TasaCambio');
    }
}
