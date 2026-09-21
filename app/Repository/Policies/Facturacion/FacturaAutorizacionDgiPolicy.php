<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacturaAutorizacionDgiPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FacturaAutorizacionDgi');
    }

    public function view(AuthUser $authUser, FacturaAutorizacionDgi $facturaAutorizacionDgi): bool
    {
        return $authUser->can('View:FacturaAutorizacionDgi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FacturaAutorizacionDgi');
    }

    public function update(AuthUser $authUser, FacturaAutorizacionDgi $facturaAutorizacionDgi): bool
    {
        return $authUser->can('Update:FacturaAutorizacionDgi');
    }

    public function delete(AuthUser $authUser, FacturaAutorizacionDgi $facturaAutorizacionDgi): bool
    {
        return $authUser->can('Delete:FacturaAutorizacionDgi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FacturaAutorizacionDgi');
    }

    public function restore(AuthUser $authUser, FacturaAutorizacionDgi $facturaAutorizacionDgi): bool
    {
        return $authUser->can('Restore:FacturaAutorizacionDgi');
    }

    public function forceDelete(AuthUser $authUser, FacturaAutorizacionDgi $facturaAutorizacionDgi): bool
    {
        return $authUser->can('ForceDelete:FacturaAutorizacionDgi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FacturaAutorizacionDgi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FacturaAutorizacionDgi');
    }

    public function replicate(AuthUser $authUser, FacturaAutorizacionDgi $facturaAutorizacionDgi): bool
    {
        return $authUser->can('Replicate:FacturaAutorizacionDgi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FacturaAutorizacionDgi');
    }
}
