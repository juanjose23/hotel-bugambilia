<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Support\HotelInfo;

final class ObtenerDatosBaseReporte
{
    public function __construct(
        private readonly RegistrarAuditoriaReporte $auditoria
    ) {}

    /** @param  array<string, mixed>  $filtros
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

        $this->auditoria->ejecutar($codigoReporte, $params);

        return array_merge(HotelInfo::getBaseData(), [
            'record' => $record,
        ]);
    }
}
