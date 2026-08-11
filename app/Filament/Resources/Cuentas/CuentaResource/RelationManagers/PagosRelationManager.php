<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\RelationManagers;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Support\CachedOptions;
use App\Support\MonedaHelper;
use BackedEnum;
use DomainException;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

final class PagosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

    protected static ?string $title = 'Pagos y Abonos';

    protected static BackedEnum|string|null $icon = 'heroicon-m-banknotes';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('cuenta.moneda'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('forma_pago')
                    ->label('Forma de Pago')
                    ->badge(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),

                TextColumn::make('monto')
                    ->label('Monto aplicado')
                    ->money(fn ($record): string => MonedaHelper::codigo($record->cuenta?->moneda))
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('propina')
                    ->label('Propina')
                    ->money(fn ($record): string => MonedaHelper::codigo($record->cuenta?->moneda)),

                TextColumn::make('referencia_transaccion')
                    ->label('Referencia')
                    ->placeholder('—'),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->placeholder('—')
                    ->limit(40),

                TextColumn::make('usuario.name')
                    ->label('Recibido por')
                    ->placeholder('Sistema'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar Pago / Abono')
                    ->icon('heroicon-o-banknotes')
                    ->modalWidth('lg')
                    ->schema(function (): array {
                        /** @var Cuenta $cuenta */
                        $cuenta = $this->getOwnerRecord();
                        $cuenta->refresh();

                        $saldo = (float) $cuenta->saldo;
                        $total = (float) $cuenta->total;
                        $simbolo = MonedaHelper::simbolo($cuenta->moneda);

                        return [
                            TextEntry::make('_resumen')
                                ->hiddenLabel()
                                ->state(new HtmlString(
                                    "<div class='p-3 bg-gray-50 dark:bg-gray-800/60 rounded-lg text-sm flex justify-between font-medium border border-gray-200 dark:border-gray-700'>".
                                    "<span>Total cuenta: <strong>{$simbolo} ".number_format($total, 2).'</strong></span>'.
                                    "<span>Saldo pendiente: <strong class='text-emerald-600 dark:text-emerald-400'>{$simbolo} ".number_format($saldo, 2).'</strong></span>'.
                                    '</div>'
                                ))
                                ->columnSpanFull(),

                            Grid::make(2)->schema([
                                Select::make('forma_pago')
                                    ->label('Método de Pago')
                                    ->options(fn () => collect(MetodoPago::cases())
                                        ->filter(fn (MetodoPago $m) => $m !== MetodoPago::CARGO_HABITACION)
                                        ->mapWithKeys(fn (MetodoPago $m) => [$m->value => $m->getLabel()]))
                                    ->required()
                                    ->native(false)
                                    ->searchable(),

                                Select::make('moneda_pago_id')
                                    ->label('Moneda de Pago')
                                    ->options(fn () => CachedOptions::monedas())
                                    ->default(fn () => app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar()?->id)
                                    ->live()
                                    ->native(false),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('monto')
                                    ->label('Monto a Cobrar')
                                    ->numeric()
                                    ->prefix(fn (Get $get): string => $this->simboloMonedaPago($get))
                                    ->default($saldo > 0 ? $saldo : null)
                                    ->required()
                                    ->minValue(0.01)
                                    ->helperText(function (Get $get) use ($saldo, $simbolo): string {
                                        return "Saldo pendiente: {$simbolo} ".number_format($saldo, 2);
                                    }),

                                TextInput::make('propina')
                                    ->label('Propina Adicional')
                                    ->numeric()
                                    ->prefix(fn (Get $get): string => $this->simboloMonedaPago($get))
                                    ->default(0)
                                    ->minValue(0),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('referencia_transaccion')
                                    ->label('Referencia / Voucher')
                                    ->maxLength(100)
                                    ->placeholder('Nro. de comprobante…'),

                                TextInput::make('observaciones')
                                    ->label('Observaciones')
                                    ->maxLength(255),
                            ]),
                        ];
                    })
                    ->action(function (array $data): void {
                        try {
                            /** @var Cuenta $cuenta */
                            $cuenta = $this->getOwnerRecord();

                            /** @var int|null $userId */
                            $userId = auth()->id();

                            // Resolver ID de moneda de pago
                            $monedaPagoId = isset($data['moneda_pago_id']) && is_numeric($data['moneda_pago_id'])
                                ? (int) $data['moneda_pago_id']
                                : null;

                            $montoCuenta = $this->convertirAMonedaCuenta((float) $data['monto'], $monedaPagoId, $cuenta);
                            $propinaRaw = isset($data['propina']) && is_numeric($data['propina'])
                                ? (float) $data['propina']
                                : 0.0;
                            $propinaAplicada = $propinaRaw > 0
                                ? $this->convertirAMonedaCuenta($propinaRaw, $monedaPagoId, $cuenta)
                                : 0.0;

                            app(RegistrarPagoCuenta::class)->ejecutar(
                                cuenta: $cuenta,
                                metodoPago: MetodoPago::from((int) $data['forma_pago']),
                                monto: $montoCuenta,
                                propina: $propinaAplicada,
                                estado: EstadoPago::APLICADO,
                                referenciaTransaccion: $data['referencia_transaccion'] ?? null,
                                observaciones: $data['observaciones'] ?? null,
                                monedaId: $cuenta->moneda_id,
                                usuarioId: $userId,
                            );

                            $cuenta->refresh();
                            $saldoRestante = (float) $cuenta->saldo;
                            $simbolo = MonedaHelper::simbolo($cuenta->moneda);

                            Notification::make()
                                ->title($saldoRestante <= 0 ? 'Pago completo' : 'Abono registrado')
                                ->body($saldoRestante <= 0
                                    ? 'La cuenta ha sido saldada en su totalidad.'
                                    : "Saldo restante: {$simbolo} ".number_format($saldoRestante, 2))
                                ->success()
                                ->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('Error al registrar el pago')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([]);
    }

    // ─── Helpers de moneda ─────────────────────────────────────────────────

    private function simboloMonedaPago(Get $get): string
    {
        $monedaId = $get('moneda_pago_id');
        if (! is_numeric($monedaId)) {
            return MonedaHelper::simbolo();
        }

        $data = CachedOptions::monedasSimbolos()->get((int) $monedaId);

        return is_array($data) ? (string) $data['simbolo'] : MonedaHelper::simbolo();
    }

    private function convertirAMonedaCuenta(float $monto, ?int $monedaPagoId, Cuenta $cuenta): float
    {
        return app(ConvertirMoneda::class)->entre($monto, $monedaPagoId, $cuenta->moneda_id);
    }
}
