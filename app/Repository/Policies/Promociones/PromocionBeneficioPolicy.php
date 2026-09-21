<?php

declare(strict_types=1);

namespace App\Repository\Policies\Promociones;

use App\Repository\Models\Promociones\PromocionBeneficio;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PromocionBeneficioPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PromocionBeneficio');
    }

    public function view(AuthUser $authUser, PromocionBeneficio $promocionBeneficio): bool
    {
        return $authUser->can('View:PromocionBeneficio');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PromocionBeneficio');
    }

    public function update(AuthUser $authUser, PromocionBeneficio $promocionBeneficio): bool
    {
        return $authUser->can('Update:PromocionBeneficio');
    }

    public function delete(AuthUser $authUser, PromocionBeneficio $promocionBeneficio): bool
    {
        return $authUser->can('Delete:PromocionBeneficio');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PromocionBeneficio');
    }

    public function restore(AuthUser $authUser, PromocionBeneficio $promocionBeneficio): bool
    {
        return $authUser->can('Restore:PromocionBeneficio');
    }

    public function forceDelete(AuthUser $authUser, PromocionBeneficio $promocionBeneficio): bool
    {
        return $authUser->can('ForceDelete:PromocionBeneficio');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PromocionBeneficio');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PromocionBeneficio');
    }

    public function replicate(AuthUser $authUser, PromocionBeneficio $promocionBeneficio): bool
    {
        return $authUser->can('Replicate:PromocionBeneficio');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PromocionBeneficio');
    }
}
