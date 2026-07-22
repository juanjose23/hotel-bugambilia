<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Recepciones;

use App\BusinessLogic\Compras\ValidarTransicionRecepcion;
use App\Enums\Compras\EstadoRecepcion;
use App\Events\Compras\RecepcionCompletada;
use App\Events\Compras\RecepcionConDiscrepancia;
use App\Events\Compras\RecepcionRechazada;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Persistencia\Compras\RecepcionRepositorioInterface;

final class GestionarTransicionRecepcion
{
    public function __construct(
        private readonly ValidarTransicionRecepcion $validarTransicion,
        private readonly RecepcionRepositorioInterface $recepcionRepositorio,
    ) {}

    public function ejecutar(RecepcionCompra $recepcion, EstadoRecepcion $nuevoEstado): RecepcionCompra
    {
        $estadoActual = $recepcion->estado;
        throw_unless(
            $this->validarTransicion->esPermitida($estadoActual, $nuevoEstado),
            \InvalidArgumentException::class,
            'No se permite la transición de '.$estadoActual->label()." a {$nuevoEstado->label()}."
        );

        $this->recepcionRepositorio->actualizarEstado($recepcion, $nuevoEstado);

        match ($nuevoEstado) {
            EstadoRecepcion::Completa => RecepcionCompletada::dispatch($recepcion),
            EstadoRecepcion::Rechazada => RecepcionRechazada::dispatch($recepcion),
            EstadoRecepcion::ConDiscrepancia => RecepcionConDiscrepancia::dispatch($recepcion),
            default => null,
        };

        return $recepcion->fresh() ?? $recepcion;
    }
}
