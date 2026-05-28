<?php

declare(strict_types=1);

namespace App\Policies\Activos;

use App\Models\Activos\RegistroIndividualizacion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RegistroIndividualizacionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RegistroIndividualizacion');
    }

    public function view(AuthUser $authUser, RegistroIndividualizacion $registroIndividualizacion): bool
    {
        return $authUser->can('View:RegistroIndividualizacion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RegistroIndividualizacion');
    }

    public function update(AuthUser $authUser, RegistroIndividualizacion $registroIndividualizacion): bool
    {
        return $authUser->can('Update:RegistroIndividualizacion');
    }

    public function delete(AuthUser $authUser, RegistroIndividualizacion $registroIndividualizacion): bool
    {
        return $authUser->can('Delete:RegistroIndividualizacion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RegistroIndividualizacion');
    }

    public function restore(AuthUser $authUser, RegistroIndividualizacion $registroIndividualizacion): bool
    {
        return $authUser->can('Restore:RegistroIndividualizacion');
    }

    public function forceDelete(AuthUser $authUser, RegistroIndividualizacion $registroIndividualizacion): bool
    {
        return $authUser->can('ForceDelete:RegistroIndividualizacion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RegistroIndividualizacion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RegistroIndividualizacion');
    }

    public function replicate(AuthUser $authUser, RegistroIndividualizacion $registroIndividualizacion): bool
    {
        return $authUser->can('Replicate:RegistroIndividualizacion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RegistroIndividualizacion');
    }
}
