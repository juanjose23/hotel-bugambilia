<?php

declare(strict_types=1);

namespace App\Actions\Activos;

use App\Actions\Activos\Concerns\FiltrosActivos;
use App\BusinessLogic\Activos\Data\ActivoFiltrosData;
use App\BusinessLogic\Catalogos\Data\EtiquetaProductoData;
use App\Repository\Models\Activos\Activo;
use App\Support\Barcode\BarcodeColor;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TipoPaginaResolver;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;
use Illuminate\Support\Collection;

final class GenerarEtiquetasCodigosBarrasActivosAction
{
    use FiltrosActivos, GuardaReporte;

    private const string CODIGO_REPORTE = 'HTB-ACT-004';

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly TipoPaginaResolver $tipoPaginaResolver,
    ) {}

    public function ejecutar(ActivoFiltrosData $filtros): PdfDocumento
    {
        [$tamanoPapel, $orientacion] = $this->tipoPaginaResolver
            ->resolver($filtros->tipoPagina);

        $layout = new LayoutPdf(
            tamano: $tamanoPapel,
            orientacion: $orientacion,
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $activos = $this->obtenerActivos($filtros);
        $etiquetas = $this->construirEtiquetas($activos);

        $etiquetasArray = collect($etiquetas)->map(
            fn (EtiquetaProductoData $etiqueta) => $etiqueta->toArray()
        );

        $columnas = TiposReporte::ETIQUETA->configuracion()->columnas ?? 3;
        $filtrosResueltos = $this->prepararFiltros($filtros);

        $paginas = $this->paginarEtiquetas(
            etiquetas: $etiquetasArray,
            paginador: new ReportePaginador($layout),
            columnas: $columnas,
            reservarFilaFiltros: $filtrosResueltos !== [],
        );

        $pdf = Pdf::loadView('reports.activos.etiquetas', [
            'paginas' => $paginas,
            'totalRegistros' => $etiquetasArray->count(),
            'datosHotel' => HotelInfo::getBaseData(),
            'codigoReporte' => self::CODIGO_REPORTE,
            'nombreReporte' => 'Etiquetas de Códigos de Barras de Activos',
            'filtrosResueltos' => $filtrosResueltos,
            'columnas' => $columnas,
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
            tipoReporte: self::CODIGO_REPORTE,
            parametros: $filtros->toArray(),
            pdf: $pdf,
        );

        return $pdf;
    }

    /**
     * @param  Collection<int, Activo>  $activos
     * @return array<int, EtiquetaProductoData>
     */
    private function construirEtiquetas(Collection $activos): array
    {
        $etiquetas = [];

        foreach ($activos as $activo) {
            $codigo = $activo->codigo_inventario;

            if (empty($codigo)) {
                continue;
            }

            $etiquetas[] = new EtiquetaProductoData(
                producto: $activo->producto->nombre ?? 'Activo',
                variante: $activo->variante->nombre_variante ?? 'General',
                codigo: $codigo,
                barcodeBase64: $this->barcodeGenerator->data(
                    code: $codigo,
                    color: BarcodeColor::HOTEL,
                )->base64,
            );
        }

        return $etiquetas;
    }

    /**
     * @return Collection<int, Activo>
     */
    private function obtenerActivos(
        ActivoFiltrosData $filtros,
    ): Collection {
        $query = Activo::query()->with(['producto', 'variante']);

        $this->aplicarFiltrosQuery($query, $filtros);

        if ($filtros->activoId !== null) {
            $query->whereKey($filtros->activoId);
        }

        return $query
            ->orderBy('codigo_inventario')
            ->get();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $etiquetas
     * @return array<int, Collection<int, array<string, mixed>>>
     */
    private function paginarEtiquetas(
        Collection $etiquetas,
        ReportePaginador $paginador,
        int $columnas,
        bool $reservarFilaFiltros,
    ): array {
        $config = TiposReporte::ETIQUETA->configuracion();
        $porPagina = $paginador->etiquetasPorPagina(
            altoEtiquetaMm: $config->altoFilaMm,
            columnas: $columnas,
        );

        $primeraPagina = $reservarFilaFiltros
            ? max($columnas, $porPagina - $columnas)
            : $porPagina;

        if ($etiquetas->count() <= $primeraPagina) {
            return [$etiquetas->values()];
        }

        return array_merge(
            [$etiquetas->take($primeraPagina)->values()],
            $etiquetas
                ->slice($primeraPagina)
                ->values()
                ->chunk($porPagina)
                ->map(fn (Collection $pagina): Collection => $pagina->values())
                ->values()
                ->all(),
        );
    }
}
