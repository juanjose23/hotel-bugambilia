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
        /** @var Collection<int, mixed> $data */
        $data = app(ObtenerHistoricoServiciosPrecios::class)->agrupadoPorCategoria($this->filtros);

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
