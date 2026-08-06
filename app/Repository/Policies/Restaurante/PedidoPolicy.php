<?php

declare(strict_types=1);

namespace App\Repository\Policies\Restaurante;

use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PedidoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pedido');
    }

    public function view(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('View:Pedido');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pedido');
    }

    public function update(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('Update:Pedido');
    }

    public function delete(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('Delete:Pedido');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Pedido');
    }

    public function restore(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('Restore:Pedido');
    }

    public function forceDelete(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('ForceDelete:Pedido');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pedido');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pedido');
    }

    public function replicate(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('Replicate:Pedido');
    }

    public function viewComanda(AuthUser $authUser, Pedido $pedido): bool
    {
        return $authUser->can('View:Pedido') || $authUser->can('Restaurante:ImprimirComanda');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pedido');
    }
}
