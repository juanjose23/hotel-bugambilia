<?php

declare(strict_types=1);

namespace App\Actions\Catalogos;

use App\Actions\Catalogos\Concerns\FiltrosProducto;
use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use App\Repository\Models\Catalogos\Producto;
use App\Support\Excel\ColumnaExcel;
use App\Support\Excel\GeneradorExcel;
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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function excel(
        ProductoFiltrosData $filtros,
        bool $incluirVariantes = true,
    ): StreamedResponse {
        $productos = $this->obtenerProductos($filtros, $incluirVariantes);

        if ($incluirVariantes) {
            return (new GeneradorExcel)->descargar(
                coleccion: $this->prepararProductosDetalladosExcel($productos),
                nombre: 'HTB-CP002-Productos-Detallado.xlsx',
                hoja: 'Productos',
                columnas: [
                    ColumnaExcel::make('SKU Producto', fn (array $r) => $r['skuProducto']),
                    ColumnaExcel::make('Producto', fn (array $r) => $r['producto']),
                    ColumnaExcel::make('Categoría', fn (array $r) => $r['categoria']),
                    ColumnaExcel::make('Marca', fn (array $r) => $r['marca']),
                    ColumnaExcel::make('SKU Variante', fn (array $r) => $r['skuVariante']),
                    ColumnaExcel::make('Variante', fn (array $r) => $r['variante']),
                    ColumnaExcel::make('Descripción', fn (array $r) => $r['descripcion']),
                    ColumnaExcel::make('Estado', fn (array $r) => $r['estado']),
                ],
            );
        }

        return (new GeneradorExcel)->descargar(
            coleccion: $productos,
            nombre: 'HTB-CP001-Productos-Simple.xlsx',
            hoja: 'Productos',
            columnas: [
                ColumnaExcel::make('SKU', fn (Producto $p) => $p->codigo ?? '#'.$p->id),
                ColumnaExcel::make('Nombre', fn (Producto $p) => $p->nombre),
                ColumnaExcel::make('Categoría', fn (Producto $p) => $p->categoria->nombre ?? 'N/A'),
                ColumnaExcel::make('Marca', fn (Producto $p) => $p->marca->nombre ?? 'N/A'),
                ColumnaExcel::make('Tipo', fn (Producto $p) => $p->tipo_nombre ?? (string) $p->tipo),
                ColumnaExcel::make('Estado', fn (Producto $p) => $p->estado->label()),
                ColumnaExcel::make('Descripción', fn (Producto $p) => $p->descripcion),
            ],
        );
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

    /**
     * @param  Collection<int, Producto>  $productos
     * @return Collection<int, array{skuProducto: string, producto: string, categoria: string, marca: string, skuVariante: string, variante: string, descripcion: ?string, estado: string}>
     */
    private function prepararProductosDetalladosExcel(Collection $productos): Collection
    {
        /** @var array<int, array{skuProducto: string, producto: string, categoria: string, marca: string, skuVariante: string, variante: string, descripcion: ?string, estado: string}> $filas */
        $filas = [];

        foreach ($productos as $producto) {
            if ($producto->variantes->isEmpty()) {
                $filas[] = $this->filaProductoDetallado(
                    producto: $producto,
                    skuVariante: 'N/A',
                    variante: 'Estándar',
                    descripcion: $producto->descripcion,
                );

                continue;
            }

            foreach ($producto->variantes as $variante) {
                $filas[] = $this->filaProductoDetallado(
                    producto: $producto,
                    skuVariante: $variante->codigo !== '' ? $variante->codigo : 'N/A',
                    variante: $variante->nombre_variante !== '' ? $variante->nombre_variante : 'Estándar',
                    descripcion: is_string($variante->descripcion ?? null) ? $variante->descripcion : $producto->descripcion,
                );
            }
        }

        return collect($filas);
    }

    /**
     * @return array{skuProducto: string, producto: string, categoria: string, marca: string, skuVariante: string, variante: string, descripcion: ?string, estado: string}
     */
    private function filaProductoDetallado(
        Producto $producto,
        string $skuVariante,
        string $variante,
        ?string $descripcion,
    ): array {
        return [
            'skuProducto' => $producto->codigo ?? '#'.$producto->id,
            'producto' => $producto->nombre,
            'categoria' => $producto->categoria->nombre ?? 'N/A',
            'marca' => $producto->marca->nombre ?? 'N/A',
            'skuVariante' => $skuVariante,
            'variante' => $variante,
            'descripcion' => $descripcion,
            'estado' => $producto->estado->label(),
        ];
    }
}
