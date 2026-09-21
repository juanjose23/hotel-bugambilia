<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Interactors\Facturacion\ImprimirFactura;
use App\Repository\Models\Facturacion\Factura;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FacturaImpresionController extends Controller
{
    public function imprimir(Factura $factura, ImprimirFactura $interactor): StreamedResponse
    {
        return $interactor->ejecutarPdf($factura, download: false);
    }

    public function pdf(Factura $factura, ImprimirFactura $interactor): StreamedResponse
    {
        return $interactor->ejecutarPdf($factura, download: true);
    }
}
