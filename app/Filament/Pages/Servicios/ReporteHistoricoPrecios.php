<?php

declare(strict_types=1);

namespace App\Filament\Pages\Servicios;

use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Filament\Shared\Forms\ServicioSelect;
use App\Repository\Queries\Servicios\Reportes\ObtenerMetricasReporteServiciosQuery;
use App\Support\Barcode\Support\CachedOptions;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
final class ReporteHistoricoPrecios extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte {
        ManejaPaginaReporte::getForms insteadof InteractsWithForms;
    }

    protected ObtenerMetricasReporteServiciosQuery $metricasQuery;

    public function getModuloReportes(): string
    {
        return 'servicios';
    }

    protected string $view = 'filament.resources.servicios.reporte-historico-precios';

    protected static ?string $slug = 'reporte-historico-precios-servicios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Servicios & Promociones';

    protected static ?string $navigationLabel = 'Histórico de Precios';

    protected static ?string $title = 'Histórico de Servicios por Precio por Moneda';

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public function boot(ObtenerMetricasReporteServiciosQuery $metricasQuery): void
    {
        $this->metricasQuery = $metricasQuery;
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => 'historico_precios',
            'categoria_id' => null,
            'servicio_id' => null,
            'moneda_id' => null,
            'estado' => null,
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return $this->metricasQuery->ejecutar();
    }

    protected function incluyeFechasReporte(): bool
    {
        return false;
    }

    /** @return array<int, mixed> */
    protected function getCamposFiltrosEspecificos(): array
    {
        return [
            Select::make('categoria_id')
                ->label('Filtrar por Categoría (Opcional)')
                ->options(fn () => CachedOptions::catalogos(CatalogoTipo::CATEGORIA_SERVICIO->value))
                ->searchable()
                ->placeholder('Todas las categorías')
                ->native(false),

            ServicioSelect::make('servicio_id')
                ->placeholder('Todos los servicios'),

            Select::make('moneda_id')
                ->label('Filtrar por Moneda (Opcional)')
                ->options(fn () => CachedOptions::monedas())
                ->searchable()
                ->placeholder('Todas las monedas')
                ->native(false),

            Select::make('estado')
                ->label('Estado del Precio')
                ->options([
                    '' => 'Todos',
                    '1' => 'Vigente',
                    '2' => 'No Vigente',
                ])
                ->default('')
                ->native(false),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->can('Page:ReporteHistoricoPrecios') || $user->can('Servicios:ReporteHistoricoPrecios'));
    }
}
