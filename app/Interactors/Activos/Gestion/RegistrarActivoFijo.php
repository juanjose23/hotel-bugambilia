<?php

// app/UseCases/Activos/Mutations/RegistrarActivoFijo.php

declare(strict_types=1);

namespace App\Interactors\Activos\Gestion;

use App\BusinessLogic\Activos\CreadorActivoConAsignacion;
use App\BusinessLogic\Activos\GeneradorCodigoInventario;
use App\BusinessLogic\Activos\ProcesadorIndividualizacionCompra;
use App\Repository\Models\Activos\Activo;
use App\Repository\Queries\Catalogos\BuscarProductoPorId;
use App\Repository\Queries\Compras\Recepciones\ObtenerRecepcionItemConCompra;
use Illuminate\Support\Facades\DB;

class RegistrarActivoFijo
{
    public function __construct(
        private readonly GeneradorCodigoInventario $generadorCodigo,
        private readonly ProcesadorIndividualizacionCompra $procesadorIndividualizacion,
        private readonly CreadorActivoConAsignacion $creadorActivo,
        private readonly BuscarProductoPorId $buscarProducto,
        private readonly ObtenerRecepcionItemConCompra $obtenerRecepcionItem,
    ) {}

    /**
     * Registra un activo fijo ya sea de forma manual o asociado a un ítem de recepción de compra.
     */
    public function execute(
        ?int $recepcionItemId,
        int $productoId,
        ?int $productoVarianteId,
        string $nombreDescriptivo,
        ?string $numeroSerie,
        ?float $costoAdquisicion,
        ?int $monedaId,
        ?int $proveedorId,
        string $fechaAdquisicion,
        int $userId,
        ?string $asignacionType = null,
        ?int $asignableId = null,
        ?string $asignacionMotivo = null
    ): Activo {
        return DB::transaction(function () use (
            $recepcionItemId,
            $productoId,
            $productoVarianteId,
            $nombreDescriptivo,
            $numeroSerie,
            $costoAdquisicion,
            $monedaId,
            $proveedorId,
            $fechaAdquisicion,
            $userId,
            $asignacionType,
            $asignableId,
            $asignacionMotivo
        ) {
            $producto = $this->buscarProducto->ejecutar($productoId);

            if (! $producto) {
                throw new \RuntimeException("No se encontró el producto con ID {$productoId}");
            }

            $codigoInventario = $this->generadorCodigo->generar($producto);

            // 2. Gestionar la asociación a la compra
            $registroId = null;
            if ($recepcionItemId) {
                $recepcionItem = $this->obtenerRecepcionItem->ejecutar($recepcionItemId);

                if (! $recepcionItem) {
                    throw new \RuntimeException("No se encontró el ítem de recepción con ID {$recepcionItemId}");
                }

                $recepcion = $recepcionItem->recepcion;
                $oc = $recepcion?->ordenCompra;

                // Si viene de compra y los datos financieros están vacíos, se extraen de la misma
                $costoAdquisicion ??= $recepcionItem->ordenItem?->precio_unitario ? (float) $recepcionItem->ordenItem->precio_unitario : null;
                $monedaId ??= $oc?->moneda_id;
                $proveedorId ??= $oc?->proveedor_id;
                $productoVarianteId ??= $recepcionItem->producto_variante_id;

                $registroId = $this->procesadorIndividualizacion->procesar(
                    recepcionItemId: $recepcionItemId,
                    productoId: $productoId,
                    productoVarianteId: $productoVarianteId,
                    cantidadRecibida: (float) $recepcionItem->cantidad_recibida,
                    userId: $userId,
                );
            }

            return $this->creadorActivo->execute(
                codigoInventario: $codigoInventario,
                registroId: $registroId,
                recepcionItemId: $recepcionItemId,
                productoId: $productoId,
                productoVarianteId: $productoVarianteId,
                nombreDescriptivo: $nombreDescriptivo ?: ($producto->nombre.' - '.$codigoInventario),
                numeroSerie: $numeroSerie,
                fechaAdquisicion: $fechaAdquisicion,
                costoAdquisicion: $costoAdquisicion,
                monedaId: $monedaId,
                proveedorId: $proveedorId,
                userId: $userId,
                asignacionType: $asignacionType,
                asignableId: $asignableId,
                asignacionMotivo: $asignacionMotivo,
            );
        });
    }
}
