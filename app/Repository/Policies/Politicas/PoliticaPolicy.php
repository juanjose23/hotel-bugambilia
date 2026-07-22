<?php

declare(strict_types=1);

namespace App\Repository\Policies\Politicas;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PoliticaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Politica');
    }

    public function view(AuthUser $authUser, Politica $politica): bool
    {
        return $authUser->can('View:Politica');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Politica');
    }

    public function update(AuthUser $authUser, Politica $politica): bool
    {
        return $authUser->can('Update:Politica');
    }

    public function delete(AuthUser $authUser, Politica $politica): bool
    {
        return $authUser->can('Delete:Politica');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Politica');
    }

    public function restore(AuthUser $authUser, Politica $politica): bool
    {
        return $authUser->can('Restore:Politica');
    }

    public function forceDelete(AuthUser $authUser, Politica $politica): bool
    {
        return $authUser->can('ForceDelete:Politica');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Politica');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Politica');
    }

    public function replicate(AuthUser $authUser, Politica $politica): bool
    {
        return $authUser->can('Replicate:Politica');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Politica');
    }
}
