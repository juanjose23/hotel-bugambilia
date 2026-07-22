<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Servicios\ObtenerHistoricoServiciosPrecios;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GenerarHistoricoPreciosPdf extends BaseReporteServicio
{
    public function __construct(
        RegistrarAuditoriaReporte $registrarAuditoria,
        private readonly ObtenerHistoricoServiciosPrecios $obtenerHistorico,
        private readonly ReportePaginador $reportePaginador,
    ) {
        parent::__construct($registrarAuditoria);
    }

    /** @param array<string, mixed> $filtros */
    public function ejecutar(array $filtros = []): Response
    {
        $this->registrarAuditoria('HTB-SER-001');

        $filtrosTipados = array_filter($filtros, fn (string $key) => in_array($key, ['servicio_id', 'moneda_id', 'estado', 'categoria_id'], true), ARRAY_FILTER_USE_KEY);
        /** @var array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null} $filtrosTipados */
        $agrupado = $this->obtenerHistorico->agrupadoPorCategoria($filtrosTipados);

        $filasPorPagina = $this->reportePaginador->filasPorPagina(altoFilaMm: 10, altoEncabezadoMm: 9);
        $paginas = $this->paginarPorCategoria($agrupado, $filasPorPagina);

        return Pdf::loadView('reports.servicios.historico-precios', array_merge($this->getBaseData(), [
            'paginas' => $paginas,
            'codigoReporte' => 'HTB-SER-001',
            'nombreReporte' => 'Histórico de Servicios por Precio por Moneda',
        ]))->download('HTB-SER-001-Historico-Precios-Servicios.pdf');
    }
}
