<?php

namespace App\Http\Controllers\Catalogos;

use App\Actions\Catalogos\GenerarEtiquetasCodigosBarrasAction;
use App\Actions\Catalogos\GenerarReporteProductosAction;
use App\Models\Catalogos\Producto;
use Illuminate\Http\Response;

class ReportesCodigosBarrasController
{
    public function reporteProductosVariantes(?Producto $producto = null): Response
    {
        $action = new GenerarReporteProductosAction;
        $pdf = $action->ejecutar($producto ? ['id' => $producto->id] : []);

        return $pdf->download('reporte-productos-variantes-'.now()->format('Y-m-d-H-i-s').'.pdf');
    }

    public function etiquetasCodigosBarras(?int $productoId = null): Response
    {
        $action = new GenerarEtiquetasCodigosBarrasAction;
        $pdf = $action->ejecutar($productoId);

        return $pdf->download('etiquetas-codigos-barras-'.now()->format('Y-m-d-H-i-s').'.pdf');
    }

    public function previewEtiquetasCodigosBarras(?int $productoId = null): Response
    {
        $action = new GenerarEtiquetasCodigosBarrasAction;
        $pdf = $action->ejecutar($productoId);

        return $pdf->stream('etiquetas-codigos-barras.pdf');
    }
}
