<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Enums\Reservas\TipoPagoReserva;
use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Resources\Reservas\Schemas\Reserva\AccionesReserva;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
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

    protected RegistrarCobroInicialReserva $registrarCobroInicialReserva;

    public function boot(
        ActualizarReserva $actualizarReserva,
        RegistrarCobroInicialReserva $registrarCobroInicialReserva,
    ): void {
        $this->actualizarReserva = $actualizarReserva;
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
            $this->crearCuentaSiFalta($reserva);

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
        $data['acompanantes'] = $record->acompanantes;
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

        $preordenDatos = $record->ultimaEntradaBitacora('preorden');
        $preorden = $preordenDatos['items'] ?? [];
        if (is_array($preorden)) {
            $data['items_preorden'] = array_values(array_map(static fn (array $item): array => [
                'plato_id' => $item['plato_id'] ?? null,
                'cantidad' => $item['cantidad'] ?? 1,
                'precio_unitario' => $item['precio_unitario'] ?? 0,
                'observaciones' => $item['observaciones'] ?? null,
            ], array_filter($preorden, 'is_array')));
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
                ->label('Cobrar saldo'),

            Action::make('gestionarPagos')
                ->label('Ver folio y pagos')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->visible($cuenta !== null && CuentaResource::canView($cuenta))
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

    private function crearCuentaSiFalta(Reserva $reserva): void
    {
        if (app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $reserva->id) instanceof Cuenta) {
            return;
        }

        $this->registrarCobroInicialReserva->ejecutar(
            reserva: $reserva,
            tipoPago: TipoPagoReserva::SIN_PAGO,
            monedaId: $reserva->moneda_id,
            metodoPago: null,
            referencia: null,
            usuarioId: $this->usuarioId(),
        );
    }

    private function usuarioId(): ?int
    {
        $id = auth()->id();

        return is_numeric($id) ? (int) $id : null;
    }
}
