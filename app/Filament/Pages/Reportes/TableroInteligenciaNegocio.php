<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes;

use App\Filament\Pages\Reportes\Widgets\AlertasOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\AnomaliasOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\EmbudoConversionWidget;
use App\Filament\Pages\Reportes\Widgets\IngresosReservasChart;
use App\Filament\Pages\Reportes\Widgets\KpisInteligenciaNegocioWidget;
use App\Filament\Pages\Reportes\Widgets\PromocionesInteligenciaNegocioWidget;
use App\Filament\Pages\Reportes\Widgets\RadialOcupacionWidget;
use App\Filament\Pages\Reportes\Widgets\ReservasEstadoChart;
use App\Filament\Pages\Reportes\Widgets\ResumenOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\ResumenRestauranteWidget;
use App\Filament\Pages\Reportes\Widgets\TendenciaTemporadaChart;
use App\Filament\Pages\Reportes\Widgets\TopPlatosChart;
use App\Filament\Pages\Reportes\Widgets\VentasPorHoraChart;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use UnitEnum;

final class TableroInteligenciaNegocio extends BaseDashboard
{
    use HasFiltersForm, HasPageShield;

    protected static string $routePath = 'tablero-inteligencia-negocio';

    protected static ?string $slug = 'tablero-inteligencia-negocio';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Inicio & Análisis';

    protected static ?string $navigationLabel = 'Inteligencia de Negocio';

    protected static ?string $title = 'Dashboard de Inteligencia de Negocio';

    protected static ?int $navigationSort = 0;

    public function mount(): void
    {
        $this->mountHasFilters();

        if (empty($this->filters['fecha_inicio']) || empty($this->filters['fecha_fin'])) {
            $this->filters = array_merge($this->filters ?? [], [
                'preset' => 'este_mes',
                'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
                'fecha_fin' => now()->format('Y-m-d'),
            ]);
            $this->getFiltersForm()->fill($this->filters);
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filtros Analíticos & Período de Control')
                    ->description('Seleccione un rango rápido o defina fechas personalizadas para sincronizar en tiempo real todos los indicadores y modelos de datos.')
                    ->icon(Heroicon::AdjustmentsHorizontal)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->schema([
                        Select::make('preset')
                            ->label('Rango Rápido')
                            ->prefixIcon(Heroicon::Clock)
                            ->options([
                                'hoy' => 'Hoy',
                                'ultimos_7_dias' => 'Últimos 7 días',
                                'este_mes' => 'Este Mes (Mes en curso)',
                                'mes_anterior' => 'Mes Anterior',
                                'ultimos_30_dias' => 'Últimos 30 días',
                                'este_trimestre' => 'Este Trimestre',
                                'este_ano' => 'Año en curso (YTD)',
                                'personalizado' => 'Rango Personalizado',
                            ])
                            ->default('este_mes')
                            ->selectablePlaceholder(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $hoy = CarbonImmutable::now();

                                match ($state) {
                                    'hoy' => [
                                        $set('fecha_inicio', $hoy->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->format('Y-m-d')),
                                    ],
                                    'ultimos_7_dias' => [
                                        $set('fecha_inicio', $hoy->subDays(6)->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->format('Y-m-d')),
                                    ],
                                    'este_mes' => [
                                        $set('fecha_inicio', $hoy->startOfMonth()->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->format('Y-m-d')),
                                    ],
                                    'mes_anterior' => [
                                        $set('fecha_inicio', $hoy->subMonthNoOverflow()->startOfMonth()->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')),
                                    ],
                                    'ultimos_30_dias' => [
                                        $set('fecha_inicio', $hoy->subDays(29)->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->format('Y-m-d')),
                                    ],
                                    'este_trimestre' => [
                                        $set('fecha_inicio', $hoy->startOfQuarter()->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->format('Y-m-d')),
                                    ],
                                    'este_ano' => [
                                        $set('fecha_inicio', $hoy->startOfYear()->format('Y-m-d')),
                                        $set('fecha_fin', $hoy->format('Y-m-d')),
                                    ],
                                    default => null,
                                };
                            }),

                        DatePicker::make('fecha_inicio')
                            ->label('Desde')
                            ->prefixIcon(Heroicon::Calendar)
                            ->format('Y-m-d')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('preset', 'personalizado');
                            }),

                        DatePicker::make('fecha_fin')
                            ->label('Hasta')
                            ->prefixIcon(Heroicon::Calendar)
                            ->format('Y-m-d')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('preset', 'personalizado');
                            }),

                        TextEntry::make('estado_sincronizacion')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->state(function (Get $get): HtmlString {
                                $inicioRaw = $get('fecha_inicio');
                                $finRaw = $get('fecha_fin');

                                $inicioStr = is_string($inicioRaw) && $inicioRaw !== '' ? $inicioRaw : now()->startOfMonth()->format('Y-m-d');
                                $finStr = is_string($finRaw) && $finRaw !== '' ? $finRaw : now()->format('Y-m-d');

                                $inicio = CarbonImmutable::parse($inicioStr)->startOfDay();
                                $fin = CarbonImmutable::parse($finStr)->endOfDay();
                                if ($inicio->greaterThan($fin)) {
                                    [$inicio, $fin] = [$fin->startOfDay(), $inicio->endOfDay()];
                                }

                                $dias = max(1, (int) $inicio->startOfDay()->diffInDays($fin->startOfDay()) + 1);
                                $antInicio = $inicio->subDays($dias)->format('d/m/Y');
                                $antFin = $inicio->subDay()->format('d/m/Y');
                                $fInicio = $inicio->format('d/m/Y');
                                $fFin = $fin->format('d/m/Y');

                                $html = <<<HTML
                                <div class="w-full rounded-xl border border-gray-200 bg-gray-50/70 p-3 text-xs dark:border-gray-800 dark:bg-gray-900/50">
                                    <div wire:loading.delay.flex class="items-center gap-2 font-medium text-amber-600 dark:text-amber-400">
                                        <svg class="h-4 w-4 animate-spin text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>Recalculando estadísticas, KPIs y series temporales en tiempo real...</span>
                                    </div>
                                    <div wire:loading.remove class="flex flex-wrap items-center justify-between gap-2 text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                            <span>Período activo: <strong class="text-gray-900 dark:text-white">{$fInicio} al {$fFin}</strong> ({$dias} días)</span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                            Comparativa calculada contra: <strong>{$antInicio} al {$antFin}</strong> ({$dias} días previos)
                                        </div>
                                    </div>
                                </div>
                                HTML;

                                return new HtmlString($html);
                            }),
                    ]),
            ]);
    }

    /**
     * @return array<string, ?int>
     */
    public function getColumns(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            // Nivel 1: Scorecard Ejecutivo Global (KPIs & Sparklines)
            KpisInteligenciaNegocioWidget::class,

            // Nivel 2: Capacidad & Ocupación en Tiempo Real
            RadialOcupacionWidget::class,

            // Nivel 3: Evolución Temporal & Demanda
            IngresosReservasChart::class,
            TendenciaTemporadaChart::class,
            ReservasEstadoChart::class,

            // Nivel 4: Embudo de Conversión & Comportamiento Intradía
            EmbudoConversionWidget::class,
            VentasPorHoraChart::class,

            // Nivel 5: Rendimiento de Restaurante & Menú
            ResumenRestauranteWidget::class,
            TopPlatosChart::class,

            // Nivel 6: Estrategia Comercial & Promociones
            PromocionesInteligenciaNegocioWidget::class,

            // Nivel 7: Eficiencia Operativa, Anomalías & Alertas
            ResumenOperacionWidget::class,
            AnomaliasOperacionWidget::class,
            AlertasOperacionWidget::class,
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('Page:TableroInteligenciaNegocio')
            || $user->can('Reportes:InteligenciaNegocio');
    }
}
