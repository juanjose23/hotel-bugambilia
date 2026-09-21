<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\BusinessLogic\CheckIn\ObtenerReadinessCheckIn;
use App\BusinessLogic\Personas\PersonaNatural\ValidCedulaNicaragua;
use App\BusinessLogic\Reservas\Data\RealizarCheckInData;
use App\BusinessLogic\Reservas\Data\RegistrarHuespedData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Interactors\Reservas\Habitaciones\RealizarCheckInHabitacion;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Support\MonedaHelper;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
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
final class CheckInPage extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|UnitEnum|null $navigationGroup = 'Recepción & Reservas';

    protected static ?string $navigationLabel = 'Check-In';

    protected static ?string $title = 'Recepción — Asistente de Check-In';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.resources.reservas.check-in-page';

    // ── URL ──────────────────────────────────────────────────────────────────
    #[Url]
    public ?int $record = null;

    // ── State ────────────────────────────────────────────────────────────────
    public ?Reserva $reserva = null;

    public ?int $reservaDetalleId = null;

    /** @var array<string, mixed> */
    public array $data = [];

    // ── Header Actions ───────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ver_reserva')
                ->label('Ver Ficha de Reserva')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (): ?string => $this->reserva !== null ? ReservaResource::getUrl('view', ['record' => $this->reserva->id]) : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->reserva !== null),

            Action::make('volver')
                ->label('Volver a la Lista')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(self::getUrl())
                ->visible(fn (): bool => $this->reserva !== null),
        ];
    }

    public function volverALista(): void
    {
        $this->record = null;
        $this->reserva = null;
        $this->reservaDetalleId = null;
        $this->form->fill(['cantidad_llaves' => 1]);
    }

    // ── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if ($this->record !== null) {
            $reserva = Reserva::query()
                ->with(['moneda', 'detalles.reservable', 'huespedes', 'habitacion', 'espacio'])
                ->find($this->record);

            if ($reserva !== null && in_array(
                $reserva->estado,
                [EstadoReserva::CONFIRMADA, EstadoReserva::PARCIALMENTE_CHECKED_IN],
                true
            )) {
                $this->reserva = $reserva;

                // Preseleccionar si hay un solo detalle
                $detalles = $reserva->detalles
                    ->whereNull('parent_id')
                    ->whereIn('estado', [EstadoReservaDetalle::CONFIRMADO, EstadoReservaDetalle::PENDIENTE]);

                if ($detalles->count() === 1) {
                    $this->reservaDetalleId = $detalles->first()?->id;
                }

                $this->form->fill([
                    'cantidad_llaves' => 1,
                    'abrir_cuenta' => (bool) ($reserva->solicita_cuenta ?? false),
                    'detalle_id' => $this->reservaDetalleId,
                ]);

                return;
            }
        }

        $this->form->fill(['cantidad_llaves' => 1]);
    }

    // ── Form ─────────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Section::make('1. Asignación de Habitación')
                    ->icon('heroicon-o-home')
                    ->description('Seleccione la habitación de la reserva para el ingreso del huésped.')
                    ->schema([
                        Select::make('detalle_id')
                            ->label('Habitación a Ocupar')
                            ->options(function (): array {
                                if ($this->reserva === null) {
                                    return [];
                                }

                                return $this->reserva->detalles
                                    ->whereNull('parent_id')
                                    ->whereIn('estado', [
                                        EstadoReservaDetalle::CONFIRMADO,
                                        EstadoReservaDetalle::PENDIENTE,
                                    ])
                                    ->mapWithKeys(function (ReservaDetalle $d): array {
                                        $habitacion = $d->reservable !== null
                                            ? Habitacion::where('reservable_id', $d->reservable->id)->first()
                                            : null;

                                        $label = $habitacion !== null
                                            ? "Hab. {$habitacion->numero} — {$habitacion->nombre}"
                                            : "Detalle #{$d->id}";

                                        return [$d->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?int $state): void {
                                $this->reservaDetalleId = $state;
                            })
                            ->helperText('Seleccione la habitación para verificar su estado de readiness y entrega.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('2. Registro de Acompañantes')
                    ->icon('heroicon-o-users')
                    ->description('Registre a los ocupantes adicionales. El titular ya se encuentra registrado.')
                    ->schema([
                        Repeater::make('huespedes_nuevos')
                            ->hiddenLabel()
                            ->defaultItems(0)
                            ->addActionLabel('Agregar acompañante')
                            ->columns(4)
                            ->reorderable(false)
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre Completo')
                                    ->required()
                                    ->maxLength(150),

                                Select::make('tipo_identificacion')
                                    ->label('Tipo Documento')
                                    ->options([
                                        'cedula' => 'Cédula',
                                        'pasaporte' => 'Pasaporte',
                                        'residencia' => 'Residencia',
                                    ])
                                    ->default('cedula')
                                    ->required()
                                    ->live()
                                    ->native(false),

                                TextInput::make('identificacion')
                                    ->label('Número de Documento')
                                    ->required()
                                    ->rules([
                                        fn (Get $get) => $get('tipo_identificacion') === 'cedula' ? new ValidCedulaNicaragua : null,
                                    ])
                                    ->maxLength(100),

                                Select::make('tipo')
                                    ->label('Categoría')
                                    ->options([
                                        'adulto' => 'Adulto',
                                        'nino' => 'Niño',
                                        'infante' => 'Infante',
                                    ])
                                    ->default('adulto')
                                    ->required()
                                    ->native(false),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('3. Llaves, Folio de Consumos & Observaciones')
                    ->icon('heroicon-o-key')
                    ->description('Configuración de llaves físicas o tarjetas RFID, apertura de cuenta de consumos y notas.')
                    ->schema([
                        TextInput::make('cantidad_llaves')
                            ->label('Llaves entregadas')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(1)
                            ->required()
                            ->suffix('llave(s)'),

                        Toggle::make('abrir_cuenta')
                            ->label('Abrir cuenta de consumo')
                            ->helperText('Permite cargar consumos de restaurante, bar, lavandería y spa a la habitación.')
                            ->live()
                            ->onColor('success')
                            ->inline(false),

                        TextInput::make('limite_cuenta')
                            ->label('Límite autorizado de la cuenta')
                            ->numeric()
                            ->prefix(fn (): string => MonedaHelper::simbolo($this->reserva?->moneda))
                            ->minValue(0)
                            ->helperText('Monto máximo permitido para cargos.')
                            ->visible(fn (callable $get): bool => (bool) $get('abrir_cuenta')),

                        Textarea::make('observaciones')
                            ->label('Observaciones de entrada')
                            ->placeholder('Solicitudes especiales, notas de recepción, preferencias...')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    // ── Table (pantalla de lista) ─────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with(['habitacion', 'espacio', 'detalles.reservable', 'moneda'])
                    ->whereIn('estado', [EstadoReserva::CONFIRMADA, EstadoReserva::PARCIALMENTE_CHECKED_IN])
            )
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_cliente')
                    ->label('Huésped Titular')
                    ->icon('heroicon-o-user')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación Asignada')
                    ->icon('heroicon-o-home')
                    ->formatStateUsing(function (Reserva $record): string {
                        $hab = $record->habitacion;
                        if ($hab !== null) {
                            return "Hab. {$hab->numero} — {$hab->nombre}";
                        }

                        $esp = $record->espacio;
                        if ($esp !== null) {
                            return "Espacio — {$esp->nombre}";
                        }

                        return 'Sin asignar';
                    })
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('fecha_check_in')
                    ->label('Fecha Entrada')
                    ->date('d/m/Y')
                    ->description(fn (Reserva $record): string => $record->fecha_check_in?->isToday() ? '¡Llega Hoy!' : ($record->fecha_check_in?->isTomorrow() ? 'Llega Mañana' : ''))
                    ->sortable(),

                TextColumn::make('fecha_check_out')
                    ->label('Fecha Salida')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('adultos')
                    ->label('Ocupantes')
                    ->formatStateUsing(fn (Reserva $record): string => "{$record->adultos} ad.".($record->ninos > 0 ? " + {$record->ninos} niñ." : ''))
                    ->alignCenter(),

                TextColumn::make('saldo')
                    ->label('Saldo Reserva')
                    ->money(fn (Reserva $record): string => MonedaHelper::codigo($record->moneda))
                    ->badge()
                    ->color(fn (Reserva $record): string => (float) $record->saldo > 0 ? 'warning' : 'success'),

                EstadoBadgeColumn::make(EstadoReserva::class),
            ])
            ->defaultSort('fecha_check_in')
            ->recordActions([
                Action::make('iniciar')
                    ->label('Hacer Check-In')
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->url(fn (Reserva $record): string => self::getUrl(['record' => $record->id])),

                Action::make('ver_reserva_modal')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Reserva $record): string => ReservaResource::getUrl('view', ['record' => $record->id]))
                    ->openUrlInNewTab(),
            ]);
    }

    // ── Readiness data ────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function getReadiness(): array
    {
        if ($this->reservaDetalleId === null) {
            return $this->emptyReadiness();
        }

        $detalle = ReservaDetalle::with(['reservable', 'huespedes', 'reserva'])->find($this->reservaDetalleId);

        if ($detalle === null) {
            return $this->emptyReadiness();
        }

        return app(ObtenerReadinessCheckIn::class)->calcular($detalle);
    }

    // ── Metrics ───────────────────────────────────────────────────────────────

    /** @return array<string, int> */
    public function getMetricasCheckIn(): array
    {
        $hoy = now()->startOfDay();

        return [
            'confirmadas_total' => Reserva::query()->whereIn('estado', [EstadoReserva::CONFIRMADA->value, EstadoReserva::PARCIALMENTE_CHECKED_IN->value])->count(),
            'pendientes_hoy' => Reserva::query()->where('estado', EstadoReserva::CONFIRMADA->value)->whereDate('fecha_check_in', '<=', $hoy)->count(),
            'realizadas_hoy' => Estancia::query()->where('estado', EstadoEstancia::ACTIVA->value)->whereDate('check_in_at', $hoy)->count(),
            'habitaciones_disponibles' => Habitacion::query()->where('estado', EstadoEspacio::Disponible)->count(),
        ];
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function submit(RealizarCheckInHabitacion $interactor): void
    {
        if ($this->reserva === null) {
            Notification::make()->title('Seleccione una reservación')->warning()->send();

            return;
        }

        try {
            $formData = $this->form->getState();

            /** @var int|null $userId */
            $userId = auth()->id();

            $detalleId = $this->reservaDetalleId
                ?? (is_numeric($formData['detalle_id'] ?? null) ? (int) $formData['detalle_id'] : null)
                ?? $this->reserva->detalles()->whereNull('parent_id')->first()?->id;

            if (! is_numeric($detalleId) || (int) $detalleId <= 0) {
                throw new DomainException('No se encontró un detalle de habitación válido para realizar Check-In.');
            }

            // Construir huéspedes nuevos
            $huespedesDto = [];
            foreach ((array) ($formData['huespedes_nuevos'] ?? []) as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $nombre = $item['nombre'] ?? null;
                if (! is_string($nombre) || trim($nombre) === '') {
                    continue;
                }

                $identificacion = $item['identificacion'] ?? null;

                $huespedesDto[] = new RegistrarHuespedData(
                    nombre: trim($nombre),
                    numeroDocumento: is_string($identificacion) ? trim($identificacion) : null,
                    tipoHuesped: match ($item['tipo'] ?? 'adulto') {
                        'nino' => TipoHuesped::NINO,
                        'infante' => TipoHuesped::INFANTE,
                        default => TipoHuesped::ADULTO,
                    },
                    esTitular: false,
                );
            }

            $dto = new RealizarCheckInData(
                reservaDetalleId: (int) $detalleId,
                huespedes: $huespedesDto,
                depositoOGarantia: is_numeric($formData['limite_cuenta'] ?? null) ? (float) $formData['limite_cuenta'] : null,
                limiteCuenta: is_numeric($formData['limite_cuenta'] ?? null) ? (float) $formData['limite_cuenta'] : null,
                cantidadLlaves: is_numeric($formData['cantidad_llaves'] ?? null) ? (int) $formData['cantidad_llaves'] : 1,
                observaciones: is_string($formData['observaciones'] ?? null) ? $formData['observaciones'] : null,
                usuarioId: $userId,
            );

            $estancia = $interactor->ejecutar($dto);

            Notification::make()
                ->title('Check-In realizado')
                ->body("Habitación {$estancia->habitacion?->numero} — Estancia #{$estancia->id} creada.")
                ->success()
                ->send();

            $this->redirect(ReservaResource::getUrl('view', ['record' => $this->reserva->id]));

        } catch (DomainException $e) {
            Notification::make()
                ->title('Error en Check-In')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Error inesperado')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function emptyReadiness(): array
    {
        return [
            'reserva_confirmada' => false,
            'detalle_activo' => false,
            'habitacion_disponible' => false,
            'habitacion_limpia' => false,
            'sin_bloqueo_mantenimiento' => false,
            'sin_estancia_activa' => false,
            'titular_identificado' => false,
            'documentacion_completa' => false,
            'capacidad_valida' => true,
            'puede_realizar_check_in' => false,
            'bloqueos' => [],
            'advertencias' => [],
            'estado_habitacion_label' => '—',
            'estado_habitacion_color' => 'gray',
            'habitacion_numero' => '—',
            'total_huespedes' => 0,
            'adultos_registrados' => 0,
            'ninos_registrados' => 0,
            'capacidad_habitacion' => 0,
        ];
    }
}
