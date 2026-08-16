<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion;

use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\EstadoFolioFactura;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Persistencia\Facturacion\FacturaFolioPersistencia;
use App\Repository\Persistencia\Facturacion\FacturaPersistencia;
use App\Repository\Queries\Facturacion\FacturaQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class AnularFacturaFiscal
{
    public function __construct(
        private FacturaQuery $facturaQuery,
        private FacturaPersistencia $facturaPersistencia,
        private FacturaFolioPersistencia $facturaFolioPersistencia,
    ) {}

    public function ejecutar(Factura $factura, string $motivo, ?int $usuarioId = null): Factura
    {
        if (trim($motivo) === '') {
            throw new DomainException('Debe indicar el motivo de anulacion de la factura.');
        }

        return DB::transaction(function () use ($factura, $motivo, $usuarioId): Factura {
            $factura = $this->facturaQuery->porIdConLock($factura->id, ['folios']);

            if ($factura->estado === EstadoFactura::Anulada) {
                return $factura;
            }

            if ($factura->estado !== EstadoFactura::Emitida) {
                throw new DomainException("Solo se pueden anular facturas emitidas. Estado actual: {$factura->estado->getLabel()}.");
            }

            $factura = $this->facturaPersistencia->anular($factura, [
                'estado' => EstadoFactura::Anulada,
                'motivo_anulacion' => $motivo,
                'anulada_at' => now(),
                'anulada_por' => $usuarioId,
            ]);

            $this->facturaFolioPersistencia->actualizarPorFactura($factura, [
                'estado' => EstadoFolioFactura::Anulado,
                'anulado_at' => now(),
                'motivo' => $motivo,
            ]);

            return $factura->load('folios');
        });
    }
}
