<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\BusinessLogic\Compras\CalcularTotalesOrden;
use App\Events\Compras\SolicitudAprobada;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\CotizacionRepositorioInterface;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;
use Illuminate\Support\Collection;

final readonly class GenerarOrdenesDesdeComparativa
{
    public function __construct(
        private GenerarCodigoOrdenCompra $generarCodigo,
        private CalcularTotalesOrden $calcularTotales,
        private OrdenCompraRepositorioInterface $ordenCompraRepositorio,
        private CotizacionRepositorioInterface $cotizacionRepositorio,
        private SolicitudRepositorioInterface $solicitudRepositorio,
    ) {}

    public function ejecutar(int $solicitudId): int
    {
        $solicitud = $this->solicitudRepositorio->buscarPorIdConItems($solicitudId);
        if (! $solicitud) {
            return 0;
        }

        $cotizacionesConGanadores = $this->cotizacionRepositorio->obtenerGanadorasPorSolicitud($solicitudId);

        if ($cotizacionesConGanadores->isEmpty()) {
            return 0;
        }

        $ordenesCreadas = 0;

        foreach ($cotizacionesConGanadores as $cot) {
            /** @var Collection<int, CotizacionItem> $itemsElegidos */
            $itemsElegidos = $cot->items;

            if ($itemsElegidos->isEmpty()) {
                continue;
            }

            if ($this->ordenCompraRepositorio->existeOrdenParaCotizacion($solicitudId, $cot->id)) {
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
