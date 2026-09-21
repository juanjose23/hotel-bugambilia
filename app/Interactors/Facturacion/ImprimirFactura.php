<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion;

use App\Actions\Facturacion\Impresion\GenerarFacturaPdfAction;
use App\Repository\Models\Facturacion\Factura;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class ImprimirFactura
{
    public function __construct(
        private GenerarFacturaPdfAction $generarPdfAction,
    ) {}

    public function ejecutarPdf(Factura $factura, bool $download = false): StreamedResponse
    {
        return $this->generarPdfAction->ejecutar($factura, $download);
    }
}
