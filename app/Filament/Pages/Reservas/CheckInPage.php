<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\BusinessLogic\CheckIn\ObtenerReadinessCheckIn;
use App\BusinessLogic\Reservas\Data\RealizarCheckInData;
use App\BusinessLogic\Reservas\Data\RegistrarHuespedData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Filament\Resources\Reservas\ReservaResource;
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
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
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

    protected static ?string $title = 'Asistente de Check-In';

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
            Action::make('volver')
                ->label('Volver a lista')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(self::getUrl())
                ->visible(fn (): bool => $this->reserva !== null),
        ];
    }

    // ── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if ($this->record !== null) {
            $reserva = Reserva::query()
                ->with(['moneda', 'detalles.reservable', 'huespedes'])
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
                Wizard::make([

                    // ── PASO 1: Seleccionar Habitación ──────────────────────────
                    Step::make('Habitación')
                        ->icon('heroicon-o-home')
                        ->description('Seleccione la habitación a hacer check-in')
                        ->schema([
                            Select::make('detalle_id')
                                ->label('Habitación de la Reserva')
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
                                ->helperText('Seleccione la habitación específica para iniciar el proceso de check-in.')
                                ->columnSpanFull(),
                        ]),

                    // ── PASO 2: Huéspedes ───────────────────────────────────────
                    Step::make('Huéspedes')
                        ->icon('heroicon-o-users')
                        ->description('Registre los acompañantes si los hay')
                        ->schema([
                            Section::make('Acompañantes')
                                ->description('Agregue a todos los huéspedes que ocuparán la habitación. El titular se registra automáticamente.')
                                ->icon('heroicon-o-user-group')
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
                                                ->native(false),

                                            TextInput::make('identificacion')
                                                ->label('Número de Documento')
                                                ->required()
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
                                ]),
                        ]),

                    // ── PASO 3: Estado de la Habitación ────────────────────────
                    Step::make('Estado')
                        ->icon('heroicon-o-check-badge')
                        ->description('Verifique las condiciones operativas de la habitación')
                        ->schema([
                            Section::make('Readiness de la Habitación')
                                ->description('Estado actual de la habitación para el ingreso del huésped.')
                                ->icon('heroicon-o-shield-check')
                                ->schema($this->buildReadinessSchema()),
                        ]),

                    // ── PASO 4: Garantía y Cuenta ───────────────────────────────
                    Step::make('Garantía')
                        ->icon('heroicon-o-credit-card')
                        ->description('Configure cuenta de consumo y llaves')
                        ->schema([
                            Section::make('Llaves y Cuenta de Consumo')
                                ->icon('heroicon-o-key')
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
                                        ->helperText('Permite cargar consumos de restaurante, bar, lavandería, etc.')
                                        ->live()
                                        ->onColor('success')
                                        ->inline(false),

                                    TextInput::make('limite_cuenta')
                                        ->label('Límite autorizado de la cuenta')
                                        ->numeric()
                                        ->prefix(fn (): string => MonedaHelper::simbolo($this->reserva?->moneda))
                                        ->minValue(0)
                                        ->helperText('Monto máximo permitido para cargos a cuenta.')
                                        ->visible(fn (callable $get): bool => (bool) $get('abrir_cuenta')),

                                    Textarea::make('observaciones')
                                        ->label('Observaciones de entrada')
                                        ->placeholder('Estado de la habitación, solicitudes especiales, notas de recepción...')
                                        ->rows(3)
                                        ->maxLength(2000)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    // ── PASO 5: Confirmar ───────────────────────────────────────
                    Step::make('Confirmar')
                        ->icon('heroicon-o-check-badge')
                        ->description('Revise y confirme el check-in')
                        ->schema($this->buildResumenSchema()),

                ]) // fin Wizard::make
                    ->submitAction(
                        Action::make('realizar_check_in')
                            ->label('REALIZAR CHECK-IN')
                            ->icon('heroicon-o-key')
                            ->color('success')
                            ->size(Size::Large)
                            ->submit('submit')
                    ),
            ]);
    }

    // ── Table (pantalla de lista) ─────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with(['habitacion', 'detalles'])
                    ->whereIn('estado', [EstadoReserva::CONFIRMADA, EstadoReserva::PARCIALMENTE_CHECKED_IN])
            )
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Reserva')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_cliente')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—'),

                TextColumn::make('fecha_check_in')
                    ->label('Check-In')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('adultos')
                    ->label('Adultos'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),

                IconColumn::make('solicita_cuenta')
                    ->label('Cuenta')
                    ->boolean(),
            ])
            ->defaultSort('fecha_check_in')
            ->recordActions([
                Action::make('iniciar')
                    ->label('Check-In')
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->url(fn (Reserva $record): string => self::getUrl(['record' => $record->id])),
            ]);
    }

    // ── Readiness ─────────────────────────────────────────────────────────────

    /** @return array<int, Component> */
    private function buildReadinessSchema(): array
    {
        if ($this->reservaDetalleId === null) {
            return [
                Section::make('Seleccione primero una habitación en el Paso 1')
                    ->icon('heroicon-o-information-circle')
                    ->schema([]),
            ];
        }

        $readiness = $this->getReadiness();

        $numeroHabitacion = is_string($readiness['habitacion_numero'] ?? null) ? $readiness['habitacion_numero'] : '—';
        $estadoHabitacionLabel = is_string($readiness['estado_habitacion_label'] ?? null) ? $readiness['estado_habitacion_label'] : '—';
        $totalHuespedes = is_int($readiness['total_huespedes'] ?? null) ? $readiness['total_huespedes'] : 0;

        /** @var list<string> $bloqueos */
        $bloqueos = $readiness['bloqueos'] ?? [];

        /** @var list<string> $advertencias */
        $advertencias = $readiness['advertencias'] ?? [];

        $items = [
            ['key' => 'reserva_confirmada',        'label' => 'Reserva confirmada'],
            ['key' => 'habitacion_disponible',      'label' => 'Habitación disponible'],
            ['key' => 'habitacion_limpia',          'label' => 'Habitación limpia'],
            ['key' => 'sin_bloqueo_mantenimiento',  'label' => 'Sin bloqueo de mantenimiento'],
            ['key' => 'sin_estancia_activa',        'label' => 'Sin estancia activa'],
            ['key' => 'titular_identificado',       'label' => 'Titular identificado'],
        ];

        return [
            Grid::make(2)
                ->schema([
                    Section::make('Checklist')
                        ->icon('heroicon-o-shield-check')
                        ->description('Hab. '.$numeroHabitacion.' — Estado: '.$estadoHabitacionLabel)
                        ->schema(
                            array_map(
                                fn (array $item): IconEntry => IconEntry::make($item['key'])
                                    ->label($item['label'])
                                    ->state(fn () => (bool) ($readiness[$item['key']] ?? false))
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                                $items
                            )
                        )
                        ->columns(1),

                    Section::make('Resumen')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            TextEntry::make('estado_check')
                                ->label('Check-In')
                                ->state(fn () => $readiness['puede_realizar_check_in'] ? 'LISTO' : 'BLOQUEADO')
                                ->badge()
                                ->color(fn () => $readiness['puede_realizar_check_in'] ? 'success' : 'danger'),

                            TextEntry::make('estado_habitacion')
                                ->label('Estado Habitación')
                                ->state(fn () => $readiness['estado_habitacion_label'])
                                ->badge()
                                ->color(fn () => $readiness['estado_habitacion_color']),

                            TextEntry::make('huespedes_count')
                                ->label('Huéspedes registrados')
                                ->state(fn () => (string) $totalHuespedes),

                            // Bloqueos
                            ...array_map(
                                fn (string $b): TextEntry => TextEntry::make('bloqueo_'.md5($b))
                                    ->label('Bloqueo')
                                    ->state(fn () => $b)
                                    ->badge()
                                    ->color('danger'),
                                $bloqueos
                            ),

                            // Advertencias
                            ...array_map(
                                fn (string $a): TextEntry => TextEntry::make('adv_'.md5($a))
                                    ->label('Advertencia')
                                    ->state(fn () => $a)
                                    ->badge()
                                    ->color('warning'),
                                $advertencias
                            ),
                        ]),
                ]),
        ];
    }

    /** @return array<int, Component> */
    private function buildResumenSchema(): array
    {
        if ($this->reserva === null) {
            return [];
        }

        return [
            Grid::make(2)
                ->schema([
                    Section::make('Datos de la Reserva')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            TextEntry::make('codigo')
                                ->label('Código')
                                ->state(fn () => $this->reserva->codigo_reserva)
                                ->weight('bold'),

                            TextEntry::make('cliente')
                                ->label('Titular')
                                ->state(fn () => $this->reserva->nombre_cliente ?? '—'),

                            TextEntry::make('checkin')
                                ->label('Check-In programado')
                                ->state(fn () => $this->reserva?->fecha_check_in?->format('d/m/Y') ?? '—'),

                            TextEntry::make('checkout')
                                ->label('Check-Out programado')
                                ->state(fn () => $this->reserva?->fecha_check_out?->format('d/m/Y') ?? '—'),

                            TextEntry::make('noches')
                                ->label('Noches')
                                ->state(function (): string {
                                    $ci = $this->reserva?->fecha_check_in;
                                    $co = $this->reserva?->fecha_check_out;

                                    return $ci && $co ? (string) $ci->diffInDays($co) : '—';
                                }),

                            TextEntry::make('saldo')
                                ->label('Saldo pendiente')
                                ->state(fn () => MonedaHelper::simbolo($this->reserva->moneda).' '.number_format((float) $this->reserva->saldo, 2))
                                ->badge()
                                ->color(fn () => (float) $this->reserva->saldo > 0 ? 'warning' : 'success'),
                        ])
                        ->columns(2),

                    Section::make('Validación Final')
                        ->icon('heroicon-o-check-badge')
                        ->description('Todos los requisitos deben cumplirse antes del ingreso.')
                        ->schema($this->buildReadinessSchema()),
                ]),
        ];
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
