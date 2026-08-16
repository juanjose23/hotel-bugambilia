<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Reservas\Reserva;
use App\Support\ReporteConfig;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
class ReportesEjecutivos extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected string $view = 'filament.pages.reportes.reportes-ejecutivos';

    protected static ?string $slug = 'reportes-ejecutivos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartPie;

    protected static string|UnitEnum|null $navigationGroup = 'Analítica & Reportes';

    protected static ?string $navigationLabel = 'Tablero Ejecutivo & Financiero';

    protected static ?string $title = 'Centro de Inteligencia Financiera y Tomador de Decisiones';

    protected static ?int $navigationSort = 1;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public float $totalIngresosReservas = 0.0;

    public float $totalRecaudado = 0.0;

    public float $totalCuentasPorCobrar = 0.0;

    public float $totalFacturadoFiscal = 0.0;

    public int $cantidadReservas = 0;

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => null,
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];

        $this->cargarMetricas();
    }

    public function cargarMetricas(): void
    {
        $fechaInicio = $this->reportData['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $this->reportData['fecha_fin'] ?? now()->format('Y-m-d');

        $queryReserva = Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin]);
        $this->totalIngresosReservas = (float) $queryReserva->sum('total');
        $this->totalRecaudado = (float) $queryReserva->sum('total_pagado');
        $this->cantidadReservas = $queryReserva->count();

        $this->totalCuentasPorCobrar = (float) (Reserva::sum('saldo') + Cuenta::sum('saldo'));

        $this->totalFacturadoFiscal = (float) Factura::whereBetween('fecha_emision', [$fechaInicio, $fechaFin])->sum('total');
    }

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Seleccionar Reporte Financiero')
                        ->options(ReporteConfig::getSelectOptions('financiero'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Selecciona un reporte financiero...'),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->state(fn ($get) => ReporteConfig::getDescripcion('financiero', $get('reporte')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha Inicio')
                        ->default(now()->startOfMonth())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->cargarMetricas())
                        ->native(false),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha Fin')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->cargarMetricas())
                        ->native(false),
                ])
                ->statePath('reportData'),
        ];
    }

    public function descargarReporte(): mixed
    {
        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';
        if (! $reporte) {
            return null;
        }

        $params = [
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
        ];

        try {
            $url = ReporteConfig::getUrl('financiero', $reporte, $params, 'pdf');
            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        $roleName = is_string($superAdminRole) ? $superAdminRole : 'super_admin';

        return $user->hasRole($roleName) || $user->can('Reservas:ReporteOcupacion');
    }
}
