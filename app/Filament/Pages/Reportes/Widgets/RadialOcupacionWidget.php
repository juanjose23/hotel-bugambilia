<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Widgets\Widget;

/**
 * Pieza central del tablero: anillo visual de ocupación + tarjeta de
 * ingresos del período + resumen operativo del día.
 */
final class RadialOcupacionWidget extends Widget
{
    use UsaRangoFechasDashboard;

    protected string $view = 'filament.pages.reportes.widgets.radial-ocupacion';

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
        $data = app(InteligenciaNegocioDashboardQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );

        /** @var array<string, int|float> $kpis */
        $kpis = is_array($data['kpis'] ?? null) ? $data['kpis'] : [];
        /** @var array<string, int|float> $anterior */
        $anterior = is_array($data['anterior'] ?? null) ? $data['anterior'] : [];
        /** @var array<string, int> $operacion */
        $operacion = is_array($data['operacion'] ?? null) ? $data['operacion'] : [];

        $ocupacion = (float) ($kpis['ocupacion'] ?? 0.0);
        $anteriorOcupacion = is_numeric($anterior['ocupacion'] ?? null) ? (float) $anterior['ocupacion'] : 0.0;

        return [
            'ocupacion' => $ocupacion,
            'deltaOcupacionPuntos' => round($ocupacion - $anteriorOcupacion, 1),
            'ingresos' => (float) ($kpis['ingresos_reservas'] ?? 0.0),
            'adr' => (float) ($kpis['adr'] ?? 0.0),
            'revpar' => (float) ($kpis['revpar'] ?? 0.0),
            'reservas' => (int) ($kpis['reservas'] ?? 0),
            'habitacionesTotales' => (int) ($operacion['habitaciones_totales'] ?? 0),
            'habitacionesOcupadasHoy' => (int) ($operacion['habitaciones_ocupadas_hoy'] ?? 0),
            'checkInHoy' => (int) ($operacion['check_in_hoy'] ?? 0),
            'checkOutHoy' => (int) ($operacion['check_out_hoy'] ?? 0),
            'limpiezasPendientes' => (int) ($operacion['limpiezas_pendientes'] ?? 0),
            'limpiezasProgramadas' => (int) ($operacion['limpiezas_programadas'] ?? 0),
        ];
    }
}
