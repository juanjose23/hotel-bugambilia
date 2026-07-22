<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\BusinessLogic\Compras\CalcularTotalesOrden;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Events\Compras\SolicitudAprobada;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use Illuminate\Support\Collection;

final class GenerarOrdenesDesdeComparativa
{
    public function __construct(
        private readonly GenerarCodigoOrdenCompra $generarCodigo,
        private readonly CalcularTotalesOrden $calcularTotales,
        private readonly OrdenCompraRepositorioInterface $ordenCompraRepositorio,
    ) {}

    public function ejecutar(int $solicitudId): int
    {
        $solicitud = Solicitud::with('items')->findOrFail($solicitudId);

        $cotizacionesConGanadores = Cotizacion::where('solicitud_id', $solicitudId)
            ->with(['items' => fn ($q) => $q->where('es_elegido', true)])
            ->whereHas('items', fn ($q) => $q->where('es_elegido', true))
            ->get();

        if ($cotizacionesConGanadores->isEmpty()) {
            return 0;
        }

        $ordenesCreadas = 0;

        foreach ($cotizacionesConGanadores as $cot) {
            $itemsElegidos = $cot->items;

            if ($itemsElegidos->isEmpty()) {
                continue;
            }

            $yaExiste = OrdenCompra::where('solicitud_id', $solicitudId)
                ->where('cotizacion_id', $cot->id)
                ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
                ->exists();

            if ($yaExiste) {
                continue;
            }

            $this->crearOrden($cot, $itemsElegidos, $solicitud);

            $ordenesCreadas++;
        }

        if ($ordenesCreadas > 0) {
            SolicitudAprobada::dispatch($solicitud);
        }

        return $ordenesCreadas;
    }

    /**
     * @param  Collection<int, CotizacionItem>  $itemsElegidos
     */
    private function crearOrden(Cotizacion $cot, Collection $itemsElegidos, Solicitud $solicitud): OrdenCompra
    {
        $codigo = $this->generarCodigo->ejecutar();
        /** @var Collection<int, mixed> $itemsElegidos */
        $totales = $this->calcularTotales->calcular($itemsElegidos);

        /** @var Collection<int, CotizacionItem> $itemsElegidos */
        return $this->ordenCompraRepositorio->crearConItems(
            $cot,
            $itemsElegidos,
            $codigo,
            $totales,
            "Generada desde Cotización #{$cot->id} - Comparativa de Precios"
        );
    }
}
