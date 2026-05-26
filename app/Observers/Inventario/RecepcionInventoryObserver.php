<?php

declare(strict_types=1);

namespace App\Observers\Inventario;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\RecepcionCompra;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use Psr\Log\LoggerInterface;

class RecepcionInventoryObserver
{
    public function __construct(
        private readonly RegistrarEntradaRecepcion $useCase,
        private readonly LoggerInterface $logger,
    ) {}

    public function created(RecepcionCompra $recepcion): void
    {
        $this->procesarEntrada($recepcion);
    }

    public function updated(RecepcionCompra $recepcion): void
    {
        if (! $recepcion->wasChanged('estado')) {
            return;
        }

        $this->procesarEntrada($recepcion);
    }

    private function procesarEntrada(RecepcionCompra $recepcion): void
    {
        $estadosEntrada = [
            EstadoRecepcion::Completa,
            EstadoRecepcion::Parcial,
            EstadoRecepcion::ConDiscrepancia,
            EstadoRecepcion::EnCuarentena,
        ];

        if (! in_array($recepcion->estado, $estadosEntrada, true)) {
            return;
        }

        try {
            $items = $recepcion->items()->get()->map(fn ($i) => [
                'id' => $i->id,
                'producto_id' => $i->producto_id,
                'producto_variante_id' => $i->producto_variante_id,
                'cantidad_recibida' => (float) $i->cantidad_recibida,
                'cantidad_rechazada' => (float) $i->cantidad_rechazada,
                'lote_proveedor' => $i->lote_proveedor,
                'fecha_vencimiento' => $i->fecha_vencimiento?->format('Y-m-d'),
                'ubicacion_id' => $i->ubicacion_id ?? $recepcion->ubicacion_id,
            ])->all();

            $this->useCase->execute(
                nuevoEstado: $recepcion->estado->name,
                items: $items,
                proveedorId: $recepcion->ordenCompra?->proveedor_id,
                creadoPorId: $recepcion->recibido_por_id,
            );
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Inventario: no se pudo registrar entrada para recepción {id}: {error}',
                ['id' => $recepcion->id, 'error' => $e->getMessage()],
            );
        }
    }
}
