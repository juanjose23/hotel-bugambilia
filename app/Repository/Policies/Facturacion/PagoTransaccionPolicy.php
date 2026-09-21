<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\PagoTransaccion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PagoTransaccionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PagoTransaccion');
    }

    public function view(AuthUser $authUser, PagoTransaccion $pagoTransaccion): bool
    {
        return $authUser->can('View:PagoTransaccion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PagoTransaccion');
    }

    public function update(AuthUser $authUser, PagoTransaccion $pagoTransaccion): bool
    {
        return $authUser->can('Update:PagoTransaccion');
    }

    public function delete(AuthUser $authUser, PagoTransaccion $pagoTransaccion): bool
    {
        return $authUser->can('Delete:PagoTransaccion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PagoTransaccion');
    }

    public function restore(AuthUser $authUser, PagoTransaccion $pagoTransaccion): bool
    {
        return $authUser->can('Restore:PagoTransaccion');
    }

    public function forceDelete(AuthUser $authUser, PagoTransaccion $pagoTransaccion): bool
    {
        return $authUser->can('ForceDelete:PagoTransaccion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PagoTransaccion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PagoTransaccion');
    }

    public function replicate(AuthUser $authUser, PagoTransaccion $pagoTransaccion): bool
    {
        return $authUser->can('Replicate:PagoTransaccion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PagoTransaccion');
    }
}
