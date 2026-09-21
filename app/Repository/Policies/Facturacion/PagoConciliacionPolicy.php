<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\PagoConciliacion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PagoConciliacionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PagoConciliacion');
    }

    public function view(AuthUser $authUser, PagoConciliacion $pagoConciliacion): bool
    {
        return $authUser->can('View:PagoConciliacion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PagoConciliacion');
    }

    public function update(AuthUser $authUser, PagoConciliacion $pagoConciliacion): bool
    {
        return $authUser->can('Update:PagoConciliacion');
    }

    public function delete(AuthUser $authUser, PagoConciliacion $pagoConciliacion): bool
    {
        return $authUser->can('Delete:PagoConciliacion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PagoConciliacion');
    }

    public function restore(AuthUser $authUser, PagoConciliacion $pagoConciliacion): bool
    {
        return $authUser->can('Restore:PagoConciliacion');
    }

    public function forceDelete(AuthUser $authUser, PagoConciliacion $pagoConciliacion): bool
    {
        return $authUser->can('ForceDelete:PagoConciliacion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PagoConciliacion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PagoConciliacion');
    }

    public function replicate(AuthUser $authUser, PagoConciliacion $pagoConciliacion): bool
    {
        return $authUser->can('Replicate:PagoConciliacion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PagoConciliacion');
    }
}
