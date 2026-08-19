<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\Support\ReporteConfig;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

trait ManejaPaginaReporte
{
    /** @var array<string, mixed>|null */
    public ?array $reportData = [];

    public string $pageSize = 'letter';

    public string $orientation = 'portrait';

    abstract public function getModuloReportes(): string;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('configuracionPagina')
                ->label('Configuración de Página')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->modalHeading('Configurar Formato de Impresión PDF')
                ->modalDescription('Selecciona el tamaño de papel y la orientación deseada para los reportes PDF.')
                ->modalSubmitActionLabel('Guardar Configuración')
                ->modalWidth('md')
                ->fillForm(fn (): array => [
                    'pageSize' => $this->pageSize,
                    'orientation' => $this->orientation,
                ])
                ->schema([
                    Select::make('pageSize')
                        ->label('Tamaño de Papel')
                        ->options([
                            'letter' => 'Carta (Letter - 216mm x 279mm)',
                            'a4' => 'A4 (210mm x 297mm)',
                            'legal' => 'Oficio (Legal - 216mm x 356mm)',
                        ])
                        ->required()
                        ->native(false),

                    Select::make('orientation')
                        ->label('Orientación de Página')
                        ->options([
                            'portrait' => 'Vertical (Retrato / Portrait)',
                            'landscape' => 'Horizontal (Apaisado / Landscape)',
                        ])
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $this->pageSize = is_string($data['pageSize'] ?? null) ? $data['pageSize'] : 'letter';
                    $this->orientation = is_string($data['orientation'] ?? null) ? $data['orientation'] : 'portrait';

                    Notification::make()
                        ->title('Configuración de página actualizada')
                        ->body('Formato: '.strtoupper($this->pageSize).' ('.ucfirst($this->orientation).')')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function aplicarPresetFecha(string $preset): void
    {
        $this->reportData ??= [];

        match ($preset) {
            'hoy' => [
                $this->reportData['fecha_inicio'] = now()->format('Y-m-d'),
                $this->reportData['fecha_fin'] = now()->format('Y-m-d'),
                $this->reportData['fecha_desde'] = now()->format('Y-m-d'),
                $this->reportData['fecha_hasta'] = now()->format('Y-m-d'),
            ],
            'este_mes' => [
                $this->reportData['fecha_inicio'] = now()->startOfMonth()->format('Y-m-d'),
                $this->reportData['fecha_fin'] = now()->format('Y-m-d'),
                $this->reportData['fecha_desde'] = now()->startOfMonth()->format('Y-m-d'),
                $this->reportData['fecha_hasta'] = now()->format('Y-m-d'),
            ],
            'ultimos_30' => [
                $this->reportData['fecha_inicio'] = now()->subDays(30)->format('Y-m-d'),
                $this->reportData['fecha_fin'] = now()->format('Y-m-d'),
                $this->reportData['fecha_desde'] = now()->subDays(30)->format('Y-m-d'),
                $this->reportData['fecha_hasta'] = now()->format('Y-m-d'),
            ],
            'este_ano' => [
                $this->reportData['fecha_inicio'] = now()->startOfYear()->format('Y-m-d'),
                $this->reportData['fecha_fin'] = now()->format('Y-m-d'),
                $this->reportData['fecha_desde'] = now()->startOfYear()->format('Y-m-d'),
                $this->reportData['fecha_hasta'] = now()->format('Y-m-d'),
            ],
            default => null,
        };

        if (property_exists($this, 'reportForm')) {
            $this->reportForm->fill($this->reportData);
        }
    }

    public function descargarReporte(): mixed
    {
        if (! property_exists($this, 'reportForm')) {
            return null;
        }

        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';

        if (! $reporte) {
            return null;
        }

        $params = array_filter([
            'fecha_inicio' => $data['fecha_inicio'] ?? $data['fecha_desde'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? $data['fecha_hasta'] ?? null,
            'fecha_desde' => $data['fecha_desde'] ?? $data['fecha_inicio'] ?? null,
            'fecha_hasta' => $data['fecha_hasta'] ?? $data['fecha_fin'] ?? null,
            'estado' => $data['estado'] ?? null,
            'tipo' => $data['tipo'] ?? null,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ], fn ($val) => $val !== null && $val !== '');

        try {
            $url = ReporteConfig::getUrl($this->getModuloReportes(), $reporte, $params, 'pdf');
            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    public function descargarExcel(): mixed
    {
        if (! property_exists($this, 'reportForm')) {
            return null;
        }

        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';

        if (! $reporte) {
            return null;
        }

        $params = array_filter([
            'fecha_inicio' => $data['fecha_inicio'] ?? $data['fecha_desde'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? $data['fecha_hasta'] ?? null,
            'fecha_desde' => $data['fecha_desde'] ?? $data['fecha_inicio'] ?? null,
            'fecha_hasta' => $data['fecha_hasta'] ?? $data['fecha_fin'] ?? null,
            'estado' => $data['estado'] ?? null,
            'tipo' => $data['tipo'] ?? null,
        ], fn ($val) => $val !== null && $val !== '');

        try {
            $url = ReporteConfig::getUrl($this->getModuloReportes(), $reporte, $params, 'excel');
            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }
}
