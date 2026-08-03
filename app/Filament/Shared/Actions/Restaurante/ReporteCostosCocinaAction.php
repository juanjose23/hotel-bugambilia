<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Interactors\Restaurante\Reportes\GenerarReporteCostosCocina;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;

final class ReporteCostosCocinaAction
{
    public static function make(): Action
    {
        return Action::make('reporteCostosCocina')
            ->label('Reporte de Costos')
            ->icon('heroicon-o-document-chart-bar')
            ->color('info')
            ->modalHeading('Reporte Ejecutivo de Costos de Cocina')
            ->modalDescription('Genere un resumen consolidado de producción y costos por plato.')
            ->modalWidth('md')
            ->schema([
                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->default(now()->startOfMonth()),

                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->default(now()->endOfMonth()),
            ])
            ->action(function (array $data): void {
                $fechaInicio = is_string($data['fecha_inicio'] ?? null) ? $data['fecha_inicio'] : null;
                $fechaFin = is_string($data['fecha_fin'] ?? null) ? $data['fecha_fin'] : null;

                $res = app(GenerarReporteCostosCocina::class)->ejecutar($fechaInicio, $fechaFin);

                $fmtTot = number_format($res['costo_total_acumulado'], 2);
                $fmtProm = number_format($res['costo_promedio_por_plato'], 2);

                Notification::make()
                    ->title('Reporte de Costos Generado')
                    ->body("Procesos: {$res['total_procesos']} | Platos: {$res['total_platos']} | Costo Total: C$ {$fmtTot} | Costo Prom/Plato: C$ {$fmtProm}")
                    ->info()
                    ->send();
            });
    }
}
