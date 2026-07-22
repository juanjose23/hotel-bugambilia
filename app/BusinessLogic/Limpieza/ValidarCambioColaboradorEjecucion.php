<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class ValidarCambioColaboradorEjecucion
{
    public function validar(LimpiezaEjecucion $model): void
    {
        if (! $model->exists || ! $model->isDirty('colaborador_id') || $model->getOriginal('carrito_id') === null) {
            return;
        }

        $currentUser = auth()->user();
        $originalColaboradorId = $model->getOriginal('colaborador_id');

        $isAssignedColaborador = false;
        if ($currentUser && $originalColaboradorId) {
            $colaboradorUser = Colaborador::with('persona.user')->find($originalColaboradorId);
            if ($colaboradorUser instanceof Colaborador && $colaboradorUser->persona && $colaboradorUser->persona->user) {
                $isAssignedColaborador = ($colaboradorUser->persona->user->id === $currentUser->id);
            }
        }

        $hasPermission = false;
        if ($currentUser) {
            try {
                $hasPermission = $currentUser->hasRole('super_admin')
                    || $currentUser->hasRole('admin');
            } catch (\Throwable) {
                $hasPermission = false;
            }
        }

        if (! $isAssignedColaborador && ! $hasPermission) {
            throw new \Exception('No tiene permisos para cambiar el colaborador de esta limpieza. Solo el colaborador asignado o un administrador pueden liberar el carrito y realizar este cambio.');
        }

        if ($model->carrito_id !== null) {
            throw new \Exception('Debe liberar el carrito (quitar el carrito seleccionado) antes de poder asignar a otro colaborador.');
        }
    }
}
