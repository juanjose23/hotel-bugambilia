<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\BusinessLogic\Reservas\Data\RealizarCheckOutData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Interactors\Reservas\Habitaciones\RealizarCheckOutHabitacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Support\MonedaHelper;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
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
            $reserva = Reserva::query()
                ->with(['habitacion', 'espacio', 'estancias.cuenta.moneda', 'estancias.habitacion'])
                ->find($this->record);

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
        }

        $this->form->fill([]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Wizard::make([
                    Step::make('Estancia')
                        ->icon('heroicon-o-home')
                        ->schema($this->buildPasoEstancia())
                        ->extraAttributes(['dusk' => 'checkout-resumen-estancia']),

                    Step::make('Consumos')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema($this->buildPasoConsumos())
                        ->extraAttributes(['dusk' => 'checkout-consumos']),

                    Step::make('Cuenta')
                        ->icon('heroicon-o-banknotes')
                        ->schema($this->buildPasoCuenta())
                        ->extraAttributes(['dusk' => 'checkout-saldo']),

                    Step::make('Habitación')
                        ->icon('heroicon-o-key')
                        ->schema($this->buildPasoHabitacion())
                        ->extraAttributes(['dusk' => 'checkout-habitacion']),

                    Step::make('Confirmar')
                        ->icon('heroicon-o-check-badge')
                        ->schema($this->buildPasoConfirmar())
                        ->extraAttributes(fn (): array => ['dusk' => $this->checkoutListo() ? 'checkout-listo' : 'checkout-bloqueado']),
                ])
                    ->submitAction(
                        Action::make('confirmar_checkout')
                            ->label('REALIZAR CHECK-OUT')
                            ->icon('heroicon-o-arrow-right-on-rectangle')
                            ->color('warning')
                            ->size(Size::Large)
                            ->submit('submit')
                            ->extraAttributes(['dusk' => 'confirmar-checkout'])
                    )
                    ->visible(fn (): bool => $this->reserva !== null),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with(['habitacion', 'espacio', 'estancia.cuenta', 'estancias.cuenta'])
                    ->whereIn('estado', [
                        EstadoReserva::CHECKED_IN,
                        EstadoReserva::PARCIALMENTE_CHECKED_IN,
                        EstadoReserva::PARCIALMENTE_CHECKED_OUT,
                    ])
            )
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_cliente')
                    ->label('Cliente / Huésped')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('fecha_check_in')
                    ->label('Check-In Entrada')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('estancia.cuenta.saldo')
                    ->label('Saldo Cuenta')
                    ->money(fn (Reserva $record): string => MonedaHelper::codigo($record->moneda ?? $record->estancia?->cuenta?->moneda))
                    ->placeholder('0.00'),
            ])
            ->defaultSort('fecha_check_in')
            ->recordActions([
                Action::make('iniciar_check_out')
                    ->label('Completar Check-Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->url(fn (Reserva $record): string => self::getUrl(['record' => $record->id])),
            ]);
    }

    /** @return array<string, int> */
    public function getMetricasCheckOut(): array
    {
        $hoy = now()->startOfDay();

        return [
            'checked_in_total' => Reserva::query()->whereIn('estado', [EstadoReserva::CHECKED_IN, EstadoReserva::PARCIALMENTE_CHECKED_IN])->count(),
            'salidas_hoy' => Reserva::query()->whereIn('estado', [EstadoReserva::CHECKED_IN, EstadoReserva::PARCIALMENTE_CHECKED_IN])->whereDate('fecha_check_out', '<=', $hoy)->count(),
            'finalizadas_hoy' => Estancia::query()->where('estado', EstadoEstancia::FINALIZADA)->whereDate('check_out_at', $hoy)->count(),
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
                ?? $this->reserva->estancia?->id;

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

    /** @return array<int, Component> */
    private function buildPasoEstancia(): array
    {
        return [
            Section::make('Resumen operativo')
                ->icon('heroicon-o-information-circle')
                ->columns(4)
                ->schema([
                    TextEntry::make('reserva_codigo')->label('Reserva')->state(fn (): string => $this->reserva->codigo_reserva ?? '-')->badge()->color('primary'),
                    TextEntry::make('estancia_codigo')->label('Estancia')->state(fn (): string => $this->codigoEstancia())->badge()->color('info'),
                    TextEntry::make('habitacion')->label('Habitación')->state(fn (): string => $this->habitacionResumen()),
                    TextEntry::make('titular')->label('Titular')->state(fn (): string => $this->reserva->nombre_cliente ?? '-'),
                    TextEntry::make('check_in_real')->label('Check-in real')->state(fn (): string => $this->estancia?->check_in_at?->format('d/m/Y H:i') ?? '-'),
                    TextEntry::make('check_out_programado')->label('Check-out programado')->state(fn (): string => $this->estancia?->fecha_salida_programada?->format('d/m/Y H:i') ?? $this->reserva?->fecha_check_out?->format('d/m/Y') ?? '-'),
                    TextEntry::make('ahora')->label('Fecha/hora actual')->state(fn (): string => now()->format('d/m/Y H:i')),
                    TextEntry::make('noches')->label('Noches')->state(fn (): string => (string) $this->nochesEstancia()),
                ]),

            Section::make('Semáforo de cierre')
                ->icon('heroicon-o-shield-check')
                ->columns(3)
                ->schema([
                    IconEntry::make('consumos_ok')->label('Consumos registrados')->state(fn (): bool => (bool) ($this->data['consumos_revisados'] ?? false))->boolean(),
                    IconEntry::make('cuenta_ok')->label('Cuenta revisada')->state(fn (): bool => $this->saldoPermitido())->boolean(),
                    IconEntry::make('saldo_ok')->label('Saldo permitido')->state(fn (): bool => $this->saldoPermitido())->boolean(),
                    IconEntry::make('danos_ok')->label('Daños / incidencias')->state(fn (): bool => ! (bool) ($this->data['danos_reportados'] ?? false))->boolean(),
                    IconEntry::make('llaves_ok')->label('Llaves / tarjetas')->state(fn (): bool => $this->llavesPermitidas())->boolean(),
                    IconEntry::make('housekeeping_ok')->label('Housekeeping')->state(fn (): bool => true)->boolean(),
                    TextEntry::make('estado_checkout')->label('Estado')->state(fn (): string => $this->checkoutListo() ? 'LISTO PARA CHECK-OUT' : 'CHECK-OUT BLOQUEADO')->badge()->color(fn (): string => $this->checkoutListo() ? 'success' : 'danger')->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, Component> */
    private function buildPasoConsumos(): array
    {
        return [
            Section::make('Consumos y servicios pendientes')
                ->icon('heroicon-o-clipboard-document-check')
                ->columns(2)
                ->schema([
                    TextEntry::make('cuenta_numero')->label('Cuenta')->state(fn (): string => $this->cuentaActiva()->numero_cuenta ?? 'Sin cuenta'),
                    TextEntry::make('total_cargos')->label('Total cargos')->state(fn (): string => MonedaHelper::formatear($this->montoCuenta('total'), $this->cuentaActiva()?->moneda)),
                    TextEntry::make('consumos_estado')->label('Validación de consumos')->state(fn (): string => (bool) ($this->data['consumos_revisados'] ?? false) ? 'Consumos revisados' : 'Pendiente de revisión final')->badge()->color(fn (): string => (bool) ($this->data['consumos_revisados'] ?? false) ? 'success' : 'warning'),
                    Toggle::make('consumos_revisados')
                        ->label('Consumos finales revisados')
                        ->helperText('Confirma que recepción revisó hospedaje, restaurante, minibar, lavandería y cargos finales reportados.')
                        ->live()
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, Component> */
    private function buildPasoCuenta(): array
    {
        return [
            Section::make('Cuenta y pagos')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    TextEntry::make('total_cuenta')->label('Total')->state(fn (): string => MonedaHelper::formatear($this->montoCuenta('total'), $this->cuentaActiva()?->moneda)),
                    TextEntry::make('pagado_cuenta')->label('Pagado')->state(fn (): string => MonedaHelper::formatear($this->montoCuenta('total_pagado'), $this->cuentaActiva()?->moneda)),
                    TextEntry::make('saldo_cuenta')->label('Saldo')->state(fn (): string => MonedaHelper::formatear($this->saldoCuenta(), $this->cuentaActiva()?->moneda))->badge()->color(fn (): string => $this->saldoPermitido() ? 'success' : 'danger'),
                    Toggle::make('credito_autorizado')
                        ->label('Saldo pendiente autorizado por crédito')
                        ->helperText('Usar solo cuando exista autorización real. Si no hay autorización, primero registre el pago.')
                        ->live()
                        ->visible(fn (): bool => $this->saldoCuenta() > 0)
                        ->columnSpanFull(),
                    Textarea::make('motivo_credito')
                        ->label('Motivo de autorización')
                        ->maxLength(500)
                        ->rows(2)
                        ->visible(fn (): bool => $this->saldoCuenta() > 0 && (bool) ($this->data['credito_autorizado'] ?? false))
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, Component> */
    private function buildPasoHabitacion(): array
    {
        return [
            Section::make('Estado final de habitación')
                ->icon('heroicon-o-key')
                ->columns(2)
                ->schema([
                    TextEntry::make('estado_habitacion')->label('Estado actual')->state(fn (): string => $this->estancia?->habitacion?->estado?->getLabel() ?? '-')->badge()->color(fn (): string => $this->estancia?->habitacion?->estado?->getColor() ?? 'gray'),
                    TextEntry::make('destino_habitacion')->label('Destino posterior')->state('Sucia / pendiente de limpieza')->badge()->color('warning'),
                    TextInput::make('llaves_devueltas')
                        ->label('Llaves / tarjetas devueltas')
                        ->integer()
                        ->minValue(0)
                        ->default(fn (): int => $this->estancia->cantidad_llaves ?? 1)
                        ->live()
                        ->required(),
                    Toggle::make('autorizar_llaves_pendientes')
                        ->label('Autorizar llaves pendientes')
                        ->helperText('Debe usarse solo con autorización de recepción.')
                        ->live(),
                    Toggle::make('habitacion_inspeccionada')
                        ->label('Habitación inspeccionada')
                        ->live(),
                    Toggle::make('danos_reportados')
                        ->label('Daños o incidencias reportadas')
                        ->live(),
                    Textarea::make('observaciones')
                        ->label('Observaciones de salida')
                        ->placeholder('Inspección, llaves, incidencias, notas de recepción...')
                        ->maxLength(2000)
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, Component> */
    private function buildPasoConfirmar(): array
    {
        return [
            Section::make('Resumen final')
                ->icon('heroicon-o-check-badge')
                ->columns(3)
                ->schema([
                    TextEntry::make('final_reserva')->label('Reserva')->state(fn (): string => $this->reserva->codigo_reserva ?? '-'),
                    TextEntry::make('final_estancia')->label('Estancia')->state(fn (): string => $this->codigoEstancia()),
                    TextEntry::make('final_habitacion')->label('Habitación')->state(fn (): string => $this->habitacionResumen()),
                    TextEntry::make('final_total')->label('Total')->state(fn (): string => MonedaHelper::formatear($this->montoCuenta('total'), $this->cuentaActiva()?->moneda)),
                    TextEntry::make('final_pagado')->label('Pagado')->state(fn (): string => MonedaHelper::formatear($this->montoCuenta('total_pagado'), $this->cuentaActiva()?->moneda)),
                    TextEntry::make('final_saldo')->label('Saldo')->state(fn (): string => MonedaHelper::formatear($this->saldoCuenta(), $this->cuentaActiva()?->moneda))->badge()->color(fn (): string => $this->saldoPermitido() ? 'success' : 'danger'),
                    IconEntry::make('final_consumos')->label('Consumos revisados')->state(fn (): bool => (bool) ($this->data['consumos_revisados'] ?? false))->boolean(),
                    IconEntry::make('final_saldo_ok')->label('Saldo permitido')->state(fn (): bool => $this->saldoPermitido())->boolean(),
                    IconEntry::make('final_llaves')->label('Llaves validadas')->state(fn (): bool => $this->llavesPermitidas())->boolean(),
                    TextEntry::make('final_estado')->label('Resultado')->state(fn (): string => $this->checkoutListo() ? 'Puede completar el check-out' : $this->motivoBloqueo())->badge()->color(fn (): string => $this->checkoutListo() ? 'success' : 'danger')->columnSpanFull(),
                ]),
        ];
    }

    private function resolverEstanciaActiva(): ?Estancia
    {
        return $this->reserva?->estancias
            ->first(fn (Estancia $estancia): bool => in_array($estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true));
    }

    private function cuentaActiva(): ?Cuenta
    {
        return $this->estancia?->cuenta;
    }

    private function montoCuenta(string $campo): float
    {
        $cuenta = $this->cuentaActiva();

        return $cuenta !== null && is_numeric($cuenta->{$campo} ?? null) ? (float) $cuenta->{$campo} : 0.0;
    }

    private function saldoCuenta(): float
    {
        return $this->montoCuenta('saldo');
    }

    private function saldoPermitido(): bool
    {
        return $this->saldoCuenta() <= 0.0 || (bool) ($this->data['credito_autorizado'] ?? false);
    }

    private function llavesPermitidas(): bool
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
        $habitacion = $this->estancia->habitacion ?? $this->reserva?->habitacion;

        if ($habitacion === null) {
            return '-';
        }

        return trim(($habitacion->numero ?? '').' - '.($habitacion->nombre ?? '')) ?: '-';
    }

    private function nochesEstancia(): int
    {
        $entrada = $this->estancia->fecha_entrada_programada ?? $this->estancia->check_in_at ?? $this->reserva?->fecha_check_in;
        $salida = $this->estancia->fecha_salida_programada ?? $this->reserva?->fecha_check_out;

        if ($entrada === null || $salida === null) {
            return 0;
        }

        return max(1, (int) $entrada->diffInDays($salida));
    }
}
