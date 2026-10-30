<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\Compras\EstadoSolicitud;
use App\Support\Barcode\Support\ReporteConfig;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

final class ReporteFiltros
{
    /**
     * @return array<int, Component>
     */
    public static function getFormSchema(string $modulo = 'compras'): array
    {
        return ReporteFormSchema::construir(
            modulo: $modulo,
            camposEspecificos: [
                Select::make('estado')
                    ->label('Estado (opcional)')
                    ->options(EstadoSolicitud::class)
                    ->placeholder('Todos los estados')
                    ->native(false)
                    ->visible(fn (Get $get) => $get('reporte') === 'solicitudes_estado'),

                Select::make('meses')
                    ->label('Últimos N meses')
                    ->options([3 => '3 meses', 6 => '6 meses', 12 => '12 meses'])
                    ->default(6)
                    ->native(false)
                    ->visible(fn (Get $get) => $get('reporte') === 'analisis_precio'),
            ],
            labelReporte: 'Reporte Analítico',
        );
    }

    /** @param array<string, mixed> $data */
    public static function getUrlReporte(array $data, string $modulo = 'compras'): string
    {
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';
        $rawFechaInicio = $data['fecha_inicio'] ?? null;
        $rawFechaFin = $data['fecha_fin'] ?? null;
        $params = [
            'fecha_inicio' => is_string($rawFechaInicio) ? $rawFechaInicio : '',
            'fecha_fin' => is_string($rawFechaFin) ? $rawFechaFin : '',
            'pageSize' => is_string($data['pageSize'] ?? null) ? $data['pageSize'] : 'letter',
            'orientation' => is_string($data['orientation'] ?? null) ? $data['orientation'] : 'portrait',
        ];

        if ($reporte === 'solicitudes_estado') {
            $params['estado'] = $data['estado'] ?? null;
        }

        if ($reporte === 'analisis_precio') {
            $params['meses'] = $data['meses'] ?? 6;
        }

        try {
            return ReporteConfig::getUrl($modulo, $reporte, $params);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Reporte desconocido: $reporte", 0, $e);
        }
    }
}
