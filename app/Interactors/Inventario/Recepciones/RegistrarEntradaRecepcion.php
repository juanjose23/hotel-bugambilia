<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Recepciones;

use App\BusinessLogic\Inventario\Servicios\CreadorLoteRecepcion;
use App\BusinessLogic\Inventario\Servicios\IndividualizadorAutomaticoRecepcion;
use App\BusinessLogic\Inventario\Validacion\ReglasLotesRecepcion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Activos\RegistroIndividualizacion;
use App\Repository\Queries\Catalogos\BuscarProductoPorId;

class RegistrarEntradaRecepcion
{
    public function __construct(
        private readonly IndividualizadorAutomaticoRecepcion $individualizador,
        private readonly CreadorLoteRecepcion $creadorLote,
        private readonly ReglasLotesRecepcion $reglas,
        private readonly BuscarProductoPorId $buscarProductoPorId
    ) {}

    /**
     * @param  array<int, array{id: int, producto_id: int, cantidad_recibida: float, producto_variante_id?: int|null, fecha_vencimiento?: string|null, lote_proveedor?: string|null, ubicacion_id?: int|null, ubicacion_detalle_id?: int|null}>  $items
     * @param  array<int, array{disponible: float|int, cuarentena: float|int}>  $decisionesDiscrepancia
     */
    public function ejecutar(
        string $nuevoEstado,
        array $items,
        ?int $proveedorId = null,
        ?int $creadoPorId = null,
        array $decisionesDiscrepancia = []
    ): void {
        foreach ($items as $item) {
            $cantidad = (float) $item['cantidad_recibida'];
            if ($cantidad <= 0) {
                continue;
            }

            $productoId = (int) $item['producto_id'];
            $producto = $this->buscarProductoPorId->ejecutar($productoId);
            if ($producto && $producto->tipo === 3) {
                $registro = RegistroIndividualizacion::firstOrCreate(
                    ['recepcion_item_id' => (int) $item['id']],
                    [
                        'producto_id' => $productoId,
                        'producto_variante_id' => isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null,
                        'cantidad_total' => (int) $cantidad,
                        'cantidad_registrada' => 0,
                        'estado' => EstadoIndividualizacion::Pendiente,
                        'registrado_por_id' => $creadoPorId,
                    ]
                );

                if ($registro->wasRecentlyCreated || $registro->estado === EstadoIndividualizacion::Pendiente) {
                    $this->individualizador->execute($registro, (int) $cantidad, $creadoPorId);
                }

                continue;
            }

            $varianteId = isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null;
            $fechaVenc = ! empty($item['fecha_vencimiento']) ? new \DateTimeImmutable((string) $item['fecha_vencimiento']) : null;
            $fechaHoy = now()->toDateString();
            $codigoBase = ! empty($item['lote_proveedor']) ? $item['lote_proveedor'] : sprintf('LOTE-%d-%s', $productoId, now()->format('Ymd'));

            $distribucion = $this->reglas->determinarDistribucionLotes($nuevoEstado, $cantidad, $item, $decisionesDiscrepancia);

            /** @var list<array{codigo_sufijo: string, estado: EstadoLote, cantidad: float}> $distribucion */
            foreach ($distribucion as $dist) {
                $this->creadorLote->execute(
                    productoId: $productoId,
                    varianteId: $varianteId,
                    codigoLote: $codigoBase.$dist['codigo_sufijo'],
                    estado: $dist['estado'],
                    cantidad: $dist['cantidad'],
                    item: $item,
                    fechaVenc: $fechaVenc,
                    proveedorId: $proveedorId,
                    fechaHoy: $fechaHoy,
                    creadoPorId: $creadoPorId
                );
            }
        }
    }
}
