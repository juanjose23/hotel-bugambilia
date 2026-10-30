<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\BusinessLogic\Shared\Reportes\DecidirModoReporte;
use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use App\Filament\Shared\Forms\ReporteFormSchema;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\User;
use App\Support\Barcode\Support\ReporteConfig;
use Barryvdh\DomPDF\PDF;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\DatabaseNotification;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ManejaPaginaReporte
{
    /** @var array<string, mixed>|null */
    public ?array $reportData = [];

    public string $pageSize = 'letter';

    public string $orientation = 'portrait';

    public ?string $reporteListoUrl = null;

    public ?string $reporteListoNombre = null;

    abstract public function getModuloReportes(): string;

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema(ReporteFormSchema::construir(
                    modulo: $this->getModuloReportes(),
                    camposEspecificos: $this->getCamposFiltrosEspecificos(),
                    labelReporte: $this->getLabelReporte(),
                    incluirFechas: $this->incluyeFechasReporte(),
                    onFechasActualizadas: $this->getOnFechasActualizadasCallback(),
                ))
                ->statePath('reportData'),
        ];
    }

    /** @return array<int, Component> */
    protected function getCamposFiltrosEspecificos(): array
    {
        return [];
    }

    protected function getLabelReporte(): string
    {
        return 'Reporte Analítico';
    }

    protected function incluyeFechasReporte(): bool
    {
        return true;
    }

    protected function getOnFechasActualizadasCallback(): ?Closure
    {
        return null;
    }

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
            'mes_anterior' => [
                $this->reportData['fecha_inicio'] = now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->reportData['fecha_fin'] = now()->subMonth()->endOfMonth()->format('Y-m-d'),
                $this->reportData['fecha_desde'] = now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->reportData['fecha_hasta'] = now()->subMonth()->endOfMonth()->format('Y-m-d'),
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
     * Procesa la solicitud de reporte usando el dispatcher directo del dominio
     * y genera el PDF en disco antes de tirarlo al visor en nueva pestaña.
     *
     * @param  array<string, mixed>  $params
     */
    public function procesarDescargaReporte(string $modulo, string $reporte, array $params): ?string
    {
        $codigo = ReporteConfig::getCodigo($modulo, $reporte) ?? $reporte;

        try {
            $params['pageSize'] = $this->pageSize;
            $params['orientation'] = $this->orientation;
            $titulo = ReporteConfig::getReportes()[$modulo][$reporte]['titulo'] ?? $codigo;

            // Decidir si debe ir a segundo plano por rango extenso o petición explícita
            $decisor = app(DecidirModoReporte::class);
            if (! isset($params['directo']) && ! isset($params['forzar_directo']) && $decisor->debeIrABackground($params)) {
                $usuarioId = (int) (auth()->id() ?? 0);
                GenerarReporteJob::dispatch($codigo, $params, $usuarioId);

                $dias = $decisor->diasDeRango($params);
                $diasMsg = $dias !== null ? " ({$dias} días de consulta)" : '';

                Notification::make("reporte-bg-init-{$codigo}")
                    ->title('⏳ Generando reporte en segundo plano')
                    ->body("El {$codigo} ({$titulo}) comprende un rango extenso{$diasMsg}. Se está generando en segundo plano y recibirás una notificación en el panel cuando el PDF esté listo.")
                    ->info()
                    ->duration(10000)
                    ->send();

                $this->dispatch('close-pending-tab');

                return null;
            }

            $params['directo'] = 1;

            // 1. Generar el archivo PDF directamente con el Dispatcher de dominio
            $token = (string) Str::uuid();
            $tempDir = storage_path('app/temp-reportes');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $dispatcher = app(ReporteDispatcher::class);
            $pdfResult = $dispatcher->generar($codigo, $params);
            $pdfContent = $pdfResult instanceof PDF ? $pdfResult->output() : $pdfResult->getContent();

            if (empty($pdfContent)) {
                throw new \RuntimeException('El reporte no produjo contenido PDF.');
            }

            $safeFilename = "{$codigo}-".Str::slug($titulo).'.pdf';
            file_put_contents("{$tempDir}/{$token}.pdf", $pdfContent);

            $ttlConfig = config('reportes.token_ttl_minutos');
            $ttlMinutos = is_int($ttlConfig) ? $ttlConfig : 30;
            $expiresAt = now()->addMinutes($ttlMinutos);

            cache()->put("reporte_meta_{$token}", [
                'filename' => $safeFilename,
                'titulo' => $titulo,
                'codigo' => $codigo,
                'user_id' => auth()->id(),
                'created_at' => now()->timestamp,
                'expires_at' => $expiresAt->timestamp,
            ], $expiresAt);

            $visorUrl = route('admin.reportes.visor', ['token' => $token]);
            $this->reporteListoUrl = $visorUrl;
            $this->reporteListoNombre = "{$codigo} — {$titulo}";

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
                        ->url($visorUrl, shouldOpenInNewTab: true),
                ]);

            $notif->send();

            /** @var User|null $usuario */
            $usuario = auth()->user();
            if ($usuario instanceof User) {
                $usuario->notifications()
                    ->where('data->body', 'like', "%{$codigo}%")
                    ->delete();

                $usuario->notifyNow(new DatabaseNotification($notif->getDatabaseMessage()));
            }

            $this->dispatch('open-new-tab', url: $visorUrl);

            return $visorUrl;
        } catch (\Throwable $e) {
            Log::error('Error en procesarDescargaReporte: '.$e->getMessage(), ['exception' => $e]);
            $this->dispatch('close-pending-tab');

            Notification::make()
                ->title('Error al generar reporte')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }

    public function descargarReporte(): ?string
    {
        if (! property_exists($this, 'reportForm') && ! isset($this->reportForm)) {
            $this->dispatch('close-pending-tab');

            return null;
        }

        try {
            $data = $this->reportForm->getState();
        } catch (\Throwable $e) {
            $this->dispatch('close-pending-tab');
            throw $e;
        }

        if (isset($data['reportData']) && is_array($data['reportData'])) {
            $data = array_merge($this->reportData ?? [], $data['reportData']);
        } else {
            $data = array_merge($this->reportData ?? [], $data);
        }

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
            'meses' => $data['meses'] ?? null,
            'dias' => $data['dias'] ?? null,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ], fn ($val) => $val !== null && $val !== '');

        return $this->procesarDescargaReporte($this->getModuloReportes(), $reporte, $params);
    }

    public function descargarExcel(): ?string
    {
        if (! property_exists($this, 'reportForm') && ! isset($this->reportForm)) {
            $this->dispatch('close-pending-tab');

            return null;
        }

        try {
            $data = $this->reportForm->getState();
        } catch (\Throwable $e) {
            $this->dispatch('close-pending-tab');
            throw $e;
        }

        if (isset($data['reportData']) && is_array($data['reportData'])) {
            $data = array_merge($this->reportData ?? [], $data['reportData']);
        } else {
            $data = array_merge($this->reportData ?? [], $data);
        }

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

            return $url;
        } catch (\Throwable $e) {
            $this->dispatch('close-pending-tab');

            Notification::make()
                ->title('Error al exportar Excel')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
