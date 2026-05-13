<?php

namespace App\Policies\Compras;

use App\Models\Compras\RecepcionCompra;
use App\Models\User;

class RecepcionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RecepcionCompra $recepcion): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RecepcionCompra $recepcion): bool
    {
        // En una cadena de suministro profesional, la recepción es una FE DE HECHOS.
        // No se edita. Si hubo error, se anula y se procesa de nuevo o se ajusta vía devolución.
        return false;
    }

    public function delete(User $user, RecepcionCompra $recepcion): bool
    {
        // El borrado de una recepción es una violación a la integridad del inventario.
        return false;
    }
}
