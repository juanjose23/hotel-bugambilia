<?php

declare(strict_types=1);

namespace App\UseCases\Servicios\Reportes\Queries;

use App\Exports\Servicios\HistoricoPreciosExport;
use App\UseCases\Servicios\Reportes\BaseReporteServicio;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GenerarHistoricoPreciosExcel extends BaseReporteServicio
{
    /**
     * @param  array{categoria_id?: int|null, servicio_id?: int|null, moneda_id?: int|null, estado?: int|null}  $filtros
     */
    public function ejecutar(array $filtros = []): BinaryFileResponse
    {
        $this->registrarAuditoria('HTB-SER-001');

        return Excel::download(new HistoricoPreciosExport($filtros), 'HTB-SER-001-Historico-Precios-Servicios.xlsx');
    }
}
