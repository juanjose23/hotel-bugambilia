<?php

declare(strict_types=1);

namespace App\Filament\Pages\Servicios;

use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Filament\Shared\Forms\ServicioSelect;
use App\Repository\Queries\Servicios\Reportes\ObtenerMetricasReporteServiciosQuery;
use App\Support\CachedOptions;
use App\Support\ReporteConfig;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use InvalidArgumentException;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
final class ReporteHistoricoPrecios extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte;

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

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Reporte Analítico')
                        ->options(ReporteConfig::getSelectOptions('servicios'))
                        ->default('historico_precios')
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Selecciona un reporte de la lista...'),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->state(fn ($get) => ReporteConfig::getDescripcion('servicios', $get('reporte')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

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
                ])
                ->statePath('reportData'),
        ];
    }

    public function descargarReporte(): mixed
    {
        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : 'historico_precios';
        if (! $reporte) {
            return null;
        }

        $params = [
            'categoria_id' => $data['categoria_id'] ?? null,
            'servicio_id' => $data['servicio_id'] ?? null,
            'moneda_id' => $data['moneda_id'] ?? null,
            'estado' => $data['estado'] ?: null,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ];

        try {
            $url = ReporteConfig::getUrl('servicios', $reporte, $params, 'pdf');
            $this->dispatch('open-new-tab', url: $url);
        } catch (InvalidArgumentException) {
            return null;
        }

        return null;
    }

    public function descargarExcel(): mixed
    {
        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : 'historico_precios';
        if (! $reporte) {
            return null;
        }

        $params = [
            'categoria_id' => $data['categoria_id'] ?? null,
            'servicio_id' => $data['servicio_id'] ?? null,
            'moneda_id' => $data['moneda_id'] ?? null,
            'estado' => $data['estado'] ?: null,
        ];

        try {
            $url = ReporteConfig::getUrl('servicios', $reporte, $params, 'excel');
            $this->dispatch('open-new-tab', url: $url);
        } catch (InvalidArgumentException) {
            return null;
        }

        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('Servicios:ReporteHistoricoPrecios') ?? false;
    }
}
