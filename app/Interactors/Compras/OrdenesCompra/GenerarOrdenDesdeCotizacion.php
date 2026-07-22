<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\BusinessLogic\Compras\CalcularTotalesOrden;
use App\Enums\Compras\EstadoSolicitud;
use App\Events\Compras\OrdenCreada;
use App\Events\Compras\SolicitudAprobada;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use Illuminate\Support\Collection;

final class GenerarOrdenDesdeCotizacion
{
    public function __construct(
        private readonly GenerarCodigoOrdenCompra $generarCodigo,
        private readonly CalcularTotalesOrden $calcularTotales,
        private readonly OrdenCompraRepositorioInterface $ordenCompraRepositorio,
    ) {}

    public function ejecutar(int $cotizacionId): OrdenCompra
    {
        $cotizacion = Cotizacion::with([
            'items',
            'proveedor.persona.personaJuridica',
            'solicitud.items',
        ])->findOrFail($cotizacionId);

        $itemsElegidos = $cotizacion->items->where('es_elegido', true);

        if ($itemsElegidos->isEmpty() && $cotizacion->es_elegida) {
            $itemsElegidos = $cotizacion->items;
        }

        if ($itemsElegidos->isEmpty()) {
            throw new \DomainException('Debe seleccionar al menos un ítem para generar la orden.');
        }

        $codigo = $this->generarCodigo->ejecutar();
        /** @var Collection<int, mixed> $itemsElegidos */
        $totales = $this->calcularTotales->calcular($itemsElegidos);

        $proveedorNombre = $cotizacion->proveedor?->persona?->personaJuridica->razon_social
            ?? $cotizacion->proveedor?->persona->primer_nombre
            ?? 'Proveedor #'.$cotizacion->proveedor_id;

        /** @var Collection<int, CotizacionItem> $itemsElegidos */
        $orden = $this->ordenCompraRepositorio->crearConItems(
            $cotizacion,
            $itemsElegidos,
            $codigo,
            $totales,
            "Generada desde Cotización #{$cotizacion->id} de {$proveedorNombre}"
        );

        $cotizacion->solicitud?->update(['estado' => EstadoSolicitud::Aprobada]);

        OrdenCreada::dispatch($orden);

        if ($orden->solicitud) {
            SolicitudAprobada::dispatch($orden->solicitud);
        }

        return $orden;
    }
}
