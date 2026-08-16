<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion;

use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Persistencia\Facturacion\FacturaFolioPersistencia;
use DomainException;

final readonly class ReservarFolioFactura
{
    public function __construct(
        private FacturaFolioPersistencia $folioPersistencia,
    ) {}

    public function ejecutar(FacturaSerie $serie, FacturaAutorizacionDgi $autorizacion, ?int $usuarioId = null): FacturaFolio
    {
        $correlativo = (int) $serie->siguiente_numero;

        if ($correlativo < (int) $autorizacion->rango_desde || $correlativo > (int) $autorizacion->rango_hasta) {
            throw new DomainException("La serie {$serie->codigo} no tiene folios disponibles dentro del rango autorizado.");
        }

        $numero = $this->formatearNumero($serie, $correlativo);

        return $this->folioPersistencia->reservar($serie, $autorizacion, $correlativo, $numero, $usuarioId);
    }

    private function formatearNumero(FacturaSerie $serie, int $correlativo): string
    {
        return $serie->codigo.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);
    }
}
