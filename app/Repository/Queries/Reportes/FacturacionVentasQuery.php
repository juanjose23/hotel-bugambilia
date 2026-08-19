<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Enums\Facturacion\EstadoFactura;
use App\Repository\Models\Facturacion\Factura;
use Illuminate\Database\Eloquent\Collection;

final class FacturacionVentasQuery
{
    /**
     * @return Collection<int, Factura>
     */
    public function porRango(string $fechaInicio, string $fechaFin): Collection
    {
        return Factura::with(['cliente.persona'])
            ->whereDate('fecha_emision', '>=', $fechaInicio)
            ->whereDate('fecha_emision', '<=', $fechaFin)
            ->where('estado', '!=', EstadoFactura::Anulada)
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }

    /**
     * @return array{
     *     totalSubtotal: float,
     *     totalImpuestos: float,
     *     totalGeneral: float,
     *     cantidadFacturas: int
     * }
     */
    public function resumenTotales(string $fechaInicio, string $fechaFin): array
    {
        $facturas = $this->porRango($fechaInicio, $fechaFin);

        return [
            'totalSubtotal' => (float) $facturas->sum(fn (Factura $f) => (float) $f->subtotal),
            'totalImpuestos' => (float) $facturas->sum(fn (Factura $f) => (float) $f->iva_total),
            'totalGeneral' => (float) $facturas->sum(fn (Factura $f) => (float) $f->total),
            'cantidadFacturas' => $facturas->count(),
        ];
    }
}
