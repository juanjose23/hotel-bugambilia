<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\AbastecerTurnoCaja;
use App\Repository\Models\Tesoreria\AbastecimientoCaja;
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

final class FondosTurnoCaja extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string|UnitEnum|null $navigationGroup = 'Fondos y bancos';

    protected static ?string $navigationLabel = 'Fondos para turnos';

    protected static ?string $title = 'Fondos para turnos';

    protected static ?string $slug = 'tesoreria/fondos-turno';

    protected static ?int $navigationSort = 32;

    protected string $view = 'filament.pages.tesoreria.fondos-turno';

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return config('tesoreria.habilitado') === true && $u !== null && ($u->can('Solicitar:FondoTurno') || $u->can('Autorizar:FondoTurno') || $u->can('Auditar:Fondos'));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->abastecimientos(auth()->user() ?? throw new \DomainException('Inicia sesión.')))->columns([TextColumn::make('id')->label('Solicitud'), TextColumn::make('sesion.usuario.name')->label('Cajero'), TextColumn::make('fondo.nombre')->label('Origen del fondo'), TextColumn::make('fondo.moneda.codigo')->label('Moneda'), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('referencia'), TextColumn::make('estado')->formatStateUsing(fn (string $state): string => match ($state) {
            'pendiente' => 'Pendiente de autorización','aprobado' => 'Fondo entregado','rechazado' => 'Rechazado',default => $state
        })->badge()])->recordActions([
            Action::make('resolver')->label('Revisar entrega de fondo')->visible(fn (AbastecimientoCaja $record): bool => $record->estado === 'pendiente' && $this->permitido('Autorizar:FondoTurno'))->schema([$this->claveOperacion(), Select::make('decision')->options(['aprobar' => 'Autorizar y entregar', 'rechazar' => 'Rechazar solicitud'])->required(), Select::make('extracto')->label('Débito del extracto, obligatorio si el origen es banco')->options(fn (): array => app(TesoreriaQuery::class)->extractosPendientes())->searchable(), Textarea::make('motivo')->minLength(10)->maxLength(1000)->required()])->action(function (AbastecimientoCaja $record, array $data): void {
                $this->operar(fn () => app(AbastecerTurnoCaja::class)->resolver($this->actorId(), $record->id, $this->texto($data, 'decision') === 'aprobar', $this->texto($data, 'motivo'), $this->texto($data, 'clave'), $this->idOpcional($data, 'extracto')), 'Solicitud resuelta; origen y turno conservan los movimientos');
            }),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('solicitar')->label('Solicitar fondo para mi turno')->visible(fn (): bool => $this->permitido('Solicitar:FondoTurno'))->schema([$this->claveOperacion(), Select::make('fondo')->label('Fondo de origen')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos())->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), TextInput::make('referencia')->label('Referencia de entrega / retiro bancario')->maxLength(150)->required()])->action(function (array $data): void {
            $this->operar(fn () => app(AbastecerTurnoCaja::class)->solicitar($this->actorId(), $this->idDato($data, 'fondo'), $this->numero($data, 'importe'), $this->texto($data, 'referencia'), $this->texto($data, 'clave')), 'Fondo solicitado; otro responsable debe autorizarlo');
        })];
    }
}
