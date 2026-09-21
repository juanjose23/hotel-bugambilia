<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\BusinessLogic\Reservas\Data\RealizarCheckOutData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Filament\Pages\Reservas\Actions\CheckOutPagarStripeAction;
use App\Filament\Pages\Reservas\Actions\CheckOutRegistrarPagoAction;
use App\Filament\Pages\Reservas\Actions\CheckOutRegistrarPagosMultiplesAction;
use App\Filament\Pages\Reservas\Schemas\CheckOutConsumosSchema;
use App\Filament\Pages\Reservas\Schemas\CheckOutCuentaSchema;
use App\Filament\Pages\Reservas\Schemas\CheckOutHabitacionSchema;
use App\Filament\Pages\Reservas\Tables\CheckOutEstanciasTable;
use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Interactors\Reservas\Habitaciones\RealizarCheckOutHabitacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Url;
use Throwable;
use UnitEnum;

/**
 * @property Schema $form
 */
final class CheckOutPage extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';

    protected static string|UnitEnum|null $navigationGroup = 'Recepción & Reservas';

    protected static ?string $navigationLabel = 'Check-Out';

    protected static ?string $title = 'Registro de Check-Out';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.resources.reservas.check-out-page';

    #[Url]
    public ?int $record = null;

    public ?Reserva $reserva = null;

    public ?Estancia $estancia = null;

    /** @var array<string, mixed> */
    public array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            CheckOutRegistrarPagoAction::make($this),
            CheckOutRegistrarPagosMultiplesAction::make($this),
            CheckOutPagarStripeAction::make($this),
            Action::make('ver_cuenta')
                ->label('Ver Folio / Cuenta')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('info')
                ->url(fn (): ?string => $this->cuentaActiva() ? CuentaResource::getUrl('view', ['record' => $this->cuentaActiva()->id]) : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->cuentaActiva() !== null),
            Action::make('volver_lista')
                ->label('Volver a Lista de Check-Out')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(self::getUrl())
                ->visible(fn (): bool => $this->record !== null || $this->reserva !== null),
        ];
    }

    public function mount(): void
    {
        if ($this->record !== null) {
            $this->inicializarRegistro($this->record);

            return;
        }

        $this->form->fill([]);
    }

    private function inicializarRegistro(int $recordId): void
    {
        $reserva = Reserva::query()
            ->with([
                'habitacion',
                'espacio',
                'moneda',
                'estancia.cuenta.moneda',
                'estancia.cuenta.pagos',
                'estancia.cuenta.detalles',
                'estancia.habitacion',
                'estancias.cuenta.moneda',
                'estancias.cuenta.pagos',
                'estancias.cuenta.detalles',
                'estancias.habitacion',
            ])
            ->find($recordId);

        if ($reserva !== null && in_array($reserva->estado, [
            EstadoReserva::CHECKED_IN,
            EstadoReserva::PARCIALMENTE_CHECKED_IN,
            EstadoReserva::PARCIALMENTE_CHECKED_OUT,
        ], true)) {
            $this->reserva = $reserva;
            $this->estancia = $this->resolverEstanciaActiva();

            $this->form->fill([
                'reserva_id' => $reserva->id,
                'estancia_id' => $this->estancia?->id,
                'llaves_devueltas' => $this->estancia->cantidad_llaves ?? 1,
                'autorizar_llaves_pendientes' => false,
                'credito_autorizado' => false,
                'consumos_revisados' => false,
                'habitacion_inspeccionada' => false,
                'danos_reportados' => false,
            ]);

            return;
        }

        // Si record es ID directo de Estancia
        $estancia = Estancia::query()
            ->with([
                'cuenta.moneda',
                'cuenta.pagos',
                'cuenta.detalles',
                'habitacion',
                'reserva.moneda',
                'reserva.habitacion',
            ])
            ->find($recordId);

        if ($estancia !== null && in_array($estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)) {
            $this->estancia = $estancia;
            $this->reserva = $estancia->reserva;

            $this->form->fill([
                'reserva_id' => $this->reserva?->id,
                'estancia_id' => $estancia->id,
                'llaves_devueltas' => $estancia->cantidad_llaves ?? 1,
                'autorizar_llaves_pendientes' => false,
                'credito_autorizado' => false,
                'consumos_revisados' => false,
                'habitacion_inspeccionada' => false,
                'danos_reportados' => false,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                ...CheckOutConsumosSchema::make($this),
                ...CheckOutCuentaSchema::make($this),
                ...CheckOutHabitacionSchema::make($this),
            ]);
    }

    public function table(Table $table): Table
    {
        return CheckOutEstanciasTable::configure($table, $this);
    }

    /** @return array<string, int|float|string> */
    public function getMetricasCheckOut(): array
    {
        $hoy = now()->startOfDay();

        $saldoPendienteTotal = Cuenta::query()
            ->whereHas('estancia', fn ($q) => $q->whereIn('estado', [EstadoEstancia::ACTIVA->value, EstadoEstancia::EXTENDIDA->value]))
            ->where('saldo', '>', 0)
            ->sum('saldo');

        return [
            'checked_in_total' => Reserva::query()->whereIn('estado', [EstadoReserva::CHECKED_IN, EstadoReserva::PARCIALMENTE_CHECKED_IN])->count(),
            'salidas_hoy' => Reserva::query()->whereIn('estado', [EstadoReserva::CHECKED_IN, EstadoReserva::PARCIALMENTE_CHECKED_IN])->whereDate('fecha_check_out', '<=', $hoy)->count(),
            'finalizadas_hoy' => Estancia::query()->where('estado', EstadoEstancia::FINALIZADA)->whereDate('check_out_at', $hoy)->count(),
            'saldo_pendiente_total' => (float) $saldoPendienteTotal,
        ];
    }

    public function submit(RealizarCheckOutHabitacion $interactor): void
    {
        if ($this->reserva === null) {
            Notification::make()
                ->title('Seleccione una estancia')
                ->warning()
                ->send();

            return;
        }

        try {
            $formData = $this->form->getState();

            /** @var int|null $userId */
            $userId = auth()->id();

            $estanciaActiva = $this->resolverEstanciaActiva();

            $estanciaId = $formData['estancia_id']
                ?? $estanciaActiva->id
                ?? $this->estancia->id
                ?? $this->reserva->estancia->id
                ?? null;

            if (! is_numeric($estanciaId) || (int) $estanciaId <= 0) {
                throw new DomainException('No se encontró una estancia activa válida para realizar Check-Out.');
            }

            if (! (bool) ($formData['consumos_revisados'] ?? false)) {
                throw new DomainException('No se puede realizar Check-Out sin confirmar la revisión final de consumos.');
            }

            $dto = new RealizarCheckOutData(
                estanciaId: (int) $estanciaId,
                observaciones: is_string($formData['observaciones'] ?? null) ? $formData['observaciones'] : null,
                autorizarSaldoPendiente: (bool) ($formData['credito_autorizado'] ?? false),
                llavesDevueltas: is_numeric($formData['llaves_devueltas'] ?? null) ? (int) $formData['llaves_devueltas'] : 1,
                autorizarLlavesPendientes: (bool) ($formData['autorizar_llaves_pendientes'] ?? false),
                usuarioId: $userId,
            );

            $interactor->ejecutar($dto);

            Notification::make()
                ->title('Check-Out registrado exitosamente')
                ->success()
                ->send();

            $this->redirect(ReservaResource::getUrl('view', ['record' => $this->reserva->id]));
        } catch (DomainException $e) {
            Notification::make()
                ->title('Error en el Check-Out')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un error al registrar el check-out: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function volverALista(): void
    {
        $this->record = null;
        $this->reserva = null;
        $this->estancia = null;
        $this->form->fill([]);
    }

    public function resolverEstanciaActiva(): ?Estancia
    {
        if ($this->estancia !== null && $this->estancia->exists && in_array($this->estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)) {
            return $this->estancia->loadMissing([
                'cuenta.detalles',
                'cuenta.pagos',
                'cuenta.moneda',
                'habitacion',
            ]);
        }

        if ($this->reserva !== null) {
            $estancia = Estancia::query()
                ->with([
                    'cuenta.detalles',
                    'cuenta.pagos',
                    'cuenta.moneda',
                    'habitacion',
                ])
                ->where('reserva_id', $this->reserva->id)
                ->whereIn('estado', [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA])
                ->first();

            if ($estancia !== null) {
                $this->estancia = $estancia;

                return $estancia;
            }
        }

        if ($this->record !== null) {
            $estancia = Estancia::query()
                ->with([
                    'cuenta.detalles',
                    'cuenta.pagos',
                    'cuenta.moneda',
                    'habitacion',
                    'reserva',
                ])
                ->find($this->record);

            if ($estancia !== null && in_array($estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)) {
                $this->estancia = $estancia;
                if ($this->reserva === null && $estancia->reserva !== null) {
                    $this->reserva = $estancia->reserva;
                }

                return $estancia;
            }
        }

        return null;
    }

    public function refrescarEstancia(): void
    {
        $this->estancia = $this->resolverEstanciaActiva();
        $this->reserva?->refresh();
    }

    public function cuentaActiva(): ?Cuenta
    {
        return $this->resolverEstanciaActiva()?->cuenta;
    }

    public function montoCuenta(string $campo): float
    {
        $cuenta = $this->cuentaActiva();

        return $cuenta !== null && is_numeric($cuenta->{$campo} ?? null) ? (float) $cuenta->{$campo} : 0.0;
    }

    public function saldoCuenta(): float
    {
        return $this->montoCuenta('saldo');
    }

    public function saldoPermitido(): bool
    {
        return $this->saldoCuenta() <= 0.0 || (bool) ($this->data['credito_autorizado'] ?? false);
    }

    public function llavesPermitidas(): bool
    {
        $entregadas = (int) ($this->estancia->cantidad_llaves ?? 1);
        $devueltas = is_numeric($this->data['llaves_devueltas'] ?? null) ? (int) $this->data['llaves_devueltas'] : $entregadas;

        return $devueltas >= $entregadas || (bool) ($this->data['autorizar_llaves_pendientes'] ?? false);
    }

    public function checkoutListo(): bool
    {
        return $this->estancia !== null
            && in_array($this->estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)
            && (bool) ($this->data['consumos_revisados'] ?? false)
            && $this->saldoPermitido()
            && $this->llavesPermitidas();
    }

    public function motivoBloqueo(): string
    {
        if ($this->estancia === null) {
            return 'No hay estancia activa para cerrar.';
        }

        if (! (bool) ($this->data['consumos_revisados'] ?? false)) {
            return 'Falta revisar consumos finales.';
        }

        if (! $this->saldoPermitido()) {
            return 'Existe saldo pendiente sin autorización.';
        }

        if (! $this->llavesPermitidas()) {
            return 'Faltan llaves o tarjetas por devolver.';
        }

        return 'Validaciones pendientes.';
    }

    public function codigoEstancia(): string
    {
        return $this->estancia !== null ? 'EST-'.str_pad((string) $this->estancia->id, 6, '0', STR_PAD_LEFT) : '-';
    }

    public function habitacionResumen(): string
    {
        $habitacion = $this->estancia->habitacion ?? $this->reserva->habitacion ?? null;

        if ($habitacion === null) {
            return '-';
        }

        return trim(($habitacion->numero ?? '').' - '.($habitacion->nombre ?? '')) ?: '-';
    }

    public function nochesEstancia(): int
    {
        $entrada = $this->estancia->fecha_entrada_programada ?? $this->estancia->check_in_at ?? $this->reserva->fecha_check_in ?? null;
        $salida = $this->estancia->fecha_salida_programada ?? $this->reserva->fecha_check_out ?? null;

        if ($entrada === null || $salida === null) {
            return 0;
        }

        return max(1, (int) $entrada->diffInDays($salida));
    }
}
