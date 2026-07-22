<?php

declare(strict_types=1);

namespace App\Interactors\Catalogos\Reportes\Productos;

use App\Actions\Catalogos\GenerarEtiquetasCodigosBarrasAction;
use App\Actions\Catalogos\GenerarReporteProductosAction;
use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use Barryvdh\DomPDF\PDF;

final readonly class GenerarReporteProductosInteractor
{
    public function __construct(
        private GenerarReporteProductosAction $action,
        private GenerarEtiquetasCodigosBarrasAction $etiquetasAction,
    ) {}

    /** @param array<string, mixed> $input */
    public function simple(array $input): PDF
    {
        return $this->action->ejecutar(
            ProductoFiltrosData::fromArray($input),
            false
        );
    }

    /** @param array<string, mixed> $input */
    public function detallado(array $input): PDF
    {
        return $this->action->ejecutar(
            ProductoFiltrosData::fromArray($input),
            true
        );
    }

    /** @param array<string, mixed> $input */
    public function etiquetas(array $input): PDF
    {
        return $this->etiquetasAction->ejecutar(
            ProductoFiltrosData::fromArray($input)
        );
    }
}
