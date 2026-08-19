<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes;

use App\Filament\Pages\Reportes\Widgets\AlertasOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\IngresosReservasChart;
use App\Filament\Pages\Reportes\Widgets\KpisInteligenciaNegocioWidget;
use App\Filament\Pages\Reportes\Widgets\PromocionesInteligenciaNegocioWidget;
use App\Filament\Pages\Reportes\Widgets\ReservasEstadoChart;
use App\Filament\Pages\Reportes\Widgets\TendenciaTemporadaChart;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $form
 */
final class TableroInteligenciaNegocio extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected string $view = 'filament.pages.reportes.tablero-inteligencia-negocio';

    protected static ?string $slug = 'tablero-inteligencia-negocio';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Inicio & Análisis';

    protected static ?string $navigationLabel = 'Inteligencia de Negocio';

    protected static ?string $title = 'Dashboard de Inteligencia de Negocio';

    protected static ?int $navigationSort = 0;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];

        $this->form->fill($this->data);
        $this->cargarDashboard();
    }

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'form' => $this->form($this->makeSchema()),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Desde')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->cargarDashboard();
                            }),

                        DatePicker::make('fecha_fin')
                            ->label('Hasta')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->cargarDashboard();
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function cargarDashboard(): null
    {
        $prop1 = 'cachedHeaderWidgetsSchemaComponents';
        $prop2 = 'cachedFooterWidgetsSchemaComponents';
        unset($this->{$prop1}, $this->{$prop2});

        return null;
    }

    /** @return array<class-string> */
    protected function getHeaderWidgets(): array
    {
        return [
            KpisInteligenciaNegocioWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int
    {
        return 1;
    }

    /** @return array<class-string> */
    protected function getFooterWidgets(): array
    {
        return [
            TendenciaTemporadaChart::class,
            IngresosReservasChart::class,
            ReservasEstadoChart::class,
            PromocionesInteligenciaNegocioWidget::class,
            AlertasOperacionWidget::class,
        ];
    }

    /** @return array<string, int> */
    public function getFooterWidgetsColumns(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    /** @return array<string, mixed> */
    public function getWidgetData(): array
    {
        return [
            'fechaInicio' => is_string($this->data['fecha_inicio'] ?? null)
                ? $this->data['fecha_inicio']
                : now()->startOfMonth()->format('Y-m-d'),
            'fechaFin' => is_string($this->data['fecha_fin'] ?? null)
                ? $this->data['fecha_fin']
                : now()->format('Y-m-d'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        $roleName = is_string($superAdminRole) ? $superAdminRole : 'super_admin';

        return $user->is_admin === true
            || $user->hasRole($roleName)
            || $user->can('Reportes:InteligenciaNegocio')
            || $user->can('page_TableroInteligenciaNegocio');
    }
}
