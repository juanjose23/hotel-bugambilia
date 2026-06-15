<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Support\HotelInfo;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;

trait GeneratesReportPdf
{
    protected function loadLogoBase64(): string
    {
        return HotelInfo::getLogoBase64();
    }

    protected function registrarAuditoria(string $codigo, array $datos = []): void
    {
        $auditoria = new RegistrarAuditoriaReporteUseCase;
        $auditoria->execute($codigo, $datos);
    }
}
