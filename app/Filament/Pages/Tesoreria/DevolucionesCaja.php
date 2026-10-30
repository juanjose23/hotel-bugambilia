<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\GestionarDevoluciones;
use App\Repository\Models\Tesoreria\DevolucionCaja;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

final class DevolucionesCaja extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|UnitEnum|null $navigationGroup = 'Caja y cierres';

    protected static ?string $navigationLabel = 'Devoluciones de efectivo';

    protected static ?string $title = 'Devoluciones de efectivo';

    protected static ?string $slug = 'tesoreria/devoluciones';

    protected static ?int $navigationSort = 24;

    protected string $view = 'filament.pages.tesoreria.devoluciones';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && ((auth()->user()?->can('Auditar:Fondos') ?? false) || (auth()->user()?->can('Solicitar:Devolucion') ?? false) || (auth()->user()?->can('Aprobar:Devolucion') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->devoluciones(auth()->user() ?? throw new \DomainException('Inicia sesión.')))->columns([TextColumn::make('id')->label('Solicitud'), TextColumn::make('pago_cuenta_id')->label('Pago original'), TextColumn::make('sesion.caja.nombre')->label('Caja'), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('pago.moneda.codigo')->label('Moneda'), TextColumn::make('motivo')->wrap(), TextColumn::make('estado')->formatStateUsing(fn (string $state): string => match ($state) {
            'solicitada' => 'Pendiente de autorización','aprobada' => 'Efectivo devuelto','rechazada' => 'Rechazada',default => $state
        })->badge(), TextColumn::make('aprobado_por')->label('Revisor')])->recordActions([Action::make('resolver')->label('Revisar devolución')->visible(fn (DevolucionCaja $record): bool => $record->estado === 'solicitada' && ($this->permitido('Aprobar:Devolucion')))->schema([$this->claveOperacion(), Select::make('decision')->options(['aprobar' => 'Autorizar salida del efectivo', 'rechazar' => 'Rechazar'])->required(), Textarea::make('motivo')->minLength(10)->maxLength(1000)->required()])->action(function (DevolucionCaja $record, array $data): void {
            $this->operar(fn () => app(GestionarDevoluciones::class)->resolver($this->actorId(), $record->id, $this->texto($data, 'decision') === 'aprobar', $this->texto($data, 'motivo'), $this->texto($data, 'clave')), 'Resolución de devolución registrada');
        })]);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('solicitar')->label('Solicitar devolución')->visible(fn (): bool => $this->permitido('Solicitar:Devolucion'))->schema([$this->claveOperacion(), Select::make('pago')->label('Pago de efectivo original')->options(fn (): array => app(TesoreriaQuery::class)->pagosEfectivo())->searchable()->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), Textarea::make('motivo')->minLength(10)->maxLength(1000)->required()])->action(function (array $data): void {
            $this->operar(fn () => app(GestionarDevoluciones::class)->solicitar($this->actorId(), $this->idDato($data, 'pago'), $this->numero($data, 'importe'), $this->texto($data, 'motivo'), $this->texto($data, 'clave')), 'Devolución pendiente de otro autorizador');
        })];
    }
}
