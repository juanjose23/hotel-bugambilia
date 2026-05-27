<?php

declare(strict_types=1);

namespace App\Exports\Activos;

use App\Models\Activos\Activo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivosExport implements FromView, ShouldAutoSize, WithStyles
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function __construct(protected array $filtros = []) {}

    public function view(): View
    {
        $query = Activo::with([
            'producto',
            'variante',
            'asignacionActiva.asignable',
            'proveedor.persona',
            'moneda',
        ]);

        // Aplicar filtros
        if (! empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }
        if (! empty($this->filtros['producto_id'])) {
            $query->where('producto_id', $this->filtros['producto_id']);
        }
        if (! empty($this->filtros['ubicacion_tipo'])) {
            $type = $this->filtros['ubicacion_tipo'];
            $query->whereHas('asignacionActiva', function ($q) use ($type) {
                $q->where('asignable_type', $type);
            });
        }

        return view('exports.activos.inventario-general', [
            'activos' => $query->get(),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '711C37']]],
            2 => ['font' => ['italic' => true, 'size' => 10]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '711C37']],
            ],
        ];
    }
}
