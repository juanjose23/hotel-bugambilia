<?php

declare(strict_types=1);

namespace App\Repository\Policies\Facturacion;

use App\Repository\Models\Facturacion\PasarelaPago;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PasarelaPagoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PasarelaPago');
    }

    public function view(AuthUser $authUser, PasarelaPago $pasarelaPago): bool
    {
        return $authUser->can('View:PasarelaPago');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PasarelaPago');
    }

    public function update(AuthUser $authUser, PasarelaPago $pasarelaPago): bool
    {
        return $authUser->can('Update:PasarelaPago');
    }

    public function delete(AuthUser $authUser, PasarelaPago $pasarelaPago): bool
    {
        return $authUser->can('Delete:PasarelaPago');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PasarelaPago');
    }

    public function restore(AuthUser $authUser, PasarelaPago $pasarelaPago): bool
    {
        return $authUser->can('Restore:PasarelaPago');
    }

    public function forceDelete(AuthUser $authUser, PasarelaPago $pasarelaPago): bool
    {
        return $authUser->can('ForceDelete:PasarelaPago');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PasarelaPago');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PasarelaPago');
    }

    public function replicate(AuthUser $authUser, PasarelaPago $pasarelaPago): bool
    {
        return $authUser->can('Replicate:PasarelaPago');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PasarelaPago');
    }
}
