<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\GestionarFondos;
use App\Repository\Models\Tesoreria\FondoTesoreria;
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

final class FondosTesoreria extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|UnitEnum|null $navigationGroup = 'Fondos y bancos';

    protected static ?string $navigationLabel = 'Fondos y bancos';

    protected static ?string $title = 'Fondos y bancos';

    protected static ?string $slug = 'tesoreria/fondos';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.tesoreria.fondos';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && (auth()->user()?->can('ViewAny:Fondos') ?? false);
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->fondos())->columns([
            TextColumn::make('codigo')->searchable(), TextColumn::make('nombre')->searchable(), TextColumn::make('tipo')->label('Tipo de fondo')->formatStateUsing(fn (string $state): string => match ($state) {
                'banco' => 'Cuenta bancaria','caja_chica' => 'Caja chica','boveda' => 'Bóveda',default => $state
            })->badge(), TextColumn::make('moneda.codigo')->label('Moneda'), TextColumn::make('saldo')->label('Saldo del libro')->default('0')->numeric(decimalPlaces: 2), TextColumn::make('responsable.name')->label('Responsable'),
        ])->recordActions([Action::make('movimientos')->label('Ver libro')->url(fn (FondoTesoreria $record): string => MovimientosFondos::getUrl(['filters' => ['fondo_id' => ['value' => $record->id]]]))]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear')->label('Crear fondo o banco')->visible(fn (): bool => $this->permitido('Administrar:Fondos'))->schema([
                $this->claveOperacion(), TextInput::make('codigo')->maxLength(30)->required(), TextInput::make('nombre')->maxLength(100)->required(), Select::make('tipo')->options(['banco' => 'Cuenta bancaria', 'caja_chica' => 'Caja chica', 'boveda' => 'Bóveda / administración'])->required(), Select::make('moneda_id')->label('Moneda')->options(fn (): array => app(TesoreriaQuery::class)->monedas())->required(), Select::make('responsable_id')->label('Responsable')->options(fn (): array => app(TesoreriaQuery::class)->usuarios())->required(), TextInput::make('banco')->maxLength(100), TextInput::make('identificacion')->label('Número o identificación de cuenta')->maxLength(100), TextInput::make('saldo')->label('Saldo inicial documentado')->numeric()->minValue(0)->step(.01)->default(0)->required(), Textarea::make('origen')->label('Documento y fecha que sustentan el saldo inicial')->minLength(10)->maxLength(1000)->required(), TextInput::make('limite')->label('Límite de cada vale de caja chica')->numeric()->minValue(0)->step(.01)->default(0)->required(),
            ])->action(function (array $data): void {
                $this->operar(fn () => app(GestionarFondos::class)->crear($this->actorId(), $this->texto($data, 'codigo'), $this->texto($data, 'nombre'), $this->texto($data, 'tipo'), $this->idDato($data, 'moneda_id'), $this->idDato($data, 'responsable_id'), $this->numero($data, 'saldo'), $this->texto($data, 'origen'), $this->numero($data, 'limite'), $this->texto($data, 'clave'), is_string($data['banco'] ?? null) ? $data['banco'] : null, is_string($data['identificacion'] ?? null) ? $data['identificacion'] : null), 'Fondo creado con saldo documentado');
            }),
            Action::make('transferir')->label('Trasladar / reponer fondo')->visible(fn (): bool => $this->permitido('Transferir:Fondos'))->modalDescription('Traslada dinero entre fondos de la misma moneda. La reposición no registra un gasto nuevo.')->schema([$this->claveOperacion(), Select::make('origen')->label('Fondo de origen')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos())->required(), Select::make('destino')->label('Fondo de destino')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos())->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), Textarea::make('concepto')->minLength(10)->maxLength(1000)->required()])->action(function (array $data): void {
                $this->operar(fn () => app(GestionarFondos::class)->transferir($this->actorId(), $this->idDato($data, 'origen'), $this->idDato($data, 'destino'), $this->numero($data, 'importe'), $this->texto($data, 'concepto'), $this->texto($data, 'clave')), 'Traslado registrado en ambos fondos');
            }),
            Action::make('movimiento')->label('Movimiento bancario')->visible(fn (): bool => $this->permitido('Administrar:Fondos'))->schema([$this->claveOperacion(), Select::make('fondo')->label('Cuenta bancaria')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos('banco'))->required(), Select::make('tipo')->options(['ingreso' => 'Entrada', 'egreso' => 'Salida / comisión'])->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), TextInput::make('referencia')->maxLength(150)->required(), Textarea::make('concepto')->minLength(10)->maxLength(1000)->required()])->action(function (array $data): void {
                $this->operar(fn () => app(GestionarFondos::class)->movimiento($this->actorId(), $this->idDato($data, 'fondo'), $this->numero($data, 'importe') * ($this->texto($data, 'tipo') === 'egreso' ? -1 : 1), $this->texto($data, 'concepto'), $this->texto($data, 'referencia'), $this->texto($data, 'clave')), 'Movimiento registrado; conciliación pendiente');
            }),
        ];
    }
}
