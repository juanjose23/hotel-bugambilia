<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\GestionarEntregas;
use App\Repository\Models\Tesoreria\EntregaCaja;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

final class EntregasCaja extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Caja y cierres';

    protected static ?string $navigationLabel = 'Entregas y depósitos';

    protected static ?string $title = 'Entregas y depósitos';

    protected static ?string $slug = 'tesoreria/entregas';

    protected static ?int $navigationSort = 23;

    protected string $view = 'filament.pages.tesoreria.entregas';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && ((auth()->user()?->can('Auditar:Fondos') ?? false) || (auth()->user()?->can('Entregar:Caja') ?? false) || (auth()->user()?->can('Recibir:Fondos') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->entregas(auth()->user() ?? throw new \DomainException('Inicia sesión.')))->columns([TextColumn::make('id')->label('Entrega'), TextColumn::make('sesion.usuario.name')->label('Entregante'), TextColumn::make('fondo.nombre')->label('Destino'), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('fondo.moneda.codigo')->label('Moneda'), TextColumn::make('referencia'), TextColumn::make('estado')->formatStateUsing(fn (string $state): string => match ($state) {
            'en_transito' => 'En tránsito','recibida' => 'Recibida',default => $state
        })->badge(), TextColumn::make('recibido_por')->label('Receptor')])->recordActions([Action::make('recibir')->label('Confirmar recepción')->visible(fn (EntregaCaja $record): bool => $record->estado === 'en_transito' && ($this->permitido('Recibir:Fondos')))->modalDescription('Confirma el dinero recibido. Si el destino es banco, selecciona el abono del extracto que acredita el depósito.')->schema([$this->claveOperacion(), Select::make('extracto')->label('Abono bancario, obligatorio para depósitos')->options(fn (): array => app(TesoreriaQuery::class)->extractosPendientes())->searchable()])->action(function (EntregaCaja $record, array $data): void {
            $this->operar(fn () => app(GestionarEntregas::class)->recibir($this->actorId(), $record->id, $this->texto($data, 'clave'), $this->idOpcional($data, 'extracto')), 'Entrega recibida y registrada en el fondo de destino');
        })]);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('entregar')->label('Entregar efectivo de mi turno')->visible(fn (): bool => $this->permitido('Entregar:Caja'))->schema([$this->claveOperacion(), Select::make('destino')->label('Destino del efectivo')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos())->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), TextInput::make('referencia')->label('Referencia de entrega o depósito')->maxLength(150)->required()])->action(function (array $data): void {
            $this->operar(fn () => app(GestionarEntregas::class)->entregar($this->actorId(), $this->idDato($data, 'destino'), $this->numero($data, 'importe'), $this->texto($data, 'referencia'), $this->texto($data, 'clave')), 'Efectivo retirado del turno; recepción pendiente');
        })];
    }
}
