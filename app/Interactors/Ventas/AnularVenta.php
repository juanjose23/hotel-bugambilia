<?php

declare(strict_types=1);

namespace App\Interactors\Ventas;

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Interactors\Facturacion\AnularFacturaFiscal;
use App\Repository\Models\Cuentas\Venta;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class AnularVenta
{
    public function __construct(
        private AnularFacturaFiscal $anularFacturaFiscal,
    ) {}

    public function ejecutar(Venta $venta, string $motivo, ?int $usuarioId = null): Venta
    {
        if ($venta->estado === EstadoVenta::Anulada) {
            throw new DomainException("La venta {$venta->numero_venta} ya está anulada.");
        }

        return DB::transaction(function () use ($venta, $motivo, $usuarioId): Venta {
            // Anular facturas fiscales asociadas que estén emitidas
            foreach ($venta->facturas as $factura) {
                if ($factura->estado === EstadoFactura::Emitida) {
                    $this->anularFacturaFiscal->ejecutar($factura, "Anulación por cancelación de venta: {$motivo}", $usuarioId);
                }
            }

            $datosFiscales = is_array($venta->datos_fiscales) ? $venta->datos_fiscales : [];
            $datosFiscales['motivo_anulacion'] = $motivo;
            $datosFiscales['anulada_en'] = now()->toIso8601String();

            $venta->update([
                'estado' => EstadoVenta::Anulada,
                'anulada_por' => $usuarioId,
                'anulada_en' => now(),
                'datos_fiscales' => $datosFiscales,
            ]);

            return $venta->refresh();
        });
    }
}
