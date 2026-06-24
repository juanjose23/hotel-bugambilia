<?php

declare(strict_types=1);

namespace App\Exports\Servicios;

use App\UseCases\Servicios\Queries\ObtenerHistoricoServiciosPrecios;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistoricoPreciosExport implements FromView, ShouldAutoSize, WithStyles
{
    /** @var array<string, mixed> */
    private array $filtros;

    /** @param array<string, mixed> $filtros */
    public function __construct(array $filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $rawServicioId = $this->filtros['servicio_id'] ?? null;
        $rawMonedaId = $this->filtros['moneda_id'] ?? null;
        $rawEstado = $this->filtros['estado'] ?? null;
        $rawCategoriaId = $this->filtros['categoria_id'] ?? null;

        $filtros = [
            'servicio_id' => is_numeric($rawServicioId) ? (int) $rawServicioId : null,
            'moneda_id' => is_numeric($rawMonedaId) ? (int) $rawMonedaId : null,
            'estado' => is_numeric($rawEstado) ? (int) $rawEstado : null,
            'categoria_id' => is_numeric($rawCategoriaId) ? (int) $rawCategoriaId : null,
        ];

        /** @var Collection<int, mixed> $data */
        $data = app(ObtenerHistoricoServiciosPrecios::class)->agrupadoPorCategoria($filtros);

        return view('exports.servicios.historico-precios', [
            'agrupado' => $data,
        ]);
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('2')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('4')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('4')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF711C37');
        $sheet->getStyle('4')->getFont()->getColor()->setARGB('FFFFFFFF');
    }
}
