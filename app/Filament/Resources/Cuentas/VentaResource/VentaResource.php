<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource;

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\TipoFactura;
use App\Filament\Resources\Cuentas\VentaResource\Schemas\VentaDirectaForm;
use App\Filament\Resources\Cuentas\VentaResource\Widgets\VentaStatsOverview;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Facturacion\EmitirFacturaDesdeVenta;
use App\Interactors\Ventas\AnularVenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Personas\Persona;
use App\Support\MonedaHelper;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'ventas';

    public static function form(Schema $schema): Schema
    {
        return VentaDirectaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Identificación de la Venta')
                ->icon('heroicon-o-document-check')
                ->columns(3)
                ->schema([
                    TextEntry::make('numero_venta')
                        ->label('N° de Venta')
                        ->weight(FontWeight::Bold)
                        ->copyable(),
                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge(),
                    TextEntry::make('moneda.codigo')
                        ->label('Moneda')
                        ->badge()
                        ->color('info'),
                    TextEntry::make('cuenta.numero_cuenta')
                        ->label('Cuenta Origen')
                        ->placeholder('Venta Directa')
                        ->copyable(),
                    TextEntry::make('created_at')
                        ->label('Fecha de Emisión')
                        ->dateTime('d/m/Y H:i:s'),
                    TextEntry::make('creador.name')
                        ->label('Registrada por')
                        ->placeholder('Sistema / Recepción'),
                ]),

            Section::make('Cliente y Datos Fiscales')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('cliente.nombre_completo')
                        ->label('Cliente')
                        ->placeholder('Consumidor Final'),
                    TextEntry::make('cliente.persona.identificacion')
                        ->label('Identificación / RUC')
                        ->placeholder('—'),
                    TextEntry::make('cliente.persona.telefono')
                        ->label('Teléfono')
                        ->placeholder('—'),
                ]),

            Section::make('Totales y Liquidación')
                ->icon('heroicon-o-calculator')
                ->columns(4)
                ->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal Gravado')
                        ->money(fn (Venta $record): string => MonedaHelper::codigo($record->moneda)),
                    TextEntry::make('impuesto_total')
                        ->label('IVA (15%)')
                        ->money(fn (Venta $record): string => MonedaHelper::codigo($record->moneda)),
                    TextEntry::make('descuento_total')
                        ->label('Descuentos')
                        ->money(fn (Venta $record): string => MonedaHelper::codigo($record->moneda)),
                    TextEntry::make('total')
                        ->label('Total Venta')
                        ->weight(FontWeight::Bold)
                        ->money(fn (Venta $record): string => MonedaHelper::codigo($record->moneda)),
                ]),

            Section::make('Ítems y Consumos Facturados')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    TextEntry::make('detalles')
                        ->hiddenLabel()
                        ->html()
                        ->state(function (Venta $record): string {
                            if ($record->detalles->isEmpty()) {
                                return "<span class='text-gray-500 italic text-sm'>Sin renglones registrados.</span>";
                            }

                            $html = "<div class='overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg'><table class='w-full text-sm text-left'><thead class='bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-700 dark:text-gray-300'><tr><th class='p-3'>Concepto</th><th class='p-3 text-center'>Cantidad</th><th class='p-3 text-right'>Precio Unit.</th><th class='p-3 text-right'>IVA</th><th class='p-3 text-right font-bold'>Total Línea</th></tr></thead><tbody class='divide-y divide-gray-100 dark:divide-gray-700'>";

                            foreach ($record->detalles as $detalle) {
                                /** @var VentaDetalle $detalle */
                                $precioUnitario = MonedaHelper::formatear((float) $detalle->precio_unitario, $record->moneda);
                                $impuesto = MonedaHelper::formatear((float) $detalle->impuesto, $record->moneda);
                                $totalLinea = MonedaHelper::formatear((float) $detalle->total_linea, $record->moneda);
                                $html .= "<tr><td class='p-3 font-medium'>{$detalle->concepto}</td><td class='p-3 text-center'>{$detalle->cantidad}</td><td class='p-3 text-right'>{$precioUnitario}</td><td class='p-3 text-right text-gray-500'>{$impuesto}</td><td class='p-3 text-right font-bold'>{$totalLinea}</td></tr>";
                            }

                            $html .= '</tbody></table></div>';

                            return $html;
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Pagos y Transacciones')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    TextEntry::make('pagos')
                        ->hiddenLabel()
                        ->html()
                        ->state(function (Venta $record): string {
                            if ($record->transaccionesPasarela->isEmpty()) {
                                return "<span class='text-gray-500 italic text-sm'>Venta sin transacciones de pasarela registradas (liquidada en cuenta / mostrador).</span>";
                            }

                            $html = "<div class='overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg'><table class='w-full text-sm text-left'><thead class='bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-700 dark:text-gray-300'><tr><th class='p-3'>Fecha</th><th class='p-3'>Método / Pasarela</th><th class='p-3'>Referencia</th><th class='p-3 text-right'>Monto</th></tr></thead><tbody class='divide-y divide-gray-100 dark:divide-gray-700'>";

                            foreach ($record->transaccionesPasarela as $pago) {
                                /** @var PagoTransaccion $pago */
                                $fecha = $pago->created_at?->format('d/m/Y H:i') ?? '-';
                                $metodo = $pago->metodo_pago ?? 'Directo';
                                $ref = $pago->referencia_externa ?? '-';
                                $montoFormateado = MonedaHelper::formatear((float) $pago->monto, $pago->moneda ?? $record->moneda);
                                $html .= "<tr><td class='p-3'>{$fecha}</td><td class='p-3 font-medium'>{$metodo}</td><td class='p-3 text-gray-500'>{$ref}</td><td class='p-3 text-right font-bold text-success-600'>{$montoFormateado}</td></tr>";
                            }

                            $html .= '</tbody></table></div>';

                            return $html;
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['cliente.persona', 'moneda', 'detalles', 'transaccionesPasarela', 'cuenta', 'facturas']))
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('estado')
                    ->label('Estado')
                    ->getTitleFromRecordUsing(fn (Venta $record): string => $record->estado->getLabel()),
                Group::make('moneda.codigo')
                    ->label('Moneda'),
                Group::make('created_at')
                    ->label('Fecha de Emisión')
                    ->date(),
                Group::make('cliente.nombre_completo')
                    ->label('Cliente'),
            ])
            ->columns([
                TextColumn::make('numero_venta')
                    ->label('N° Venta')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Venta $record): string => $record->cuenta?->numero_cuenta ? "Cuenta: {$record->cuenta->numero_cuenta}" : 'Venta Directa'),

                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('cliente.persona', fn (Builder $personaQuery): Builder => Persona::filtrarPorNombre($personaQuery, $search)))
                    ->description(function (Venta $record): ?string {
                        $persona = $record->cliente?->persona;
                        if (! $persona) {
                            return null;
                        }

                        if ($persona->personaNatural !== null) {
                            return $persona->personaNatural->numero_identificacion;
                        }

                        return $persona->personaJuridica?->numero_identificacion;
                    })
                    ->placeholder('Consumidor Final'),

                TextColumn::make('moneda.codigo')
                    ->label('Moneda')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                MontoMonedaColumn::make('subtotal')
                    ->label('Subtotal')
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                            ->label('Subtotal')
                            ->formatStateUsing(fn ($state): string => MonedaHelper::formatear((float) $state)),
                    ]),

                MontoMonedaColumn::make('impuesto_total')
                    ->label('IVA')
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                            ->label('IVA')
                            ->formatStateUsing(fn ($state): string => MonedaHelper::formatear((float) $state)),
                    ]),

                MontoMonedaColumn::make('total')
                    ->label('Total')
                    ->weight(FontWeight::Bold)
                    ->summarize([
                        Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state): string => MonedaHelper::formatear((float) $state)),
                    ]),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->icon(fn (EstadoVenta $state): string => match ($state) {
                        EstadoVenta::Emitida => 'heroicon-m-check-circle',
                        EstadoVenta::Anulada => 'heroicon-m-x-circle',
                        EstadoVenta::NotaCredito => 'heroicon-m-exclamation-circle',
                    })
                    ->color(fn (EstadoVenta $state): string => $state->getColor())
                    ->formatStateUsing(fn (EstadoVenta $state): string => $state->getLabel()),

                FechaStandardColumn::make('created_at', 'Emitida')
                    ->toggleable(),
            ])
            ->filters([
                FiltroEstado::make(EstadoVenta::class),

                SelectFilter::make('moneda_id')
                    ->label('Moneda')
                    ->relationship('moneda', 'codigo')
                    ->preload(),

                Filter::make('rango_fecha')
                    ->label('Rango de Emisión')
                    ->schema([
                        DatePicker::make('desde')->label('Emitida Desde'),
                        DatePicker::make('hasta')->label('Emitida Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '>=', $d))
                        ->when($data['hasta'] ?? null, fn (Builder $q, string $h): Builder => $q->whereDate('created_at', '<=', $h))),

                Filter::make('con_factura_fiscal')
                    ->label('Con Factura Fiscal DGI')
                    ->query(fn (Builder $query): Builder => $query->whereHas('facturas', fn (Builder $q): Builder => $q->where('estado', EstadoFactura::Emitida))),

                Filter::make('sin_factura_fiscal')
                    ->label('Sin Factura Fiscal DGI')
                    ->query(fn (Builder $query): Builder => $query->where('estado', EstadoVenta::Emitida)->whereDoesntHave('facturas', fn (Builder $q): Builder => $q->where('estado', EstadoFactura::Emitida))),
            ])
            ->recordUrl(fn (Venta $record): string => self::getUrl('view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('emitir_factura')
                        ->label('Emitir Factura')
                        ->icon(Heroicon::DocumentCurrencyDollar)
                        ->color('success')
                        ->visible(function (Venta $record): bool {
                            if ($record->estado !== EstadoVenta::Emitida) {
                                return false;
                            }
                            $record->loadMissing('facturas');

                            return $record->facturas->where('estado', EstadoFactura::Emitida)->isEmpty();
                        })
                        ->modalHeading('Emitir Factura Fiscal DGI')
                        ->schema([
                            Select::make('factura_serie_id')
                                ->label('Serie Fiscal DGI')
                                ->options(fn (): array => FacturaSerie::query()->where('activa', true)->pluck('codigo', 'id')->all())
                                ->required(),
                            Select::make('tipo')
                                ->label('Tipo de Factura')
                                ->options([
                                    TipoFactura::Contado->value => TipoFactura::Contado->getLabel(),
                                    TipoFactura::Credito->value => TipoFactura::Credito->getLabel(),
                                ])
                                ->default(TipoFactura::Contado->value)
                                ->required(),
                        ])
                        ->action(function (Venta $record, array $data): void {
                            $usuarioId = auth()->id();
                            try {
                                $factura = app(EmitirFacturaDesdeVenta::class)->ejecutar(
                                    venta: $record,
                                    serieId: (int) $data['factura_serie_id'],
                                    tipo: TipoFactura::from((int) $data['tipo']),
                                    datosReceptor: [],
                                    usuarioId: is_int($usuarioId) ? $usuarioId : null,
                                );

                                Notification::make()
                                    ->title('Factura Emitida')
                                    ->body("Se ha emitido la factura N° {$factura->numero}.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('imprimir_factura')
                        ->label('Imprimir Factura')
                        ->icon(Heroicon::Printer)
                        ->color('success')
                        ->visible(function (Venta $record): bool {
                            $record->loadMissing('facturas');

                            return $record->facturas->contains(fn ($f): bool => $f->estado === EstadoFactura::Emitida);
                        })
                        ->url(function (Venta $record): string {
                            $record->loadMissing('facturas');
                            $factura = $record->facturas->first(fn ($f): bool => $f->estado === EstadoFactura::Emitida);

                            return $factura ? route('admin.facturacion.facturas.imprimir', $factura) : '#';
                        })
                        ->openUrlInNewTab(),

                    Action::make('anular')
                        ->label('Anular Venta')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->visible(fn (Venta $record): bool => $record->estado === EstadoVenta::Emitida)
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('motivo')->label('Motivo')->required()->rows(3),
                        ])
                        ->action(function (Venta $record, array $data): void {
                            $usuarioId = auth()->id();
                            try {
                                app(AnularVenta::class)->ejecutar($record, (string) $data['motivo'], is_int($usuarioId) ? $usuarioId : null);
                                Notification::make()->title('Venta Anulada')->warning()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                            }
                        }),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Opciones'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'cliente.persona.personaNatural',
                'cliente.persona.personaJuridica',
                'moneda',
                'detalles',
                'transaccionesPasarela',
                'cuenta',
                'facturas',
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            VentaStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/crear'),
            'view' => Pages\ViewVenta::route('/{record}'),
        ];
    }
}
