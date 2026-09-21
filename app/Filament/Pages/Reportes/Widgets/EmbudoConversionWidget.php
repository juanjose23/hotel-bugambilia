<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Embudo de reservas: cuántas nacen, cuántas se confirman y cuántas llegan
 * a check-in, con el contrapeso de las cancelaciones.
 */
final class EmbudoConversionWidget extends StatsOverviewWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Embudo de reservas';

    protected ?string $description = 'El camino de cada reserva en el período: creada, confirmada, con check-in y cancelada.';

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, int|float> $embudo */
        $embudo = is_array($data['embudo'] ?? null) ? $data['embudo'] : [];

        $creadas = (int) ($embudo['reservas'] ?? 0);
        $confirmadas = (int) ($embudo['confirmadas'] ?? 0);
        $checkin = (int) ($embudo['checkin_periodo'] ?? 0);
        $canceladas = (int) ($embudo['canceladas'] ?? 0);
        $conversion = (float) ($embudo['conversion'] ?? 0.0);
        $cancelacion = (float) ($embudo['cancelacion'] ?? 0.0);

        return [
            Stat::make('Reservas creadas', $creadas)
                ->description('Solicitudes del período')
                ->descriptionIcon(Heroicon::DocumentPlus)
                ->color('primary'),

            Stat::make('Confirmadas', $confirmadas)
                ->description('Conversión de '.number_format($conversion, 1).'%')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make('Con check-in', $checkin)
                ->description('Huéspedes que ingresaron')
                ->descriptionIcon(Heroicon::UserPlus)
                ->color('info'),

            Stat::make('Canceladas', $canceladas)
                ->description('Tasa de '.number_format($cancelacion, 1).'%')
                ->descriptionIcon(Heroicon::XCircle)
                ->color('danger'),
        ];
    }

    /** @return array<string, mixed> */
    private function dashboard(): array
    {
        $rango = $this->rangoDashboard();

        return app(InteligenciaNegocioDashboardQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );
    }
}
