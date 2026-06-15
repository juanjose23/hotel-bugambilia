<?php

declare(strict_types=1);

namespace App\UseCases\Reportes\Queries;

use App\Support\HotelInfo;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;

class ObtenerDatosBaseReporteUseCase
{
    public function __construct(
        private readonly RegistrarAuditoriaReporteUseCase $auditoriaUseCase
    ) {}

    /**
     * Retorna los datos base para cualquier reporte del sistema y registra la auditoría.
     *
     * @param  mixed  $record  Registro principal del reporte (opcional)
     * @param  array<string, mixed>  $filtros  Filtros opcionales pasados al reporte
     * @return array<string, mixed>
     */
    public function execute(string $codigoReporte, mixed $record = null, array $filtros = []): array
    {
        $params = $filtros;
        if ($record !== null && is_object($record)) {
            $params['id'] = $record->id ?? null;
            $params['codigo'] = $record->codigo ?? null;
            $params['referencia_id'] = $record->id ?? null;
        }

        $this->auditoriaUseCase->execute($codigoReporte, $params);

        return array_merge(HotelInfo::getBaseData(), [
            'record' => $record,
        ]);
    }
}
