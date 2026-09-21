<?php

declare(strict_types=1);

namespace App\Actions\Inventario;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;

final class GenerarHojaInicialInventarioFisicoAction
{
    /**
     * Generates a default pre-populated Univer Sheet JSON configuration
     * with all currently active lotes (not Agotado).
     *
     * @return array<string, mixed>
     */
    public function ejecutar(): array
    {
        $lotes = Lote::with(['producto', 'ubicacion'])
            ->where('estado', '!=', EstadoLote::Agotado)->get();

        $cellData = [];

        // Row 0: Header style & value
        $cellData['0'] = [
            '0' => ['v' => 'ID Lote', 's' => ['bl' => 1]],
            '1' => ['v' => 'Código Lote', 's' => ['bl' => 1]],
            '2' => ['v' => 'Producto', 's' => ['bl' => 1]],
            '3' => ['v' => 'Ubicación', 's' => ['bl' => 1]],
            '4' => ['v' => 'Stock Sistema', 's' => ['bl' => 1]],
            '5' => ['v' => 'Cantidad Física', 's' => ['bl' => 1]],
            '6' => ['v' => 'Diferencia (Fórm.)', 's' => ['bl' => 1]],
            '7' => ['v' => 'Notas / Observaciones', 's' => ['bl' => 1]],
        ];

        $rowIndex = 1;
        foreach ($lotes as $lote) {
            $rowStr = (string) $rowIndex;

            // Formula is =F{RowIndex+1}-E{RowIndex+1} (Quantity Physical - Stock System)
            $rowNum = $rowIndex + 1;
            $formula = "=F{$rowNum}-E{$rowNum}";

            $cellData[$rowStr] = [
                '0' => ['v' => $lote->id],
                '1' => ['v' => $lote->codigo_lote],
                '2' => ['v' => $lote->producto ? $lote->producto->nombre : 'Sin producto'],
                '3' => ['v' => $lote->ubicacion->nombre ?? 'Sin Ubicación'],
                '4' => ['v' => (float) $lote->cantidad_disponible],
                '5' => ['v' => (float) $lote->cantidad_disponible], // Default physical equal to system initially
                '6' => ['f' => $formula],
                '7' => ['v' => ''],
            ];
            $rowIndex++;
        }

        return [
            'id' => 'workbook-inventario-'.time(),
            'sheetOrder' => ['sheet-1'],
            'sheets' => [
                'sheet-1' => [
                    'id' => 'sheet-1',
                    'name' => 'Físico vs Sistema',
                    'rowCount' => max(100, $rowIndex + 20),
                    'columnCount' => 10,
                    'cellData' => $cellData,
                ],
            ],
        ];
    }
}
