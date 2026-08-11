<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\Pages;

use App\Enums\Cuentas\EstadoCuenta;
use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Interactors\Cuentas\Cobros\SolicitarCobroCuenta;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Cuentas\Gestion\CerrarCuentaYGenerarVenta;
use App\Repository\Models\Cuentas\Cuenta;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

final class ViewCuenta extends ViewRecord
{
    protected static string $resource = CuentaResource::class;

    protected static ?string $title = 'Detalle de Cuenta';

    protected function resolveRecord(int|string $key): Model
    {
        $record = parent::resolveRecord($key);
        $record->loadMissing(['moneda', 'cliente', 'estancia.habitacion', 'reserva.moneda']);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            // ─── 1. ABRIR CUENTA (SOLICITADA → ABIERTA) ────────────────────
            Action::make('abrir_cuenta')
                ->label('Abrir Cuenta')
                ->icon('heroicon-o-folder-open')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Activar Cuenta')
                ->modalDescription('¿Confirmas activar esta cuenta para recibir cargos y pagos?')
                ->modalSubmitActionLabel('Sí, abrir cuenta')
                ->visible(function (): bool {
                    $rec = $this->getRecord();

                    return $rec instanceof Cuenta && ($rec->estado === EstadoCuenta::SOLICITADA || $rec->estado === EstadoCuenta::BLOQUEADA);
                })
                ->action(function (): void {
                    /** @var Cuenta $cuenta */
                    $cuenta = $this->getRecord();
                    $userId = auth()->id() !== null ? (int) auth()->id() : null;

                    try {
                        app(AbrirCuenta::class)->ejecutar(
                            tipo: $cuenta->tipo_cuenta,
                            cuentaExistente: $cuenta,
                            usuarioId: $userId,
                        );

                        Notification::make()
                            ->title('Cuenta abierta')
                            ->body("La cuenta #{$cuenta->numero_cuenta} está activa y lista para recibir cargos.")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $cuenta]));
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('No se pudo abrir la cuenta')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ─── 2. COBRAR CUENTA (con multi-moneda y datos del cliente) ───
            CobrarCuentaAction::makeFromResolver(
                resolverCuenta: function (): ?Cuenta {
                    /** @var Cuenta $record */
                    $record = $this->getRecord();

                    return $record->fresh();
                },
                onSuccess: function (Cuenta $cuenta): void {
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $cuenta]));
                },
            ),

            // ─── 3. SOLICITAR COBRO (ABIERTA → PENDIENTE_PAGO) ─────────────
            Action::make('solicitar_cobro')
                ->label('Solicitar Cobro')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Marcar como Pendiente de Pago')
                ->modalDescription('Esto pre-cerrará la cuenta. El cliente deberá pagar para finalizar.')
                ->modalSubmitActionLabel('Sí, solicitar cobro')
                ->visible(function (): bool {
                    $rec = $this->getRecord();

                    return $rec instanceof Cuenta && $rec->estado === EstadoCuenta::ABIERTA;
                })
                ->action(function (): void {
                    /** @var Cuenta $cuenta */
                    $cuenta = $this->getRecord();
                    $userId = auth()->id() !== null ? (int) auth()->id() : null;

                    app(SolicitarCobroCuenta::class)->ejecutar($cuenta, $userId);

                    Notification::make()
                        ->title('Cuenta marcada como Pendiente de Pago')
                        ->body("Cuenta #{$cuenta->numero_cuenta} — El cliente puede proceder al cobro.")
                        ->warning()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $cuenta]));
                }),

            // ─── 4. CERRAR Y GENERAR VENTA (saldo = 0) ─────────────────────
            Action::make('cerrar_y_generar_venta')
                ->label('Cerrar y Generar Venta')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Cerrar Cuenta y Emitir Venta')
                ->modalDescription('Se generará el documento de venta definitivo. Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, cerrar y generar')
                ->visible(function (): bool {
                    $rec = $this->getRecord();

                    return $rec instanceof Cuenta && ! $rec->tieneSaldoPendiente() && $rec->estado->puedeCerrarse();
                })
                ->action(function (): void {
                    /** @var Cuenta $cuenta */
                    $cuenta = $this->getRecord();
                    $userId = auth()->id() !== null ? (int) auth()->id() : null;

                    try {
                        $venta = app(CerrarCuentaYGenerarVenta::class)->ejecutar($cuenta, $userId);

                        Notification::make()
                            ->title('Cuenta cerrada — Venta generada')
                            ->body("Venta #{$venta->numero_venta} emitida correctamente.")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $cuenta]));
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('Error al cerrar la cuenta')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
