<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\FacturaSerie;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacturaSeriePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FacturaSerie');
    }

    public function view(AuthUser $authUser, FacturaSerie $facturaSerie): bool
    {
        return $authUser->can('View:FacturaSerie');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FacturaSerie');
    }

    public function update(AuthUser $authUser, FacturaSerie $facturaSerie): bool
    {
        return $authUser->can('Update:FacturaSerie');
    }

    public function delete(AuthUser $authUser, FacturaSerie $facturaSerie): bool
    {
        return $authUser->can('Delete:FacturaSerie');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FacturaSerie');
    }

    public function restore(AuthUser $authUser, FacturaSerie $facturaSerie): bool
    {
        return $authUser->can('Restore:FacturaSerie');
    }

    public function forceDelete(AuthUser $authUser, FacturaSerie $facturaSerie): bool
    {
        return $authUser->can('ForceDelete:FacturaSerie');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FacturaSerie');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FacturaSerie');
    }

    public function replicate(AuthUser $authUser, FacturaSerie $facturaSerie): bool
    {
        return $authUser->can('Replicate:FacturaSerie');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FacturaSerie');
    }
}
