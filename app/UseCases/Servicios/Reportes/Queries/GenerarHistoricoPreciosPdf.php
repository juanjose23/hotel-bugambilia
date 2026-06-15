<?php

declare(strict_types=1);

namespace App\UseCases\Servicios\Reportes\Queries;

use App\Support\ReportePaginador;
use App\UseCases\Servicios\Queries\ObtenerHistoricoServiciosPrecios;
use App\UseCases\Servicios\Reportes\BaseReporteServicio;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class GenerarHistoricoPreciosPdf extends BaseReporteServicio
{
    /**
     * @param  array{categoria_id?: int|null, servicio_id?: int|null, moneda_id?: int|null, estado?: int|null}  $filtros
     */
    public function ejecutar(array $filtros = []): PdfBuilder
    {
        $this->registrarAuditoria('HTB-SER-001');

        $agrupado = app(ObtenerHistoricoServiciosPrecios::class)->agrupadoPorCategoria($filtros);

        $filasPorPagina = ReportePaginador::filasPorPaginaSpatie(theadPx: 26, rowPx: 24, safety: 1);
        $paginas = $this->paginarPorCategoria($agrupado, $filasPorPagina);

        return Pdf::view('reports.servicios.historico-precios', array_merge($this->getBaseData(), [
            'paginas' => $paginas,
            'codigoReporte' => 'HTB-SER-001',
            'nombreReporte' => 'Histórico de Servicios por Precio por Moneda',
        ]))->name('HTB-SER-001-Historico-Precios-Servicios.pdf')->download();
    }
}
