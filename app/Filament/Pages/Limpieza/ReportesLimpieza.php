<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Repository\Queries\Limpieza\Reportes\ObtenerReporteOperacionHoteleraQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
final class ReportesLimpieza extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte {
        ManejaPaginaReporte::getForms insteadof InteractsWithForms;
    }

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
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
            'fecha_desde' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => now()->format('Y-m-d'),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $rawInicio = $this->reportData['fecha_inicio'] ?? $this->reportData['fecha_desde'] ?? null;
        $fechaInicio = is_string($rawInicio) ? $rawInicio : now()->startOfMonth()->toDateString();

        $rawFin = $this->reportData['fecha_fin'] ?? $this->reportData['fecha_hasta'] ?? null;
        $fechaFin = is_string($rawFin) ? $rawFin : now()->toDateString();

        $datos = app(ObtenerReporteOperacionHoteleraQuery::class)->ejecutar([
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
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

        return $user->can('Page:ReportesLimpieza')
            || $user->can('Limpieza:ReporteOperacionHotelera');
    }
}
