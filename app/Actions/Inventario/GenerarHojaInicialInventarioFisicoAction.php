<?php

declare(strict_types=1);

namespace App\Actions\Inventario;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;

final class GenerarHojaInicialInventarioFisicoAction
{
    public const int LIMITE_MAXIMO_LOTES = 500;

    /**
     * Generates a default pre-populated Univer Sheet JSON configuration
     * with active lotes, optionally filtered by location or category.
     *
     * @return array<string, mixed>
     */
    public function ejecutar(
        ?int $ubicacionId = null,
        ?int $categoriaId = null,
        bool $precargarIgual = true,
        bool $conteoCiego = false,
        int $limite = self::LIMITE_MAXIMO_LOTES,
    ): array {
        $cellData = [];
        $cellData['0'] = $this->encabezados();

        $query = Lote::query()
            ->select(['id', 'codigo_lote', 'producto_id', 'ubicacion_id', 'cantidad_disponible'])
            ->with([
                'producto' => fn ($q) => $q->select(['id', 'nombre', 'categoria_id']),
                'ubicacion' => fn ($q) => $q->select(['id', 'nombre']),
            ])
            ->where('estado', '!=', EstadoLote::Agotado);

        if ($ubicacionId !== null && $ubicacionId > 0) {
            $descendientes = Ubicacion::obtenerDescendientesIds($ubicacionId);
            $query->whereIn('ubicacion_id', $descendientes);
        }

        if ($categoriaId !== null && $categoriaId > 0) {
            $query->whereHas('producto', function ($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId);
            });
        }

        $lotes = $query->orderBy('ubicacion_id')
            ->orderBy('id')
            ->limit($limite > 0 ? $limite : self::LIMITE_MAXIMO_LOTES)
            ->get();

        $rowIndex = 1;
        foreach ($lotes as $lote) {
            $rowStr = (string) $rowIndex;
            $rowNum = $rowIndex + 1;
            $formula = "=F{$rowNum}-E{$rowNum}";

            $stockSistema = (float) $lote->cantidad_disponible;
            $cantidadFisica = $precargarIgual && ! $conteoCiego ? $stockSistema : '';

            $cellData[$rowStr] = [
                '0' => ['v' => $lote->id],
                '1' => ['v' => $lote->codigo_lote],
                '2' => ['v' => $lote->producto ? $lote->producto->nombre : 'Sin producto'],
                '3' => ['v' => $lote->ubicacion->nombre ?? 'Sin Ubicación'],
                '4' => ['v' => $conteoCiego ? 'Oculto (Ciego)' : $stockSistema],
                '5' => ['v' => $cantidadFisica],
                '6' => ['f' => $conteoCiego ? '' : $formula],
                '7' => ['v' => ''],
            ];
            $rowIndex++;
        }

        return $this->construirEstructuraLibro($cellData, $rowIndex);
    }

    /**
     * Devuelve una plantilla de hoja vacía con solo la fila de encabezados.
     * Evita sobrecargar la memoria al abrir el formulario inicial de creación.
     *
     * @return array<string, mixed>
     */
    public function plantillaVacia(): array
    {
        $cellData = [];
        $cellData['0'] = $this->encabezados();

        return $this->construirEstructuraLibro($cellData, 1);
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function encabezados(): array
    {
        return [
            '0' => ['v' => 'ID Lote', 's' => ['bl' => 1]],
            '1' => ['v' => 'Código Lote', 's' => ['bl' => 1]],
            '2' => ['v' => 'Producto', 's' => ['bl' => 1]],
            '3' => ['v' => 'Ubicación', 's' => ['bl' => 1]],
            '4' => ['v' => 'Stock Sistema', 's' => ['bl' => 1]],
            '5' => ['v' => 'Cantidad Física', 's' => ['bl' => 1]],
            '6' => ['v' => 'Diferencia (Fórm.)', 's' => ['bl' => 1]],
            '7' => ['v' => 'Notas / Observaciones', 's' => ['bl' => 1]],
        ];
    }

    /**
     * @param  array<int|string, mixed>  $cellData
     * @return array<string, mixed>
     */
    private function construirEstructuraLibro(array $cellData, int $rowCount): array
    {
        return [
            'id' => 'workbook-inventario-'.time(),
            'sheetOrder' => ['sheet-1'],
            'sheets' => [
                'sheet-1' => [
                    'id' => 'sheet-1',
                    'name' => 'Físico vs Sistema',
                    'rowCount' => max(100, $rowCount + 20),
                    'columnCount' => 10,
                    'cellData' => $cellData,
                ],
            ],
        ];
    }
}
