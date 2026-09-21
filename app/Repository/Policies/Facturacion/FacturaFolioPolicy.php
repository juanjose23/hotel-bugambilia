<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\FacturaFolio;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacturaFolioPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FacturaFolio');
    }

    public function view(AuthUser $authUser, FacturaFolio $facturaFolio): bool
    {
        return $authUser->can('View:FacturaFolio');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FacturaFolio');
    }

    public function update(AuthUser $authUser, FacturaFolio $facturaFolio): bool
    {
        return $authUser->can('Update:FacturaFolio');
    }

    public function delete(AuthUser $authUser, FacturaFolio $facturaFolio): bool
    {
        return $authUser->can('Delete:FacturaFolio');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FacturaFolio');
    }

    public function restore(AuthUser $authUser, FacturaFolio $facturaFolio): bool
    {
        return $authUser->can('Restore:FacturaFolio');
    }

    public function forceDelete(AuthUser $authUser, FacturaFolio $facturaFolio): bool
    {
        return $authUser->can('ForceDelete:FacturaFolio');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FacturaFolio');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FacturaFolio');
    }

    public function replicate(AuthUser $authUser, FacturaFolio $facturaFolio): bool
    {
        return $authUser->can('Replicate:FacturaFolio');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FacturaFolio');
    }
}
