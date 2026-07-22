<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios\Reportes;

use App\Exports\Servicios\HistoricoPreciosExport;
use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Servicios\ObtenerHistoricoServiciosPrecios;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GenerarHistoricoPreciosExcel extends BaseReporteServicio
{
    public function __construct(
        RegistrarAuditoriaReporte $registrarAuditoria,
        private readonly ObtenerHistoricoServiciosPrecios $obtenerHistorico,
    ) {
        parent::__construct($registrarAuditoria);
    }

    /**
     * @param  array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null}  $filtros
     */
    public function ejecutar(array $filtros = []): BinaryFileResponse
    {
        $this->registrarAuditoria('HTB-SER-001');

        $datos = $this->obtenerHistorico->ejecutar($filtros);

        return Excel::download(new HistoricoPreciosExport($datos), 'HTB-SER-001-Historico-Precios-Servicios.xlsx');
    }
}
