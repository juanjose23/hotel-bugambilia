<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\BusinessLogic\Reportes\ClasificarAnomaliasOperacion;
use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\ObtenerMetricasOperacionQuery;
use Filament\Widgets\Widget;

/**
 * Tabla de anomalías operativas del período con severidad
 * calculada por BusinessLogic con umbrales configurables.
 */
final class AnomaliasOperacionWidget extends Widget
{
    use UsaRangoFechasDashboard;

    protected string $view = 'filament.pages.reportes.widgets.anomalias-operacion';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $rango = $this->rangoDashboard();
        $data = app(ObtenerMetricasOperacionQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );

        $kpis = $data['kpis'];
        $detalle = $data['detalle'];

        $anomalias = app(ClasificarAnomaliasOperacion::class)->clasificar($kpis);

        return [
            'anomalias' => $anomalias,
            'detalle' => $detalle,
            'tienePendientes' => (int) ($kpis['limpiezas_pendientes'] ?? 0) > 0,
            'bloqueadas' => $detalle['bloqueadas'],
            'mantenimientosVencidos' => $detalle['mantenimientos_vencidos'],
            'mantenimientosProximos' => $detalle['mantenimientos_proximos'],
        ];
    }
}
