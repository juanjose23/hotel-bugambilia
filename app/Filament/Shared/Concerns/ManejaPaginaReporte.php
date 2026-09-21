<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\BusinessLogic\Shared\Reportes\ContarRegistrosReporte;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\User;
use App\Support\ReporteConfig;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\DatabaseNotification;
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

    /**
     * Procesa la descarga del reporte:
     * - Si supera el umbral de registros (>500) -> despacha Job y muestra notificación nativa Filament.
     * - Si es <= 500 registros -> abre la pestaña con la URL para descarga directa.
     *
     * @param  array<string, mixed>  $params
     */
    public function procesarDescargaReporte(string $modulo, string $reporte, array $params): void
    {
        $codigo = ReporteConfig::getCodigo($modulo, $reporte) ?? $reporte;
        $contador = app(ContarRegistrosReporte::class);

        if ($contador->superaUmbral($codigo, $params)) {
            $userId = (int) (auth()->id() ?? 0);

            GenerarReporteJob::dispatch(
                codigoReporte: $codigo,
                parametros: $params,
                usuarioId: $userId,
            );

            $urlSegundoPlano = route('admin.reportes.en-proceso', ['codigo' => $codigo], false);
            $this->dispatch('open-new-tab', url: $urlSegundoPlano);

            Notification::make("reporte-bg-{$codigo}")
                ->title('⏳ Generando reporte en segundo plano')
                ->body("El reporte {$codigo} contiene más de 10,000 registros para generarse en vivo. Se está procesando en segundo plano y te avisaremos en la campana en cuanto esté listo para descargar.")
                ->warning()
                ->duration(12000)
                ->send();

            /** @var User|null $usuario */
            $usuario = auth()->user();
            if ($usuario instanceof User) {
                $usuario->notifications()
                    ->where('data->body', 'like', "%{$codigo}%")
                    ->delete();

                $bgNotif = Notification::make("reporte-bg-{$codigo}")
                    ->title('⏳ Reporte en segundo plano')
                    ->body("El reporte {$codigo} se está procesando. Recibirás un aviso con el botón de descarga aquí en la campana tan pronto finalice.")
                    ->warning();

                $usuario->notifyNow(new DatabaseNotification($bgNotif->getDatabaseMessage()));
            }

            return;
        }

        try {
            $params['directo'] = 1;
            $url = ReporteConfig::getUrl($modulo, $reporte, $params, 'pdf');
            $titulo = ReporteConfig::getReportes()[$modulo][$reporte]['titulo'] ?? $codigo;

            $notif = Notification::make("reporte-{$codigo}")
                ->title('📄 Reporte listo para visualizar')
                ->body("El {$codigo} ({$titulo}) se ha generado exitosamente.")
                ->success()
                ->duration(10000)
                ->actions([
                    Action::make('abrir')
                        ->label('Abrir Documento PDF')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->button()
                        ->color('primary')
                        ->url($url, shouldOpenInNewTab: true),
                ]);

            $notif->send();

            /** @var User|null $usuario */
            $usuario = auth()->user();
            if ($usuario instanceof User) {
                // Eliminar avisos previos del mismo reporte para que la campana no se llene de duplicados
                $usuario->notifications()
                    ->where('data->body', 'like', "%{$codigo}%")
                    ->delete();

                $usuario->notifyNow(new DatabaseNotification($notif->getDatabaseMessage()));
            }

            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('close-pending-tab');

            Notification::make()
                ->title('Error al generar reporte')
                ->body($e->getMessage())
                ->danger()
                ->send();
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
            $this->dispatch('close-pending-tab');

            Notification::make()
                ->title('Seleccione un reporte')
                ->body('Por favor seleccione un reporte analítico de la lista antes de generar el documento.')
                ->warning()
                ->send();

            return null;
        }

        $params = array_filter([
            'fecha_inicio' => $data['fecha_inicio'] ?? $data['fecha_desde'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? $data['fecha_hasta'] ?? null,
            'fecha_desde' => $data['fecha_desde'] ?? $data['fecha_inicio'] ?? null,
            'fecha_hasta' => $data['fecha_hasta'] ?? $data['fecha_fin'] ?? null,
            'estado' => $data['estado'] ?? null,
            'tipo' => $data['tipo'] ?? null,
            'tipo_pago' => $data['tipo_pago'] ?? null,
            'categoria_id' => $data['categoria_id'] ?? null,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ], fn ($val) => $val !== null && $val !== '');

        $this->procesarDescargaReporte($this->getModuloReportes(), $reporte, $params);

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
            $this->dispatch('close-pending-tab');

            Notification::make()
                ->title('Seleccione un reporte')
                ->body('Por favor seleccione un reporte analítico de la lista antes de exportar a Excel.')
                ->warning()
                ->send();

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
            $codigo = ReporteConfig::getCodigo($this->getModuloReportes(), $reporte) ?? $reporte;

            $notif = Notification::make()
                ->title('Archivo Excel generado')
                ->body("El reporte {$codigo} se ha exportado exitosamente.")
                ->success()
                ->duration(10000)
                ->actions([
                    Action::make('descargar')
                        ->label('Descargar Hoja de Cálculo')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->button()
                        ->color('success')
                        ->url($url, shouldOpenInNewTab: true),
                ]);

            $notif->send();

            /** @var User|null $usuario */
            $usuario = auth()->user();
            if ($usuario instanceof User) {
                $notif->sendToDatabase($usuario);
            }

            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('close-pending-tab');

            Notification::make()
                ->title('Error al exportar Excel')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }

        return null;
    }
}
