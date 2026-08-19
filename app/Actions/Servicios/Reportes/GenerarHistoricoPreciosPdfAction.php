<?php

declare(strict_types=1);

namespace App\Actions\Servicios\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Servicios\ObtenerHistoricoServiciosPrecios;
use App\Support\HotelInfo;
use App\Support\Pdf\LayoutPdf;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

final class GenerarHistoricoPreciosPdfAction
{
    public function __construct(
        private readonly RegistrarAuditoriaReporte $registrarAuditoria,
        private readonly ObtenerHistoricoServiciosPrecios $obtenerHistorico,
    ) {}

    /** @param array<string, mixed> $filtros */
    public function ejecutar(array $filtros = []): Response
    {
        $this->registrarAuditoria->ejecutar('HTB-SER-001', [
            'usuario' => Auth::id(),
            'ip' => request()->ip(),
        ]);

        $filtrosTipados = array_filter($filtros, fn (string $key) => in_array($key, ['servicio_id', 'moneda_id', 'estado', 'categoria_id'], true), ARRAY_FILTER_USE_KEY);
        /** @var array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null} $filtrosTipados */
        $agrupado = $this->obtenerHistorico->agrupadoPorCategoria($filtrosTipados);

        $layout = new LayoutPdf;
        $filasPorPagina = (new ReportePaginador($layout))
            ->filasPorPagina(altoFilaMm: 10, altoEncabezadoMm: 9);
        $paginas = $this->paginarPorCategoria($agrupado, $filasPorPagina);

        $baseData = array_merge(HotelInfo::getBaseData(), [
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return Pdf::loadView('reports.servicios.historico-precios', array_merge($baseData, [
            'paginas' => $paginas,
            'codigoReporte' => 'HTB-SER-001',
            'nombreReporte' => 'Historico de Servicios por Precio por Moneda',
            'pageSize' => $layout->tamano->cssName(),
            'orientation' => $layout->orientacion->cssName(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ]))->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        )->download('HTB-SER-001-Historico-Precios-Servicios.pdf');
    }

    /**
     * @param  iterable<string, iterable<mixed>>  $agrupado
     * @return array<int, array<int, array{tipo: string, categoria?: string, item?: mixed}>>
     */
    private function paginarPorCategoria(iterable $agrupado, int $filasPorPagina): array
    {
        $filas = [];
        foreach ($agrupado as $categoria => $items) {
            $filas[] = ['tipo' => 'categoria', 'categoria' => $categoria];
            foreach ($items as $item) {
                $filas[] = ['tipo' => 'item', 'item' => $item];
            }
        }

        if (empty($filas)) {
            return [];
        }

        $filasCollection = collect($filas);

        $chunks = $filasCollection->chunk($filasPorPagina)
            ->map(fn ($c) => $c->values()->all())
            ->values()
            ->all();

        $totalChunks = count($chunks);
        for ($i = 0; $i < $totalChunks - 1; $i++) {
            $lastIndex = count($chunks[$i]) - 1;
            if ($lastIndex >= 0 && $chunks[$i][$lastIndex]['tipo'] === 'categoria') {
                $header = $chunks[$i][$lastIndex];
                array_splice($chunks[$i], $lastIndex, 1);
                array_unshift($chunks[$i + 1], $header);
            }
        }

        return array_values(array_filter($chunks, fn ($c) => count($c) > 0));
    }
}
