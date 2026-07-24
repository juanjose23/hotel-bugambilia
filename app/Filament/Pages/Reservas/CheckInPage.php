<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Resources\Reservas\Schemas\CheckIn\SeccionFormularioCheckIn;
use App\Filament\Resources\Reservas\Schemas\CheckIn\SeccionResumenCheckIn;
use App\Interactors\CheckIn\RegistrarCheckIn;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * @property Schema $form
 */
final class CheckInPage extends Page implements HasForms, HasTable
{
    use HasPageShield;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|UnitEnum|null $navigationGroup = 'Reservaciones';

    protected static ?string $navigationLabel = 'Check-In';

    protected static ?string $title = 'Registro de Check-In';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reservas.check-in-page';

    #[Url]
    public ?int $record = null;

    public ?Reserva $reserva = null;

    /** @var array<string, mixed> */
    public array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver_lista')
                ->label('Volver a Lista de Check-In')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(self::getUrl())
                ->visible(fn (): bool => $this->record !== null || $this->reserva !== null),
        ];
    }

    public function mount(): void
    {
        if ($this->record !== null) {
            $reserva = Reserva::query()->with(['habitacion', 'espacio'])->find($this->record);
            if ($reserva !== null && $reserva->estado === EstadoReserva::CONFIRMADA) {
                $this->reserva = $reserva;
                $this->llenarFormulario($reserva);

                return;
            }
        }

        $this->form->fill([
            'cantidad_llaves' => 1,
            'tipo_persona' => 'natural',
        ]);
    }

    private function llenarFormulario(Reserva $reserva): void
    {
        $this->form->fill([
            'reserva_id' => $reserva->id,
            'tipo_persona' => 'natural',
            'nombre_cliente' => $reserva->nombre_cliente,
            'telefono_cliente' => $reserva->telefono_cliente,
            'email_cliente' => $reserva->email_cliente,
            'cantidad_llaves' => 1,
            'abrir_cuenta' => (bool) ($reserva->solicita_cuenta ?? false),
            'limite_cuenta' => $reserva->limite_cuenta_solicitado,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                SeccionResumenCheckIn::make()
                    ->visible(fn (): bool => $this->reserva !== null),

                SeccionFormularioCheckIn::make()
                    ->visible(fn (): bool => $this->reserva !== null),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with(['habitacion', 'espacio'])
                    ->where('estado', EstadoReserva::CONFIRMADA)
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
                    ->label('Fecha Check-In')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('adultos')
                    ->label('Adultos')
                    ->numeric(),

                IconColumn::make('solicita_cuenta')
                    ->label('Cuenta')
                    ->boolean(),
            ])
            ->defaultSort('fecha_check_in', 'asc')
            ->actions([
                Action::make('iniciar_check_in')
                    ->label('Completar Check-In')
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->url(fn (Reserva $record): string => self::getUrl(['record' => $record->id])),
            ]);
    }

    /** @return array<string, int> */
    public function getMetricasCheckIn(): array
    {
        $hoy = now()->startOfDay();

        return [
            'confirmadas_total' => Reserva::query()->where('estado', EstadoReserva::CONFIRMADA)->count(),
            'pendientes_hoy' => Reserva::query()->where('estado', EstadoReserva::CONFIRMADA)->whereDate('fecha_check_in', '<=', $hoy)->count(),
            'realizadas_hoy' => Estancia::query()->where('estado', EstadoEstancia::ACTIVA)->whereDate('check_in_at', $hoy)->count(),
        ];
    }

    public function submit(RegistrarCheckIn $interactor): void
    {
        if ($this->reserva === null) {
            Notification::make()
                ->title('Seleccione una reservación')
                ->warning()
                ->send();

            return;
        }

        try {
            $data = $this->form->getState();

            /** @var int|null $userId */
            $userId = auth()->id();

            $interactor->ejecutar(
                $this->reserva,
                $userId,
                $data,
            );

            Notification::make()
                ->title('Check-In registrado exitosamente')
                ->success()
                ->send();

            $this->redirect(ReservaResource::getUrl('view', ['record' => $this->reserva->id]));
        } catch (DomainException $e) {
            Notification::make()
                ->title('Error en el Check-In')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un error al registrar el check-in: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
