<?php

declare(strict_types=1);

namespace App\Actions\Catalogos;

use App\Actions\Catalogos\Concerns\FiltrosProducto;
use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use App\Repository\Models\Catalogos\Producto;
use App\Support\HotelInfo;
use App\Support\Pdf\Calculadores\CalculadorAlturaProducto;
use App\Support\Pdf\Calculadores\CalculadorTablaDetalle;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TipoPaginaResolver;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;
use Illuminate\Support\Collection;

final class GenerarReporteProductosAction
{
    use FiltrosProducto, GuardaReporte;

    public function __construct(
        private readonly TipoPaginaResolver $tipoPaginaResolver,
    ) {}

    public function ejecutar(
        ProductoFiltrosData $filtros,
        bool $incluirVariantes = true,
    ): PdfDocumento {
        [$tamanoPapel, $orientacion] = $this->tipoPaginaResolver
            ->resolver($filtros->tipoPagina);

        $productos = $this->obtenerProductos($filtros, $incluirVariantes);
        $filtrosResueltos = $this->prepararFiltros($filtros);

        $layout = new LayoutPdf(
            tamano: $tamanoPapel,
            orientacion: $orientacion,
        );

        $paginador = new ReportePaginador($layout);

        $paginas = $paginador->paginar(
            items: $productos,
            tipo: $incluirVariantes ? TiposReporte::TABLA_DETALLE : TiposReporte::TABLA_SIMPLE,
            calculador: $incluirVariantes
                ? new CalculadorAlturaProducto
                : new CalculadorTablaDetalle,
            altoExtraPrimeraPaginaMm: $filtrosResueltos !== [] ? 10 : 0,
        );

        $nombreReporte = $incluirVariantes
            ? 'Reporte Detallado de Productos'
            : 'Reporte Simple de Productos';

        $codigoReporte = $incluirVariantes
            ? 'HTB-CP002'
            : 'HTB-CP001';

        $datosHotel = HotelInfo::getBaseData();

        $pdf = Pdf::loadView('reports.catalogos.productos', [
            'paginas' => $paginas,
            'incluirVariantes' => $incluirVariantes,
            'nombreReporte' => $nombreReporte,
            'codigoReporte' => $codigoReporte,
            'datosHotel' => $datosHotel,
            'totalRegistros' => $productos->count(),
            'filtrosResueltos' => $filtrosResueltos,
            'pageSize' => $tamanoPapel->cssName(),
            'orientation' => $orientacion->cssName(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ])->setPaper(
            $tamanoPapel->dompdfName(),
            $orientacion->dompdfName(),
        );

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: $filtros->toArray(),
            pdf: $pdf,
        );

        return $pdf;
    }

    /**
     * @return Collection<int, Producto>
     */
    private function obtenerProductos(
        ProductoFiltrosData $filtros,
        bool $incluirVariantes,
    ): Collection {
        $query = Producto::query()
            ->with(['categoria', 'marca']);

        if ($incluirVariantes) {
            $query->with('variantes');
        }

        return $this->aplicarFiltrosQuery($query, $filtros)
            ->orderBy('nombre')
            ->get();
    }
}
