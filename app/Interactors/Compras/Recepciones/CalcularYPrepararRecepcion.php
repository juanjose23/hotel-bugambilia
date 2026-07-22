<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Recepciones;

use App\Enums\Compras\EstadoRecepcion;
use App\Repository\Models\Compras\OrdenCompraItem;

final class CalcularYPrepararRecepcion
{
    public function __construct(
        private readonly GenerarCodigoRecepcion $generarCodigo,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function ejecutar(array $data): array
    {
        $data['codigo'] = $this->generarCodigo->ejecutar();

        $totalPendiente = 0.0;
        $totalRecibido = 0.0;
        $totalRechazado = 0.0;

        /** @var array<int, array<string, mixed>> $itemsData */
        $itemsData = (array) ($data['items'] ?? []);

        foreach ($itemsData as $i => $item) {
            $item = (array) $item;

            /** @var OrdenCompraItem $ordenItem */
            $ordenItem = OrdenCompraItem::withSum('recepcionItems', 'cantidad_recibida')
                ->findOrFail(is_numeric($item['orden_item_id'] ?? null) ? (int) $item['orden_item_id'] : 0);

            $alreadyReceived = is_numeric($ordenItem->recepcion_items_sum_cantidad_recibida ?? null)
                ? (float) $ordenItem->recepcion_items_sum_cantidad_recibida
                : 0.0;

            $ordered = (float) $ordenItem->cantidad;
            $nowReceiving = is_numeric($item['cantidad_recibida'] ?? null) ? (float) $item['cantidad_recibida'] : 0.0;
            $nowRejected = is_numeric($item['cantidad_rechazada'] ?? null) ? (float) $item['cantidad_rechazada'] : 0.0;
            $pending = $ordered - $alreadyReceived;

            if ($nowReceiving > $pending) {
                $productName = $ordenItem->producto !== null
                    ? $ordenItem->producto->nombre
                    : "Ítem #{$ordenItem->id}";

                throw new \InvalidArgumentException(
                    "{$productName}: solo quedan {$pending} de {$ordered} unidades pendientes por recibir "
                    ."(ya se recibieron {$alreadyReceived}). Está intentando recibir {$nowReceiving}."
                );
            }

            $totalPendiente += $pending;
            $totalRecibido += $nowReceiving;
            $totalRechazado += $nowRejected;

            $itemsData[$i]['producto_id'] = $itemsData[$i]['producto_id'] ?? $ordenItem->producto_id;
            $itemsData[$i]['producto_variante_id'] = $itemsData[$i]['producto_variante_id'] ?? $ordenItem->producto_variante_id;
            $itemsData[$i]['unidad_medida_id'] = $itemsData[$i]['unidad_medida_id'] ?? $ordenItem->unidad_medida_id;
        }

        $data['items'] = $itemsData;

        // Las nuevas recepciones siempre se crean en estado borrador (Pendiente)
        $data['estado'] = EstadoRecepcion::Pendiente;

        return $data;
    }
}
