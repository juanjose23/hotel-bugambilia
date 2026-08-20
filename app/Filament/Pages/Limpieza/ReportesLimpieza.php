<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Repository\Queries\Limpieza\Reportes\ObtenerReporteOperacionHoteleraQuery;
use App\Support\ReporteConfig;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
final class ReportesLimpieza extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte;

    protected string $view = 'filament.resources.limpieza.reportes-limpieza';

    protected static ?string $slug = 'reportes-limpieza';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza & Lavandería';

    protected static ?string $navigationLabel = 'Reportes de Limpieza';

    protected static ?string $title = 'Reportes de Limpieza y Operación';

    protected static ?int $navigationSort = 99;

    public function getModuloReportes(): string
    {
        return 'limpieza';
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => 'operacion_hotelera',
            'fecha_desde' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => now()->format('Y-m-d'),
        ];
    }

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Reporte Analítico')
                        ->options(ReporteConfig::getSelectOptions('limpieza'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable(),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->state(fn ($get) => ReporteConfig::getDescripcion('limpieza', $get('reporte')) ?? 'Seleccione un reporte de la lista.')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

                    DatePicker::make('fecha_desde')
                        ->label('Desde')
                        ->default(now()->startOfMonth())
                        ->required()
                        ->native(false),

                    DatePicker::make('fecha_hasta')
                        ->label('Hasta')
                        ->default(now())
                        ->required()
                        ->native(false),
                ])
                ->statePath('reportData'),
        ];
    }

    public function descargarReporte(): null
    {
        $data = $this->reportData ?? [];

        $fechaDesde = is_string($data['fecha_desde'] ?? null)
            ? $data['fecha_desde']
            : now()->startOfMonth()->format('Y-m-d');

        $fechaHasta = is_string($data['fecha_hasta'] ?? null)
            ? $data['fecha_hasta']
            : now()->format('Y-m-d');
        $reporte = is_string($data['reporte'] ?? null) && $data['reporte'] !== ''
            ? $data['reporte']
            : 'operacion_hotelera';

        $url = ReporteConfig::getUrl('limpieza', $reporte, [
            'reporte' => $reporte,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'fecha_inicio' => $fechaDesde,
            'fecha_fin' => $fechaHasta,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ]);

        $this->dispatch('open-new-tab', url: $url);

        Notification::make()
            ->title('Reporte generado')
            ->body('Se abrió el PDF seleccionado en una nueva pestaña.')
            ->success()
            ->send();

        return null;
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $datos = app(ObtenerReporteOperacionHoteleraQuery::class)->ejecutar([
            'fecha_desde' => $this->reportData['fecha_desde'] ?? now()->startOfMonth()->toDateString(),
            'fecha_hasta' => $this->reportData['fecha_hasta'] ?? now()->toDateString(),
        ]);

        return [
            'resumenOperacion' => $datos['resumen'],
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
            || $user->can('Limpieza:ReporteOperacionHotelera')
            || $user->can('page_ReportesLimpieza');
    }
}
