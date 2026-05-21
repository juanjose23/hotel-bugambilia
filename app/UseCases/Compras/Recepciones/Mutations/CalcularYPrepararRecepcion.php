<?php

declare(strict_types=1);

namespace App\UseCases\Compras\Recepciones\Mutations;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompraItem;

class CalcularYPrepararRecepcion
{
    /**
     * Calcula y prepara los datos de la recepción antes de su creación.
     *
     * @param  array<string, mixed>  $data  Datos crudos del formulario de recepción.
     * @return array<string, mixed> Datos mutados y listos para ser guardados en la base de datos.
     *
     * @throws \InvalidArgumentException Si alguna cantidad recibida supera la cantidad pendiente.
     */
    public function execute(array $data): array
    {
        $data['codigo'] = app(GenerarCodigoRecepcion::class)->execute();

        $totalPendiente = 0.0;
        $totalRecibido = 0.0;
        $totalRechazado = 0.0;

        foreach ($data['items'] ?? [] as $i => $item) {
            /** @var OrdenCompraItem $ordenItem */
            $ordenItem = OrdenCompraItem::withSum('recepcionItems', 'cantidad_recibida')
                ->findOrFail($item['orden_item_id']);

            $alreadyReceived = (float) ($ordenItem->recepcion_items_sum_cantidad_recibida ?? 0);
            $ordered = (float) $ordenItem->cantidad;
            $nowReceiving = (float) ($item['cantidad_recibida'] ?? 0);
            $nowRejected = (float) ($item['cantidad_rechazada'] ?? 0);
            $pending = $ordered - $alreadyReceived;

            if ($nowReceiving > $pending) {
                $productName = $ordenItem->producto->nombre ?? "Ítem #{$ordenItem->id}";

                throw new \InvalidArgumentException(
                    "{$productName}: solo quedan {$pending} de {$ordered} unidades pendientes por recibir "
                    ."(ya se recibieron {$alreadyReceived}). Está intentando recibir {$nowReceiving}."
                );
            }

            $totalPendiente += $pending;
            $totalRecibido += $nowReceiving;
            $totalRechazado += $nowRejected;

            $data['items'][$i]['producto_id'] ??= $ordenItem->producto_id;
            $data['items'][$i]['producto_variante_id'] ??= $ordenItem->producto_variante_id;
            $data['items'][$i]['unidad_medida_id'] ??= $ordenItem->unidad_medida_id;
        }

        // Las nuevas recepciones siempre se crean en estado borrador (Pendiente)
        $data['estado'] = EstadoRecepcion::Pendiente;

        return $data;
    }
}
