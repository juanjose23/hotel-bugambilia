<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class KpisInteligenciaNegocioWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Resumen ejecutivo';

    protected ?string $description = 'Indicadores principales para leer ocupación, ingresos y ventas del período seleccionado.';

    public ?string $fechaInicio = null;

    public ?string $fechaFin = null;

    public static function canView(): bool
    {
        return auth()->user()?->is_admin === true
            || auth()->user()?->can('Reportes:InteligenciaNegocio') === true
            || auth()->user()?->can('page_TableroInteligenciaNegocio') === true;
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, int|float> $kpis */
        $kpis = is_array($data['kpis'] ?? null) ? $data['kpis'] : [];

        $ingresosReservas = (float) ($kpis['ingresos_reservas'] ?? 0.0);
        $reservas = (int) ($kpis['reservas'] ?? 0);
        $ocupacion = (float) ($kpis['ocupacion'] ?? 0.0);
        $adr = (float) ($kpis['adr'] ?? 0.0);
        $revpar = (float) ($kpis['revpar'] ?? 0.0);
        $cobrado = (float) ($kpis['cobrado'] ?? 0.0);
        $facturado = (float) ($kpis['facturado'] ?? 0.0);
        $restaurante = (float) ($kpis['restaurante'] ?? 0.0);

        return [
            Stat::make('Ingresos por reservas', 'C$ '.number_format($ingresosReservas, 2))
                ->description($reservas.' reservas en el período')
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color('success'),

            Stat::make('Ocupación estimada', number_format($ocupacion, 1).'%')
                ->description('ADR C$ '.number_format($adr, 2).' · RevPAR C$ '.number_format($revpar, 2))
                ->descriptionIcon(Heroicon::HomeModern)
                ->color('info'),

            Stat::make('Cobrado', 'C$ '.number_format($cobrado, 2))
                ->description('Fiscal emitido C$ '.number_format($facturado, 2))
                ->descriptionIcon(Heroicon::DocumentCheck)
                ->color('primary'),

            Stat::make('Restaurante', 'C$ '.number_format($restaurante, 2))
                ->description('Ventas operativas registradas')
                ->descriptionIcon(Heroicon::ShoppingBag)
                ->color('warning'),
        ];
    }

    /** @return array<string, mixed> */
    private function dashboard(): array
    {
        return app(InteligenciaNegocioDashboardQuery::class)->paraRango(
            $this->fechaInicio ?? now()->startOfMonth()->format('Y-m-d'),
            $this->fechaFin ?? now()->format('Y-m-d'),
        );
    }
}
