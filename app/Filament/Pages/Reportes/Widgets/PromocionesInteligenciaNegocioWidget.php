<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use App\Support\MonedaHelper;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class PromocionesInteligenciaNegocioWidget extends StatsOverviewWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Promociones';

    protected ?string $description = 'Lectura rápida del uso, alcance e impacto económico de las promociones.';

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, mixed> $promociones */
        $promociones = is_array($data['promociones'] ?? null) ? $data['promociones'] : [];

        $topList = is_array($promociones['top'] ?? null) ? $promociones['top'] : [];
        /** @var array{nombre: string, reservas: int}|null $top */
        $top = isset($topList[0]) && is_array($topList[0]) ? $topList[0] : null;

        $topDescription = $top !== null
            ? "{$top['nombre']} · {$top['reservas']} reservas"
            : 'Sin reservas con promoción';

        $activas = is_numeric($promociones['activas'] ?? null) ? (int) $promociones['activas'] : 0;
        $reservasConPromocion = is_numeric($promociones['reservas_con_promocion'] ?? null) ? (int) $promociones['reservas_con_promocion'] : 0;
        $usosBeneficios = is_numeric($promociones['usos_beneficios'] ?? null) ? (int) $promociones['usos_beneficios'] : 0;
        $descuentoTotal = is_numeric($promociones['descuento_total'] ?? null) ? (float) $promociones['descuento_total'] : 0.0;

        return [
            Stat::make('Promociones activas', $activas)
                ->description('Vigentes para venta hoy')
                ->descriptionIcon(Heroicon::Gift)
                ->color('primary'),

            Stat::make('Reservas con promoción', $reservasConPromocion)
                ->description($topDescription)
                ->descriptionIcon(Heroicon::Trophy)
                ->color('success'),

            Stat::make('Beneficios usados', $usosBeneficios)
                ->description('Usos registrados en el período')
                ->descriptionIcon(Heroicon::Sparkles)
                ->color('info'),

            Stat::make('Descuento otorgado', MonedaHelper::formatear($descuentoTotal))
                ->description('Reservas y beneficios de cliente')
                ->descriptionIcon(Heroicon::ReceiptPercent)
                ->color('warning'),
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
