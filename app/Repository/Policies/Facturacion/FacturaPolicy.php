<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\Factura;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacturaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Factura');
    }

    public function view(AuthUser $authUser, Factura $factura): bool
    {
        return $authUser->can('View:Factura');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Factura');
    }

    public function update(AuthUser $authUser, Factura $factura): bool
    {
        return $authUser->can('Update:Factura');
    }

    public function delete(AuthUser $authUser, Factura $factura): bool
    {
        return $authUser->can('Delete:Factura');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Factura');
    }

    public function restore(AuthUser $authUser, Factura $factura): bool
    {
        return $authUser->can('Restore:Factura');
    }

    public function forceDelete(AuthUser $authUser, Factura $factura): bool
    {
        return $authUser->can('ForceDelete:Factura');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Factura');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Factura');
    }

    public function replicate(AuthUser $authUser, Factura $factura): bool
    {
        return $authUser->can('Replicate:Factura');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Factura');
    }
}
