<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\Compras\EstadoSolicitud;
use App\Support\ReporteConfig;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

final class ReporteFiltros
{
    /**
     * @return array<int, Component>
     */
    public static function getFormSchema(string $modulo = 'compras'): array
    {
        return [
            Select::make('reporte')
                ->label('Reporte Analítico')
                ->options(ReporteConfig::getSelectOptions($modulo))
                ->required()
                ->live()
                ->native(false)
                ->searchable()
                ->placeholder('Selecciona un reporte de la lista...'),

            TextEntry::make('reporte_descripcion')
                ->hiddenLabel()
                ->state(fn (Get $get) => ReporteConfig::getDescripcion($modulo, is_string($get('reporte')) ? $get('reporte') : '') ?? 'Seleccione un reporte de la lista para ver su descripción...')
                ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

            DatePicker::make('fecha_inicio')
                ->label('Fecha Inicio')
                ->default(now()->startOfMonth()->format('Y-m-d'))
                ->required()
                ->native(false),

            DatePicker::make('fecha_fin')
                ->label('Fecha Fin')
                ->default(now()->format('Y-m-d'))
                ->required()
                ->native(false),

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
        ];
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
