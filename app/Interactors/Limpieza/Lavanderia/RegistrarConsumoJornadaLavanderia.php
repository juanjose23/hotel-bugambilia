<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Actions\Limpieza\Lavanderia\FinalizarProcesosLavanderia;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class RegistrarConsumoJornadaLavanderia
{
    public function __construct(
        private FinalizarProcesosLavanderia $finalizarProcesos,
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
    ) {}

    /**
     * @param  int|array<int>  $ubicacionLavanderiaId
     * @param  list<array{stock_id: int, cantidad: float, notas?: string|null}>  $insumos
     * @param  list<array{stock_id: int, cantidad: float, notas?: string|null}>  $mermas
     * @return array{total_insumos: int, total_cantidad: float, total_mermas: int}
     */
    public function execute(
        int|array $ubicacionLavanderiaId,
        string $fechaJornada,
        string|int $turno,
        array $insumos,
        ?string $operadorNombre = null,
        ?float $kilosLavados = null,
        ?int $cargasLavadas = null,
        ?int $creadoPorId = null,
        ?string $observacionesGenerales = null,
        array $mermas = [],
    ): array {
        return $this->ejecutar(
            $ubicacionLavanderiaId,
            $fechaJornada,
            $turno,
            $insumos,
            $operadorNombre,
            $kilosLavados,
            $cargasLavadas,
            $creadoPorId,
            $observacionesGenerales,
            $mermas,
        );
    }

    /**
     * @param  int|array<int>  $ubicacionLavanderiaId
     * @param  list<array{stock_id: int, cantidad: float, notas?: string|null}>  $insumos
     * @param  list<array{stock_id: int, cantidad: float, notas?: string|null}>  $mermas
     * @return array{total_insumos: int, total_cantidad: float, total_mermas: int}
     */
    public function ejecutar(
        int|array $ubicacionLavanderiaId,
        string $fechaJornada,
        string|int $turno,
        array $insumos,
        ?string $operadorNombre = null,
        ?float $kilosLavados = null,
        ?int $cargasLavadas = null,
        ?int $creadoPorId = null,
        ?string $observacionesGenerales = null,
        array $mermas = [],
    ): array {
        $insumosValidos = array_filter($insumos, fn (array $item): bool => $item['stock_id'] > 0 && $item['cantidad'] > 0.0);

        if (empty($insumosValidos)) {
            throw new InvalidArgumentException('Debe seleccionar al menos un insumo con cantidad mayor a cero.');
        }

        $mermasValidas = array_filter($mermas, fn (array $item): bool => $item['stock_id'] > 0 && $item['cantidad'] > 0.0);

        return DB::transaction(function () use (
            $ubicacionLavanderiaId,
            $fechaJornada,
            $turno,
            $insumosValidos,
            $operadorNombre,
            $kilosLavados,
            $cargasLavadas,
            $creadoPorId,
            $observacionesGenerales,
            $mermasValidas
        ): array {
            $totalInsumos = 0;
            $totalCantidad = 0.0;
            $totalMermas = 0;

            $turnoLabel = 'Turno';
            if (is_numeric($turno) && (int) $turno > 0) {
                $nombreTurno = $this->lavanderiaRepositorio->buscarNombreTurno((int) $turno);
                $turnoLabel = $nombreTurno !== null ? $nombreTurno : "Turno #{$turno}";
            } else {
                $turnoLabel = ucfirst((string) $turno);
            }

            $referenciaBase = "Consumo Jornada {$turnoLabel} - {$fechaJornada}";

            if ($operadorNombre !== null && trim($operadorNombre) !== '') {
                $referenciaBase .= " (Operador: {$operadorNombre})";
            }

            if ($kilosLavados !== null && $kilosLavados > 0.0) {
                $referenciaBase .= " - {$kilosLavados} kg";
            } elseif ($cargasLavadas !== null && $cargasLavadas > 0) {
                $referenciaBase .= " - {$cargasLavadas} cargas";
            }

            // 1. Procesar Insumos Químicos
            foreach ($insumosValidos as $item) {
                $stockId = (int) $item['stock_id'];
                $cantidad = (float) $item['cantidad'];
                $notasItem = isset($item['notas']) && trim((string) $item['notas']) !== ''
                    ? trim((string) $item['notas'])
                    : $observacionesGenerales;

                $this->lavanderiaRepositorio->descontarStockJornada(
                    stockId: $stockId,
                    ubicacionLavanderiaId: $ubicacionLavanderiaId,
                    cantidad: $cantidad,
                    referencia: $referenciaBase,
                    creadoPorId: $creadoPorId,
                    notas: $notasItem,
                    tipoMovimiento: 'CONSUMO_LAVANDERIA',
                    documentoTipo: 'jornada_lavanderia',
                );

                $totalInsumos++;
                $totalCantidad += $cantidad;
            }

            // 2. Procesar Mermas / Bajas del Turno (si hubo)
            foreach ($mermasValidas as $mermaItem) {
                $mermaStockId = (int) $mermaItem['stock_id'];
                $mermaCantidad = (float) $mermaItem['cantidad'];
                $motivoMerma = isset($mermaItem['notas']) && trim((string) $mermaItem['notas']) !== ''
                    ? trim((string) $mermaItem['notas'])
                    : 'Baja/Merma reportada en cierre de turno';

                $stockMerma = $this->lavanderiaRepositorio->descontarStockJornada(
                    stockId: $mermaStockId,
                    ubicacionLavanderiaId: $ubicacionLavanderiaId,
                    cantidad: $mermaCantidad,
                    referencia: "Merma en {$referenciaBase}",
                    creadoPorId: $creadoPorId,
                    notas: $motivoMerma,
                    tipoMovimiento: 'CONSUMO_LAVANDERIA',
                    documentoTipo: 'merma_jornada_lavanderia',
                );

                $this->finalizarProcesos->execute(
                    productoId: (int) $stockMerma->producto_id,
                    productoVarianteId: $stockMerma->producto_variante_id !== null ? (int) $stockMerma->producto_variante_id : null,
                    loteId: $stockMerma->lote_id !== null ? (int) $stockMerma->lote_id : null,
                    cantidad: $mermaCantidad,
                );

                $totalMermas += (int) $mermaCantidad;
            }

            return [
                'total_insumos' => $totalInsumos,
                'total_cantidad' => $totalCantidad,
                'total_mermas' => $totalMermas,
            ];
        });
    }
}
