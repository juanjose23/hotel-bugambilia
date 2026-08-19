<?php

declare(strict_types=1);

namespace App\Interactors\Catalogos\Productos;

use App\Actions\Catalogos\GenerarEtiquetasCodigosBarrasAction;
use App\Actions\Catalogos\GenerarReporteProductosAction;
use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use Barryvdh\DomPDF\PDF;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarReporteProductos
{
    public function __construct(
        private GenerarReporteProductosAction $action,
        private GenerarEtiquetasCodigosBarrasAction $etiquetasAction,
        private RegistrarAuditoriaReporte $auditoria,
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

    /** @param array<string, mixed> $input */
    public function excel(array $input, bool $incluirVariantes = false): StreamedResponse
    {
        $this->auditoria->ejecutar($incluirVariantes ? 'HTB-CP002' : 'HTB-CP001', [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
            'formato' => 'excel',
        ]);

        return $this->action->excel(
            ProductoFiltrosData::fromArray($input),
            $incluirVariantes
        );
    }
}
