<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\TipoPagoReserva;
use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Resources\Reservas\Schemas\Reserva\AccionesReserva;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Interactors\Cuentas\Cobros\ProcesarCobroCuenta;
use App\Interactors\Reservas\Gestion\ActualizarReserva;
use App\Interactors\Reservas\Operaciones\RegistrarCobroInicialReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Cuentas\ObtenerCuentaReservaQuery;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;

/**
 * @property Reserva $record
 */
class EditReserva extends EditRecord
{
    protected static string $resource = ReservaResource::class;

    protected ActualizarReserva $actualizarReserva;

    protected ProcesarCobroCuenta $procesarCobroCuenta;

    protected RegistrarCobroInicialReserva $registrarCobroInicialReserva;

    public function boot(
        ActualizarReserva $actualizarReserva,
        ProcesarCobroCuenta $procesarCobroCuenta,
        RegistrarCobroInicialReserva $registrarCobroInicialReserva,
    ): void {
        $this->actualizarReserva = $actualizarReserva;
        $this->procesarCobroCuenta = $procesarCobroCuenta;
        $this->registrarCobroInicialReserva = $registrarCobroInicialReserva;
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof Reserva) {
            throw new LogicException('El registro recibido no es una reserva.');
        }

        try {
            $reserva = $this->actualizarReserva->ejecutar($record, $data);
            $cobroRegistradoAlCrearCuenta = $this->crearCuentaSiFalta($reserva, $data);
            if (! $cobroRegistradoAlCrearCuenta) {
                $this->registrarPagoSiAplica($reserva, $data);
            }

            return $reserva->refresh();
        } catch (DomainException|InvalidArgumentException $exception) {
            $mensaje = trim($exception->getMessage());

            Notification::make()
                ->title('Revise los datos de la reserva')
                ->body($mensaje)
                ->warning()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.'.$this->campoParaMensaje($mensaje) => $mensaje,
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;
        $detalles = $record
            ->load(['detalles.reservable.servicio', 'detalles.reservable.espacio', 'detalles.reservable.habitacion'])
            ->detalles
            ->whereNotNull('parent_id');

        $data['servicios_adicionales'] = $detalles
            ->filter(fn ($detalle) => $detalle->reservable?->servicio instanceof Servicio)
            ->map(fn ($detalle): array => [
                'servicio_id' => $detalle->reservable?->servicio?->id,
                'cantidad' => $detalle->cantidad,
            ])
            ->values()
            ->all();

        $data['espacios_adicionales'] = $detalles
            ->filter(fn ($detalle) => $detalle->reservable?->espacio instanceof Espacio)
            ->map(fn ($detalle): array => [
                'espacio_id' => $detalle->reservable?->espacio?->id,
                'cantidad' => 1,
            ])
            ->values()
            ->all();

        $data['habitaciones_adicionales'] = $detalles
            ->filter(fn ($detalle) => $detalle->reservable?->habitacion instanceof Habitacion)
            ->map(fn ($detalle): array => [
                'habitacion_id' => $detalle->reservable?->habitacion?->id,
                'cantidad' => 1,
            ])
            ->values()
            ->all();

        $principal = $record->detalles->whereNull('parent_id')->first();
        if ($principal !== null && $principal->fecha_fin !== null) {
            $data['duracion_horas'] = max(1, (int) ceil(($principal->fecha_fin->getTimestamp() - $principal->fecha_inicio->getTimestamp()) / 3600));
        }

        $meta = is_array($record->meta_datos) ? $record->meta_datos : [];
        $preorden = $meta['platos_preordenados'] ?? [];
        if (is_array($preorden)) {
            $data['items_preorden'] = array_values(array_map(static fn (array $item): array => [
                'plato_id' => $item['plato_id'] ?? null,
                'cantidad' => $item['cantidad'] ?? 1,
                'precio_unitario' => $item['precio_unitario'] ?? 0,
                'observaciones' => $item['observaciones'] ?? null,
            ], array_filter($preorden, 'is_array')));
        }

        if ((float) $record->saldo > 0) {
            $data['tipo_pago_reserva'] = 'abono_50';
            $data['moneda_id'] = $record->moneda_id;
            $data['monto_pago_reserva'] = $this->faltanteParaCubrir50($record);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $cuenta = app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $this->record->id);

        return [
            ...AccionesReserva::make(),

            CobrarCuentaAction::makeFromResolver(
                resolverCuenta: fn (): ?Cuenta => app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $this->record->id),
                onSuccess: function (Cuenta $cuenta): void {
                    $this->record = $this->record->refresh();
                    $this->fillForm();
                },
            )
                ->name('cobrarReserva')
                ->label('Cobrar reserva'),

            Action::make('gestionarPagos')
                ->label((float) $this->record->saldo > 0 ? 'Registrar abono o pago' : 'Ver pagos')
                ->icon('heroicon-o-banknotes')
                ->color((float) $this->record->saldo > 0 ? 'warning' : 'success')
                ->visible($cuenta !== null)
                ->url($cuenta !== null ? CuentaResource::getUrl('view', ['record' => $cuenta]) : null),
        ];
    }

    private function campoParaMensaje(string $mensaje): string
    {
        $mensaje = Str::lower($mensaje);

        return match (true) {
            Str::contains($mensaje, ['capacidad', 'mesa adicional', 'mesas sugeridas']) => 'espacios_adicionales',
            Str::contains($mensaje, ['mesa/espacio', 'espacio seleccionado', 'recurso principal', 'recurso seleccionado']) => 'espacio_id',
            Str::contains($mensaje, ['habitación']) => 'habitacion_id',
            Str::contains($mensaje, ['servicio']) => 'servicio_id',
            Str::contains($mensaje, ['hora', 'horario']) => 'hora_reserva',
            Str::contains($mensaje, ['fecha', 'día', 'periodo']) => 'fecha_check_in',
            Str::contains($mensaje, ['platillo', 'preorden', 'pre-orden']) => 'items_preorden',
            Str::contains($mensaje, ['cliente']) => 'nombre_cliente',
            default => 'tipo_reserva',
        };
    }

    /** @param array<string, mixed> $data */
    private function registrarPagoSiAplica(Reserva $reserva, array $data): void
    {
        $tipoPago = $data['tipo_pago_reserva'] ?? null;
        $monto = $data['monto_pago_reserva'] ?? null;

        if (! is_string($tipoPago) || $tipoPago === 'sin_pago') {
            return;
        }

        $montoCobrar = is_numeric($monto) && (float) $monto > 0
            ? (float) $monto
            : $this->montoSugeridoParaPago($reserva, $tipoPago);

        if ($montoCobrar <= 0) {
            return;
        }

        $cuenta = app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $reserva->id);

        if (! $cuenta instanceof Cuenta) {
            throw new DomainException('La reserva no tiene una cuenta abierta para registrar el cobro.');
        }

        $this->procesarCobroCuenta->ejecutar($cuenta, $this->usuarioId(), [
            'forma_pago' => $data['metodo_pago_reserva'] ?? 1,
            'moneda_pago_id' => $data['moneda_id'] ?? $cuenta->moneda_id,
            'monto' => $montoCobrar,
            'referencia_transaccion' => $data['referencia_pago_reserva'] ?? null,
            'observaciones' => "Abono registrado desde edición de reserva {$reserva->codigo_reserva}",
            'tipo_comprobante' => 'voucher',
        ]);
    }

    /** @param array<string, mixed> $data */
    private function crearCuentaSiFalta(Reserva $reserva, array $data): bool
    {
        if (app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $reserva->id) instanceof Cuenta) {
            return false;
        }

        $tipoPagoValor = $data['tipo_pago_reserva'] ?? null;
        $tipoPago = is_string($tipoPagoValor) && TipoPagoReserva::tryFrom($tipoPagoValor) !== null
            ? TipoPagoReserva::from($tipoPagoValor)
            : TipoPagoReserva::SIN_PAGO;

        $metodoValor = $data['metodo_pago_reserva'] ?? null;
        $metodoPago = is_numeric($metodoValor) ? MetodoPago::tryFrom((int) $metodoValor) : null;

        $this->registrarCobroInicialReserva->ejecutar(
            reserva: $reserva,
            tipoPago: $tipoPago,
            monedaId: is_numeric($data['moneda_id'] ?? null) ? (int) $data['moneda_id'] : null,
            metodoPago: $metodoPago,
            referencia: is_string($data['referencia_pago_reserva'] ?? null) ? $data['referencia_pago_reserva'] : null,
            usuarioId: $this->usuarioId(),
            montoSolicitado: $this->montoSolicitadoParaCuentaNueva($reserva, $tipoPago, $data),
            cargosFacturacionIds: [],
        );

        return $tipoPago !== TipoPagoReserva::SIN_PAGO;
    }

    /** @param array<string, mixed> $data */
    private function montoSolicitadoParaCuentaNueva(Reserva $reserva, TipoPagoReserva $tipoPago, array $data): ?float
    {
        $monto = $data['monto_pago_reserva'] ?? null;

        if (is_numeric($monto) && (float) $monto > 0) {
            return (float) $monto;
        }

        return match ($tipoPago) {
            TipoPagoReserva::SIN_PAGO => null,
            TipoPagoReserva::ABONO_50 => $this->faltanteParaCubrir50($reserva),
            TipoPagoReserva::PAGO_COMPLETO => (float) $reserva->saldo,
        };
    }

    private function montoSugeridoParaPago(Reserva $reserva, string $tipoPago): float
    {
        $tipo = TipoPagoReserva::tryFrom($tipoPago);

        return match ($tipo) {
            TipoPagoReserva::ABONO_50 => $this->faltanteParaCubrir50($reserva),
            TipoPagoReserva::PAGO_COMPLETO => (float) $reserva->saldo,
            default => 0.0,
        };
    }

    private function faltanteParaCubrir50(Reserva $reserva): float
    {
        return round(max(0.0, ((float) $reserva->total * 0.5) - (float) $reserva->total_pagado), 2);
    }

    private function usuarioId(): ?int
    {
        $id = auth()->id();

        return is_numeric($id) ? (int) $id : null;
    }
}
