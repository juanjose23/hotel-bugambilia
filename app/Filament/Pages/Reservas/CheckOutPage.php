<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Resources\Reservas\Schemas\CheckOut\SeccionCuentaConsumoCheckOut;
use App\Filament\Resources\Reservas\Schemas\CheckOut\SeccionObservacionesCheckOut;
use App\Filament\Resources\Reservas\Schemas\CheckOut\SeccionResumenCheckOut;
use App\Interactors\CheckOut\RegistrarCheckOut;
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
    use HasPageShield;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';

    protected static string|UnitEnum|null $navigationGroup = 'Reservaciones';

    protected static ?string $navigationLabel = 'Check-Out';

    protected static ?string $title = 'Registro de Check-Out';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.resources.reservas.check-out-page';

    #[Url]
    public ?int $record = null;

    public ?Reserva $reserva = null;

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
            $reserva = Reserva::query()->with(['habitacion', 'espacio', 'estancia.cuenta'])->find($this->record);
            if ($reserva !== null && $reserva->estado === EstadoReserva::CHECKED_IN) {
                $this->reserva = $reserva;
                $this->form->fill([
                    'reserva_id' => $reserva->id,
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
                SeccionResumenCheckOut::make()
                    ->visible(fn (): bool => $this->reserva !== null),

                SeccionCuentaConsumoCheckOut::make()
                    ->visible(fn (): bool => $this->reserva !== null),

                SeccionObservacionesCheckOut::make()
                    ->visible(fn (): bool => $this->reserva !== null),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with(['habitacion', 'espacio', 'estancia.cuenta'])
                    ->where('estado', EstadoReserva::CHECKED_IN)
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
                    ->money('NIO')
                    ->placeholder('C$ 0.00'),
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
            'checked_in_total' => Reserva::query()->where('estado', EstadoReserva::CHECKED_IN)->count(),
            'salidas_hoy' => Reserva::query()->where('estado', EstadoReserva::CHECKED_IN)->whereDate('fecha_check_out', '<=', $hoy)->count(),
            'finalizadas_hoy' => Estancia::query()->where('estado', EstadoEstancia::FINALIZADA)->whereDate('check_out_at', $hoy)->count(),
        ];
    }

    public function submit(RegistrarCheckOut $interactor): void
    {
        if ($this->reserva === null) {
            Notification::make()
                ->title('Seleccione una estancia')
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
                ->body('Ocurrió un error al registrar el check-out.'.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
