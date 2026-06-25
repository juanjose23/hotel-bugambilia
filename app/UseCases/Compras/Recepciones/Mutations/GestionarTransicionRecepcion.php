<?php

namespace App\UseCases\Compras\Recepciones\Mutations;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\RecepcionCompra;
use App\Services\Compras\NotificadorCompras;

class GestionarTransicionRecepcion
{
    public function execute(RecepcionCompra $recepcion, EstadoRecepcion $nuevoEstado): RecepcionCompra
    {
        throw_unless(
            $recepcion->estado->transicionPermitida($nuevoEstado),
            \InvalidArgumentException::class,
            "No se permite la transición de {$recepcion->estado->label()} a {$nuevoEstado->label()}."
        );

        $recepcion->update(['estado' => $nuevoEstado]);

        match ($nuevoEstado) {
            EstadoRecepcion::Completa => app(NotificadorCompras::class)->recepcionCompletada($recepcion),
            EstadoRecepcion::Rechazada => app(NotificadorCompras::class)->recepcionRechazada($recepcion),
            EstadoRecepcion::ConDiscrepancia => app(NotificadorCompras::class)->recepcionConDiscrepancia($recepcion),
            default => null,
        };

        return $recepcion->fresh() ?? $recepcion;
    }
}
