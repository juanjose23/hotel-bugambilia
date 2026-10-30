<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Shared\Actions\VerGuiaAction;
use App\Interactors\Tesoreria\CobrarDesdeCaja;
use App\Interactors\Tesoreria\GestionarCaja;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use BackedEnum;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

final class MiTurnoCaja extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Caja y cierres';

    protected static ?string $navigationLabel = 'Mi turno de caja';

    protected static ?string $title = 'Mi turno de caja';

    protected static ?string $slug = 'tesoreria/mi-turno';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.tesoreria.mi-turno';

    public static function canAccess(): bool
    {
        return config('tesoreria.habilitado') && (auth()->user()?->can('Operar:Caja') ?? false);
    }

    private function turnoAbierto(): bool
    {
        return app(OperacionCajaQuery::class)->miSesion($this->actorId())?->estado === 'abierta';
    }

    private function sesionId(): int
    {
        $sesion = app(OperacionCajaQuery::class)->miSesion($this->actorId());
        if ($sesion === null) {
            throw new DomainException('Abre primero un turno.');
        }

        return $sesion->id;
    }

    protected function getViewData(): array
    {
        $s = app(OperacionCajaQuery::class)->miSesion($this->actorId());

        return ['sesion' => $s, 'saldos' => $s !== null ? app(OperacionCajaQuery::class)->resumen($s) : []];
    }

    public function table(Table $table): Table
    {
        return $table->query(app(OperacionCajaQuery::class)->movimientos($this->actorId()))->columns([
            TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'), TextColumn::make('tipo')->label('Movimiento')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                'cobro' => 'Cobro en efectivo','vuelto' => 'Vuelto entregado','ingreso' => 'Ingreso','egreso' => 'Egreso',default => $state
            }),
            TextColumn::make('concepto')->wrap(), TextColumn::make('moneda.codigo')->label('Moneda'), TextColumn::make('importe')->label('Importe')->numeric(decimalPlaces: 2), TextColumn::make('pago_cuenta_id')->label('Pago')->placeholder('Manual'), TextColumn::make('referencia')->placeholder('—'),
        ])->emptyStateHeading('Aún no hay movimientos en este turno');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('abrir')->extraModalFooterActions([VerGuiaAction::make('caja-apertura')])->label('Abrir turno')->icon('heroicon-o-lock-open')->visible(fn (): bool => auth()->user()?->can('Abrir:Caja') && ! in_array(app(OperacionCajaQuery::class)->miSesion($this->actorId())?->estado, ['abierta', 'pendiente_revision'], true))
                ->schema([
                    Hidden::make('clave')->default(fn (): string => Str::uuid()->toString()),
                    Select::make('caja_id')->extraAttributes(['data-guia' => 'caja-elegir'])->label('Caja disponible')->options(fn (): array => app(OperacionCajaQuery::class)->disponibles())->required()->live()->afterStateUpdated(function (mixed $state, Set $set): void {
                        $filas = [];
                        foreach (app(OperacionCajaQuery::class)->monedasCaja(is_numeric($state) ? (int) $state : null) as $id => $nombre) {
                            $filas[] = ['moneda_id' => $id, 'moneda' => $nombre, 'importe' => null];
                        }$set('fondos', $filas);
                    }),
                    Repeater::make('fondos')->extraAttributes(['data-guia' => 'caja-fondo'])->label('Cuenta el fondo recibido por moneda')->addable(false)->deletable(false)->reorderable(false)->minItems(1)->schema([Hidden::make('moneda_id')->required(), TextInput::make('moneda')->disabled()->dehydrated(false), TextInput::make('importe')->label('Fondo contado')->numeric()->minValue(0)->step(.01)->required()]),
                    Textarea::make('origen')->label('Quién entrega el fondo y de dónde procede')->minLength(10)->maxLength(2000)->required(),
                ])->action(function (array $data): void {
                    /** @var array{caja_id:int,clave:string,origen:string,fondos:array<array-key,array{moneda_id:int,importe:float}>} $data */
                    $fondos = array_values(array_map(fn (array $f): array => ['moneda_id' => (int) $f['moneda_id'], 'importe' => (float) $f['importe']], $data['fondos']));
                    $this->operar(fn () => app(GestionarCaja::class)->abrir($this->actorId(), (int) $data['caja_id'], $fondos, $data['origen'], $data['clave']), 'Turno abierto');
                }),
            Action::make('cobrar')->extraModalFooterActions([VerGuiaAction::make('caja-cobrar')])->label('Cobrar una cuenta')->icon('heroicon-o-banknotes')->visible(fn (): bool => $this->turnoAbierto())->schema([
                Hidden::make('clave')->default(fn (): string => Str::uuid()->toString())->required(),
                Select::make('cuenta_id')->extraAttributes(['data-guia' => 'cobro-cuenta'])->label('Cuenta con saldo pendiente')->options(fn (): array => app(OperacionCajaQuery::class)->cuentas())->searchable()->required(),
                TextInput::make('recibido')->extraAttributes(['data-guia' => 'cobro-recibido'])->label('Efectivo recibido')->helperText('En la moneda indicada en la cuenta. Se registran por separado lo recibido y el vuelto.')->numeric()->minValue(.01)->step(.01)->required(),
                Toggle::make('factura')->extraAttributes(['data-guia' => 'cobro-factura'])->label('Emitir factura al liquidar')->default(false)->live()->visible(fn (): bool => auth()->user()?->can('Create:Factura') ?? false),
                Select::make('serie_id')->extraAttributes(['data-guia' => 'cobro-serie'])->label('Serie de factura')->options(fn (): array => app(OperacionCajaQuery::class)->series())->visible(fn (Get $get): bool => (bool) $get('factura'))->required(fn (Get $get): bool => (bool) $get('factura')),
            ])->action(function (array $data): void {
                /** @var array{clave:string,cuenta_id:int,recibido:float,factura?:bool,serie_id?:int|null} $data */
                $this->operar(fn () => app(CobrarDesdeCaja::class)->ejecutar($this->actorId(), (int) $data['cuenta_id'], (float) $data['recibido'], (bool) ($data['factura'] ?? false), isset($data['serie_id']) ? (int) $data['serie_id'] : null, $data['clave']), 'Cobro registrado en cuenta y caja');
            }),
            $this->accionMovimiento('ingreso', 'Registrar ingreso'), $this->accionMovimiento('egreso', 'Registrar egreso'),
            Action::make('cerrar')->extraModalFooterActions([VerGuiaAction::make('caja-cierre')])->label('Arqueo y cierre')->color('warning')->icon('heroicon-o-lock-closed')->visible(fn (): bool => $this->turnoAbierto() && (auth()->user()?->can('Cerrar:Caja') ?? false))
                ->modalDescription('Escribe lo que realmente contaste. Si hay diferencia, el turno se detiene para revisión de otro usuario autorizado.')
                ->fillForm(function (): array {
                    $s = app(OperacionCajaQuery::class)->miSesion($this->actorId());

                    return ['conteo' => $s !== null ? array_map(fn (array $r): array => ['moneda_id' => $r['moneda_id'], 'moneda' => $r['moneda'], 'esperado' => $r['esperado'], 'importe' => null], app(OperacionCajaQuery::class)->resumen($s)) : []];
                })->schema([
                    Repeater::make('conteo')->extraAttributes(['data-guia' => 'caja-conteo'])->label('Efectivo físico')->addable(false)->deletable(false)->reorderable(false)->schema([Hidden::make('moneda_id')->required(), TextInput::make('moneda')->disabled()->dehydrated(false), TextInput::make('esperado')->label('Esperado por el sistema')->disabled()->dehydrated(false), TextInput::make('importe')->label('Efectivo contado')->numeric()->minValue(0)->step(.01)->required()]),
                    Textarea::make('motivo')->extraAttributes(['data-guia' => 'caja-motivo'])->label('Explica cualquier faltante o sobrante')->maxLength(2000)->helperText('Obligatorio cuando el conteo no coincide; mínimo diez caracteres.'),
                ])->action(function (array $data): void {
                    /** @var array{conteo:array<array-key,array{moneda_id:int,importe:float}>,motivo?:string|null} $data */
                    $conteo = array_values(array_map(fn (array $f): array => ['moneda_id' => (int) $f['moneda_id'], 'importe' => (float) $f['importe']], $data['conteo']));
                    $this->operar(fn () => app(GestionarCaja::class)->cerrar($this->actorId(), $this->sesionId(), $conteo, $data['motivo'] ?? null), 'Arqueo registrado; consulta el estado del turno');
                }),
        ];
    }

    private function accionMovimiento(string $tipo, string $etiqueta): Action
    {
        return Action::make($tipo)->label($etiqueta)->color($tipo === 'egreso' ? 'danger' : 'gray')->visible(fn (): bool => $this->turnoAbierto() && (auth()->user()?->can($tipo === 'egreso' ? 'Egreso:Caja' : 'Operar:Caja') ?? false))->schema([
            Hidden::make('clave')->default(fn (): string => Str::uuid()->toString()), Select::make('moneda_id')->label('Moneda')->options(fn (): array => app(OperacionCajaQuery::class)->monedasTurno($this->actorId()))->required(),
            TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), Textarea::make('concepto')->label('Motivo')->minLength(10)->maxLength(1000)->required(), TextInput::make('referencia')->label('Referencia del respaldo')->maxLength(150),
        ])->action(function (array $data) use ($tipo): void {
            /** @var array{clave:string,moneda_id:int,importe:float,concepto:string,referencia?:string|null} $data */
            $this->operar(fn () => app(GestionarCaja::class)->movimiento($this->actorId(), $this->sesionId(), (int) $data['moneda_id'], $tipo, (float) $data['importe'], $data['concepto'], $data['clave'], $data['referencia'] ?? null), 'Movimiento registrado');
        });
    }
}
