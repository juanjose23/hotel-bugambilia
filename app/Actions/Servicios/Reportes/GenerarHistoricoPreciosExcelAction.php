<?php

declare(strict_types=1);

namespace App\Actions\Servicios\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Servicios\ObtenerHistoricoServiciosPrecios;
use App\Support\Excel\ColumnaExcel;
use App\Support\Excel\GeneradorExcel;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerarHistoricoPreciosExcelAction
{
    public function __construct(
        private readonly RegistrarAuditoriaReporte $registrarAuditoria,
        private readonly ObtenerHistoricoServiciosPrecios $obtenerHistorico,
    ) {}

    /**
     * @param  array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null}  $filtros
     */
    public function ejecutar(array $filtros = []): StreamedResponse
    {
        $this->registrarAuditoria->ejecutar('HTB-SER-001', [
            'usuario' => Auth::id(),
            'ip' => request()->ip(),
        ]);

        $datos = $this->obtenerHistorico->ejecutar($filtros);

        return (new GeneradorExcel)->descargar(
            coleccion: $datos,
            nombre: 'HTB-SER-001-Historico-Precios-Servicios.xlsx',
            hoja: 'Histórico Precios',
            columnas: [
                ColumnaExcel::make('Código', fn ($r) => $r->servicio_codigo ?? 'N/A'),
                ColumnaExcel::make('Servicio', fn ($r) => $r->servicio),
                ColumnaExcel::make('Categoría', fn ($r) => $r->categoria ?? 'Sin categoría'),
                ColumnaExcel::make('Moneda', fn ($r) => $r->moneda ?? 'N/A'),
                ColumnaExcel::make('Precio', fn ($r) => (float) $r->precio, numerica: true),
                ColumnaExcel::make('Fecha Inicio', fn ($r) => $r->fecha_inicio ?? 'N/A'),
                ColumnaExcel::make('Fecha Fin', fn ($r) => $r->fecha_fin ?? 'N/A'),
                ColumnaExcel::make('Estado', fn ($r) => (int) $r->estado === 1 ? 'Vigente' : 'No Vigente'),
            ],
        );
    }
}
