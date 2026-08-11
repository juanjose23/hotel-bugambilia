<?php

declare(strict_types=1);

namespace App\Actions\Catalogos;

use App\Actions\Catalogos\Concerns\FiltrosProducto;
use App\BusinessLogic\Catalogos\Data\EtiquetaProductoData;
use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Support\Barcode\BarcodeColor;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\Barcode\BarcodeType;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TipoPaginaResolver;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;
use Illuminate\Support\Collection;

final class GenerarEtiquetasCodigosBarrasAction
{
    use FiltrosProducto, GuardaReporte;

    private const string CODIGO_REPORTE = 'HTB-CP003';

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
    ) {}

    public function ejecutar(ProductoFiltrosData $filtros): PdfDocumento
    {
        [$tamanoPapel, $orientacion] = app(TipoPaginaResolver::class)
            ->resolver($filtros->tipoPagina);

        $layout = new LayoutPdf(
            tamano: $tamanoPapel,
            orientacion: $orientacion,
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $productos = $this->obtenerProductos($filtros);
        $etiquetas = $this->construirEtiquetas($productos);

        $etiquetasArray = collect($etiquetas)->map(
            fn (EtiquetaProductoData $etiqueta) => $etiqueta->toArray()
        );

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $etiquetasArray,
            tipo: TiposReporte::ETIQUETA,
        );

        $pdf = Pdf::loadView('reports.catalogos.etiquetas', [
            'paginas' => $paginas,
            'totalRegistros' => $etiquetasArray->count(),
            'datosHotel' => HotelInfo::getBaseData(),
            'codigoReporte' => self::CODIGO_REPORTE,
            'nombreReporte' => 'Etiquetas de Códigos de Barras',
            'filtrosResueltos' => $this->prepararFiltros($filtros),
            'columnas' => TiposReporte::ETIQUETA->configuracion()->columnas ?? 4,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
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
     * @param  Collection<int, Producto>  $productos
     * @return array<int, EtiquetaProductoData>
     */
    private function construirEtiquetas(Collection $productos): array
    {
        return $productos
            ->flatMap(fn (Producto $producto) => $this->buildEtiquetasProducto($producto))
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, EtiquetaProductoData>
     */
    private function buildEtiquetasProducto(Producto $producto): Collection
    {
        $codigoBase = $this->resolverCodigo($producto->codigo, $producto->id);

        if ($producto->variantes->isNotEmpty()) {
            return $producto->variantes->map(
                fn (ProductoVariante $variante) => $this->buildEtiqueta(
                    nombreProducto: $producto->nombre ?? '',
                    nombreVariante: $variante->nombre_variante ?? 'Estándar',
                    codigo: $this->resolverCodigo($variante->codigo, $producto->id),
                )
            );
        }

        return collect([$this->buildEtiqueta(
            nombreProducto: $producto->nombre ?? '',
            nombreVariante: 'Estándar',
            codigo: $codigoBase,
        )]);
    }

    private function buildEtiqueta(
        string $nombreProducto,
        string $nombreVariante,
        string $codigo,
    ): EtiquetaProductoData {
        $barcode = $this->barcodeGenerator->data(
            code: $codigo,
            type: BarcodeType::Code128,
            height: 70,
            color: BarcodeColor::HOTEL,
        );

        return new EtiquetaProductoData(
            producto: $nombreProducto,
            variante: $nombreVariante,
            codigo: $codigo,
            barcodeBase64: $barcode->base64,
        );
    }

    private function resolverCodigo(?string $codigo, int $productoId): string
    {
        return (! empty($codigo)) ? $codigo : 'SKU-'.$productoId;
    }

    /**
     * @return Collection<int, Producto>
     */
    private function obtenerProductos(
        ProductoFiltrosData $filtros,
    ): Collection {
        $query = Producto::query()->with('variantes');

        $this->aplicarFiltrosQuery($query, $filtros);

        if ($filtros->productoId !== null) {
            $query->whereKey($filtros->productoId);
        }

        return $query
            ->orderBy('nombre')
            ->get();
    }
}
