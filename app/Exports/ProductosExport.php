<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Catalogos\Producto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosExport implements FromView, ShouldAutoSize, WithStyles
{
    /** @var array<string, mixed> */
    protected array $filtros;

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function __construct(array $filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $query = Producto::with(['variantes', 'categoria', 'marca']);

        // Aplicar filtros
        if (! empty($this->filtros['categoria_id'])) {
            $query->where('categoria_id', $this->filtros['categoria_id']);
        }
        if (! empty($this->filtros['marca_id'])) {
            $query->where('marca_id', $this->filtros['marca_id']);
        }
        if (! empty($this->filtros['tipo'])) {
            $query->where('tipo', $this->filtros['tipo']);
        }
        if (! empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }

        return view('exports.productos', [
            'productos' => $query->get(),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Estilo para el encabezado (Fila 1 a 3 son info general, Fila 5 es tabla)
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '711C37']]],
        ];
    }
}
