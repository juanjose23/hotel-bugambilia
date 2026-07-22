<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;
use RuntimeException;

final readonly class ConciliadorInventarioFisico
{
    public function __construct(
        private LoteRepositorioInterface $loteRepositorio,
        private StockRepositorioInterface $stockRepositorio,
        private MovimientoStockRepositorioInterface $movimientoStockRepositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $datosHoja
     */
    public function conciliar(
        array $datosHoja,
        int $inventarioId,
        string $codigo,
        int $creadoPorId,
    ): int {
        if (! isset($datosHoja['sheets']) || ! is_array($datosHoja['sheets']) || $datosHoja['sheets'] === []) {
            throw new RuntimeException('La hoja de cálculo no contiene datos válidos.');
        }

        /** @var array<int|string, array<string, mixed>> $sheets */
        $sheets = $datosHoja['sheets'];

        $sheet = reset($sheets);

        if (! is_array($sheet)) {
            throw new RuntimeException('Formato de hoja inválido.');
        }

        $cellData = $sheet['cellData'] ?? [];

        if (! is_array($cellData)) {
            throw new RuntimeException('Formato de hoja inválido.');
        }

        /** @var array<int|string, mixed> $cellData */
        $lotesAjustados = 0;

        foreach ($cellData as $rowIndex => $row) {
            if ((int) $rowIndex === 0) {
                continue;
            }

            if (! is_array($row)) {
                continue;
            }

            /** @var array<int, array{v?: mixed}> $row */
            $loteIdVal = $this->obtenerValorCelda($row, 0);

            if (! is_numeric($loteIdVal)) {
                continue;
            }

            $lote = $this->loteRepositorio->buscarPorId((int) $loteIdVal);

            if ($lote === null) {
                continue;
            }

            $cantidadSistema = $this->obtenerNumero($row, 4);
            $cantidadFisica = $this->obtenerNumero($row, 5);
            $notas = $this->obtenerTexto($row);

            if (abs($cantidadFisica - $cantidadSistema) < 0.0001) {
                continue;
            }

            $discrepancia = $cantidadFisica - $cantidadSistema;

            $lote->cantidad_disponible = $cantidadFisica;

            if ($cantidadFisica <= 0.0) {
                $lote->estado = EstadoLote::Agotado;
            }

            $this->loteRepositorio->guardar($lote);

            $stock = $this->stockRepositorio->buscarPorLoteUbicacion(
                $lote->id,
                (int) $lote->ubicacion_id,
            );

            if ($stock !== null) {
                if ($cantidadFisica <= 0.0) {
                    $this->stockRepositorio->eliminar($stock);
                } else {
                    $stock->cantidad = $cantidadFisica;
                    $this->stockRepositorio->guardar($stock);
                }
            } elseif ($cantidadFisica > 0.0) {
                $this->stockRepositorio->crear([
                    'producto_id' => $lote->producto_id,
                    'producto_variante_id' => $lote->producto_variante_id,
                    'lote_id' => $lote->id,
                    'ubicacion_id' => $lote->ubicacion_id,
                    'cantidad' => $cantidadFisica,
                ]);
            }

            $cantidadMovimiento = abs($discrepancia);

            $costoUnitario = $lote->costo_unitario;

            $this->movimientoStockRepositorio->registrar([
                'tipo' => 'MOV_AJUSTE',
                'lote_id' => $lote->id,
                'producto_id' => $lote->producto_id,
                'cantidad' => $cantidadMovimiento,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario !== null
                    ? $costoUnitario * $cantidadMovimiento
                    : null,
                'ubicacion_origen_id' => $discrepancia < 0
                    ? $lote->ubicacion_id
                    : null,
                'ubicacion_destino_id' => $discrepancia > 0
                    ? $lote->ubicacion_id
                    : null,
                'documento_tipo' => 'inventario_fisico',
                'documento_id' => $inventarioId,
                'referencia' => "Ajuste Conciliación Física $codigo",
                'creado_por_id' => $creadoPorId,
                'notas' => trim(
                    "Ajuste por inventario físico. Faltante/Sobrante: $discrepancia. Notas: $notas"
                ),
            ]);

            $lotesAjustados++;
        }

        return $lotesAjustados;
    }

    /**
     * @param  array<int, array{v?: mixed}>  $row
     */
    private function obtenerValorCelda(array $row, int $indice): mixed
    {
        return $row[$indice]['v'] ?? null;
    }

    /**
     * @param  array<int, array{v?: mixed}>  $row
     */
    private function obtenerNumero(array $row, int $indice): float
    {
        $valor = $this->obtenerValorCelda($row, $indice);

        return is_numeric($valor)
            ? (float) $valor
            : 0.0;
    }

    /**
     * @param  array<int, array{v?: mixed}>  $row
     */
    private function obtenerTexto(array $row): string
    {
        $valor = $this->obtenerValorCelda($row, 7);

        return is_string($valor)
            ? $valor
            : '';
    }
}
