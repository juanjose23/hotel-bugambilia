<?php

declare(strict_types=1);

namespace App\Jobs;

use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use App\Events\Shared\ReporteGenerado;
use App\Events\Shared\ReporteIniciado;
use App\Notifications\Reportes\Shared\NotificadorReportes;
use App\Repository\Models\User;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;
use App\Support\Barcode\Support\Pdf\Concerns\GuardaReporte;
use Barryvdh\DomPDF\PDF;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

final class GenerarReporteJob implements ShouldQueue
{
    use GuardaReporte, Queueable;

    /** Número máximo de intentos antes de marcar el job como fallido. */
    public int $tries = 2;

    /** Tiempo máximo (segundos) para generar un reporte masivo. */
    public int $timeout = 600;

    /** @var array<int, int> Segundos de espera entre reintentos para no saturar el worker. */
    public array $backoff = [60, 120];

    /** @param array<string, mixed> $parametros */
    public function __construct(
        public string $codigoReporte,
        public array $parametros,
        public int $usuarioId,
    ) {}

    public function handle(): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        if ($this->usuarioId > 0) {
            ReporteIniciado::dispatch(
                $this->usuarioId,
                $this->codigoReporte,
            );
        }

        $dispatcher = app(ReporteDispatcher::class);
        $pdf = $dispatcher->generar($this->codigoReporte, $this->parametros);

        $pdfContent = $pdf instanceof PDF
            ? (string) $pdf->output()
            : (string) $pdf->getContent();

        if (empty($pdfContent)) {
            throw new \RuntimeException("El reporte {$this->codigoReporte} no produjo contenido PDF.");
        }

        // 1. Guardar copia en disco temporal con token seguro
        $token = (string) Str::uuid();
        $tempDir = storage_path('app/temp-reportes');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        file_put_contents("{$tempDir}/{$token}.pdf", $pdfContent);

        $ttlConfig = config('reportes.token_ttl_minutos');
        $ttlMinutos = is_int($ttlConfig) ? $ttlConfig : 30;
        $expiresAt = now()->addMinutes($ttlMinutos);
        $safeFilename = "{$this->codigoReporte}-".now()->format('Ymd_His').'.pdf';

        cache()->put("reporte_meta_{$token}", [
            'filename' => $safeFilename,
            'titulo' => $this->codigoReporte,
            'codigo' => $this->codigoReporte,
            'user_id' => $this->usuarioId > 0 ? $this->usuarioId : null,
            'created_at' => now()->timestamp,
            'expires_at' => $expiresAt->timestamp,
        ], $expiresAt);

        $visorUrl = route('admin.reportes.visor', ['token' => $token]);

        // 2. Guardar auditoría permanente si aplica
        $this->guardarAuditoria(
            tipoReporte: $this->codigoReporte,
            parametros: $this->parametros,
            pdf: $pdf,
            usuarioId: $this->usuarioId,
        );

        // 3. Notificar al usuario solicitante en la base de datos de Filament
        if ($this->usuarioId > 0) {
            /** @var User|null $usuario */
            $usuario = User::query()->find($this->usuarioId);
            if ($usuario instanceof User) {
                Notification::make("reporte-bg-{$token}")
                    ->title('📄 Reporte Histórico Listo')
                    ->body("El {$this->codigoReporte} se ha generado exitosamente en segundo plano.")
                    ->success()
                    ->actions([
                        Action::make('abrir')
                            ->label('Abrir Documento PDF')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->button()
                            ->color('primary')
                            ->url($visorUrl, shouldOpenInNewTab: true),
                    ])
                    ->sendToDatabase($usuario);
            }

            ReporteGenerado::dispatch(
                $this->usuarioId,
                $this->codigoReporte,
                $visorUrl,
            );
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if ($this->usuarioId <= 0) {
            return;
        }

        try {
            $usuario = app(BuscarUsuarioCuentaQuery::class)->porId($this->usuarioId);
            if ($usuario !== null) {
                app(NotificadorReportes::class)->reporteFallido(
                    $usuario,
                    $this->codigoReporte,
                    $exception?->getMessage() ?? 'Error inesperado al generar el documento.',
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
