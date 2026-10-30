<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\GestionarConciliacion;
use App\Repository\Models\Tesoreria\ExtractoBancario;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

final class ConciliacionBancaria extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Fondos y bancos';

    protected static ?string $navigationLabel = 'Conciliación bancaria';

    protected static ?string $title = 'Conciliación bancaria';

    protected static ?string $slug = 'tesoreria/conciliacion';

    protected static ?int $navigationSort = 34;

    protected string $view = 'filament.pages.tesoreria.conciliacion';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && ((auth()->user()?->can('Auditar:Fondos') ?? false) || (auth()->user()?->can('Registrar:Extracto') ?? false) || (auth()->user()?->can('Conciliar:Banco') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->extractos())->columns([TextColumn::make('fondo.nombre')->label('Banco'), TextColumn::make('fecha')->date('d/m/Y'), TextColumn::make('referencia')->searchable(), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('fondo.moneda.codigo')->label('Moneda'), TextColumn::make('conciliacion.comision')->label('Comisión reconocida')->numeric(decimalPlaces: 2)->placeholder('—'), TextColumn::make('conciliacion.pago_transaccion_id')->label('Pasarela')->placeholder('—'), TextColumn::make('situacion')->label('Conciliación')->state(fn (ExtractoBancario $record): string => app(TesoreriaQuery::class)->conciliado($record->id) ? 'Conciliado' : 'Pendiente')->badge()])->recordActions([Action::make('soporte')->label('Ver extracto')->visible(fn (): bool => $this->permitido('Registrar:Extracto') || $this->permitido('Conciliar:Banco') || $this->permitido('Auditar:Fondos'))->url(fn (ExtractoBancario $record): string => route('tesoreria.archivo', ['tipo' => 'extracto', 'id' => $record->id]))->openUrlInNewTab()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('extracto')->label('Registrar partida del extracto')->visible(fn (): bool => $this->permitido('Registrar:Extracto'))->schema([$this->claveOperacion(), Select::make('fondo')->label('Cuenta bancaria')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos('banco'))->required(), DatePicker::make('fecha')->maxDate(today())->required(), TextInput::make('referencia')->maxLength(150)->required(), Select::make('tipo')->options(['ingreso' => 'Abono del banco', 'egreso' => 'Débito del banco'])->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), $this->soporte()])->action(function (array $data): void {
                $this->operar(fn () => app(GestionarConciliacion::class)->registrar($this->actorId(), $this->idDato($data, 'fondo'), $this->texto($data, 'fecha'), $this->texto($data, 'referencia'), $this->numero($data, 'importe') * ($this->texto($data, 'tipo') === 'egreso' ? -1 : 1), $this->texto($data, 'soporte'), $this->texto($data, 'clave')), 'Partida bancaria conservada; no modifica por sí sola el saldo del libro');
            }),
            Action::make('conciliar')->label('Relacionar libro con extracto')->visible(fn (): bool => $this->permitido('Conciliar:Banco'))->schema([$this->claveOperacion(), Select::make('extracto')->label('Partida del extracto pendiente')->options(fn (): array => app(TesoreriaQuery::class)->extractosPendientes())->searchable()->required(), Select::make('movimiento')->label('Movimiento del libro pendiente')->options(fn (): array => app(TesoreriaQuery::class)->movimientosPendientes())->searchable()->required()])->action(function (array $data): void {
                $this->operar(fn () => app(GestionarConciliacion::class)->conciliar($this->actorId(), $this->idDato($data, 'extracto'), $this->idDato($data, 'movimiento'), $this->texto($data, 'clave')), 'Partidas conciliadas sin cambiar sus importes');
            }),
            Action::make('liquidar')->label('Liquidar cobro de pasarela')->visible(fn (): bool => $this->permitido('Conciliar:Banco'))->schema([$this->claveOperacion(), Select::make('transaccion')->label('Cobro capturado pendiente')->options(fn (): array => app(TesoreriaQuery::class)->pasarelasPendientes())->searchable()->required(), Select::make('extracto')->label('Abono bancario neto')->options(fn (): array => app(TesoreriaQuery::class)->extractosPendientes())->searchable()->required(), TextInput::make('comision')->label('Comisión descontada por el banco')->numeric()->minValue(0)->step(.01)->default(0)->required()])->action(function (array $data): void {
                $this->operar(fn () => app(GestionarConciliacion::class)->liquidarPasarela($this->actorId(), $this->idDato($data, 'transaccion'), $this->idDato($data, 'extracto'), $this->numero($data, 'comision'), $this->texto($data, 'clave')), 'Liquidación bancaria verificada y comisión registrada');
            }),
        ];
    }
}
