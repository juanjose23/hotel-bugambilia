<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Reservas\ReservaResource;
use App\Interactors\Reservas\Gestion\CrearReserva;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CreateReserva extends CreateRecord
{
    protected static string $resource = ReservaResource::class;

    protected CrearReserva $crearReserva;

    public function boot(CrearReserva $crearReserva): void
    {
        $this->crearReserva = $crearReserva;
    }

    public function mount(): void
    {
        parent::mount();

        $tipo = request()->query('tipo_reserva') ?? request()->query('tipo');
        $espacioId = request()->query('espacio_id');

        $initial = [];
        if (is_string($tipo) && $tipo !== '') {
            $initial['tipo_reserva'] = $tipo;
        }
        if (is_numeric($espacioId)) {
            $initial['espacio_id'] = (int) $espacioId;
        }

        if ($initial !== []) {
            $this->form->fill($initial);
        }
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): Model
    {
        $servicios = $data['servicios_adicionales'] ?? [];
        $espacios = $data['espacios_adicionales'] ?? [];
        $habitaciones = $data['habitaciones_adicionales'] ?? [];

        try {
            return $this->crearReserva->ejecutar(
                $data,
                is_array($servicios) ? $servicios : [],
                is_array($espacios) ? $espacios : [],
                is_array($habitaciones) ? $habitaciones : [],
            );
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

    /**
     * Se ocultan las acciones a pie de página general para que los botones de creación
     * se desplieguen únicamente en el último paso ("Pago y confirmación") del Wizard.
     *
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(function (): string {
                $tipoPago = $this->data['tipo_pago_reserva'] ?? null;

                return $tipoPago === 'sin_pago'
                    ? 'Crear reserva sin pago'
                    : 'Crear reserva y registrar pago';
            })
            ->icon('heroicon-o-credit-card')
            ->color('primary');
    }

    private function campoParaMensaje(string $mensaje): string
    {
        $mensaje = Str::lower($mensaje);

        return match (true) {
            Str::contains($mensaje, ['capacidad', 'mesa adicional', 'mesas sugeridas']) => 'espacios_adicionales',
            Str::contains($mensaje, ['mesa/espacio', 'espacio seleccionado', 'recurso seleccionado']) => 'espacio_id',
            Str::contains($mensaje, ['hora', 'horario']) => 'hora_reserva',
            Str::contains($mensaje, ['fecha', 'día', 'periodo']) => 'fecha_check_in',
            Str::contains($mensaje, ['platillo', 'preorden', 'pre-orden']) => 'items_preorden',
            Str::contains($mensaje, ['moneda']) => 'moneda_id',
            Str::contains($mensaje, ['monto recibido', 'total pendiente', 'exactamente el total']) => 'monto_pago_reserva',
            Str::contains($mensaje, ['pago', 'abono', 'forma de pago']) => 'tipo_pago_reserva',
            Str::contains($mensaje, ['cliente']) => 'nombre_cliente',
            default => 'tipo_reserva',
        };
    }
}
