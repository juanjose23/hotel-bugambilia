<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaResource;

use App\Enums\Facturacion\EstadoFactura;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Facturacion\AnularFacturaFiscal;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Personas\Persona;
use App\Support\MonedaHelper;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class FacturaResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?string $navigationLabel = 'Facturas';

    protected static ?string $modelLabel = 'Factura';

    protected static ?string $pluralModelLabel = 'Facturas';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'facturacion/facturas';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Datos Fiscales DGI')
                ->icon('heroicon-o-document-currency-dollar')
                ->columns(4)
                ->schema([
                    TextEntry::make('numero')
                        ->label('N° de Factura')
                        ->weight(FontWeight::Bold)
                        ->copyable(),
                    TextEntry::make('serie.codigo')
                        ->label('Serie DGI')
                        ->badge()
                        ->color('info'),
                    TextEntry::make('tipo')
                        ->label('Tipo de Factura')
                        ->badge(),
                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge(),
                    TextEntry::make('numero_autorizacion_dgi')
                        ->label('N° Autorización DGI')
                        ->placeholder('DGI-AUT-OFICIAL'),
                    TextEntry::make('fecha_emision')
                        ->label('Fecha de Emisión')
                        ->dateTime('d/m/Y H:i:s'),
                    TextEntry::make('moneda.codigo')
                        ->label('Moneda Factura')
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('tasa_cambio')
                        ->label('Tasa de Cambio')
                        ->state(fn (Factura $record): string => $record->tasa_cambio ? MonedaHelper::simbolo().' '.number_format((float) $record->tasa_cambio, 4) : '1.0000'),
                ]),

            Section::make('Cliente y Receptor Fiscal')
                ->icon('heroicon-o-building-office')
                ->columns(3)
                ->schema([
                    TextEntry::make('cliente.nombre_completo')
                        ->label('Razón Social / Nombre')
                        ->placeholder('Consumidor Final'),
                    TextEntry::make('cliente.persona.identificacion')
                        ->label('RUC / Cédula / Pasaporte')
                        ->placeholder('—'),
                    TextEntry::make('cliente.persona.telefono')
                        ->label('Teléfono')
                        ->placeholder('—'),
                ]),

            Section::make('Desglose Tributario Oficial')
                ->icon('heroicon-o-calculator')
                ->columns(4)
                ->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal Gravado')
                        ->money(fn (Factura $record): string => MonedaHelper::codigo($record->moneda)),
                    TextEntry::make('iva_total')
                        ->label('IVA (15%)')
                        ->money(fn (Factura $record): string => MonedaHelper::codigo($record->moneda)),
                    TextEntry::make('descuento_total')
                        ->label('Descuentos')
                        ->money(fn (Factura $record): string => MonedaHelper::codigo($record->moneda)),
                    TextEntry::make('total')
                        ->label('Total Facturado')
                        ->weight(FontWeight::Bold)
                        ->money(fn (Factura $record): string => MonedaHelper::codigo($record->moneda)),
                ]),

            Section::make('Detalle de Ítems Facturados')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    TextEntry::make('detalles')
                        ->hiddenLabel()
                        ->html()
                        ->state(function (Factura $record): string {
                            if ($record->detalles->isEmpty()) {
                                return "<span class='text-gray-500 italic text-sm'>Sin ítems facturados.</span>";
                            }

                            $html = "<div class='overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg'><table class='w-full text-sm text-left'><thead class='bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-700 dark:text-gray-300'><tr><th class='p-3'>Concepto</th><th class='p-3 text-center'>Cant.</th><th class='p-3 text-right'>Precio Unit.</th><th class='p-3 text-right'>IVA (15%)</th><th class='p-3 text-right font-bold'>Total</th></tr></thead><tbody class='divide-y divide-gray-100 dark:divide-gray-700'>";

                            foreach ($record->detalles as $detalle) {
                                /** @var FacturaDetalle $detalle */
                                $precioUnitario = MonedaHelper::formatear((float) $detalle->precio_unitario, $record->moneda);
                                $iva = MonedaHelper::formatear((float) $detalle->iva, $record->moneda);
                                $totalLinea = MonedaHelper::formatear((float) $detalle->total_linea, $record->moneda);
                                $html .= "<tr><td class='p-3 font-medium'>{$detalle->concepto}</td><td class='p-3 text-center'>{$detalle->cantidad}</td><td class='p-3 text-right'>{$precioUnitario}</td><td class='p-3 text-right text-gray-500'>{$iva}</td><td class='p-3 text-right font-bold'>{$totalLinea}</td></tr>";
                            }

                            $html .= '</tbody></table></div>';

                            return $html;
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Origen y Trazabilidad')
                ->icon('heroicon-o-link')
                ->columns(3)
                ->schema([
                    TextEntry::make('venta.numero_venta')
                        ->label('Venta Asociada')
                        ->placeholder('—')
                        ->copyable(),
                    TextEntry::make('cuenta.numero_cuenta')
                        ->label('Cuenta / Folio')
                        ->placeholder('—')
                        ->copyable(),
                    TextEntry::make('emisor.name')
                        ->label('Emitida por')
                        ->placeholder('Sistema / Recepción'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['cliente.persona', 'serie', 'moneda', 'detalles', 'venta', 'cuenta']))
            ->defaultSort('fecha_emision', 'desc')
            ->columns([
                TextColumn::make('numero')->label('Factura')->searchable()->sortable()->copyable()->weight(FontWeight::Bold),
                TextColumn::make('serie.codigo')->label('Serie')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tipo')->label('Tipo')->badge(),
                TextColumn::make('venta.numero_venta')->label('Venta')->searchable()->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cuenta.numero_cuenta')->label('Cuenta')->searchable()->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('cliente.persona', fn (Builder $personaQuery): Builder => Persona::filtrarPorNombre($personaQuery, $search)))
                    ->placeholder('-'),
                TextColumn::make('moneda.codigo')->label('Moneda')->toggleable(isToggledHiddenByDefault: true),
                MontoMonedaColumn::make('iva_total')->label('IVA')->toggleable(isToggledHiddenByDefault: true),
                MontoMonedaColumn::make('total'),
                EstadoBadgeColumn::make(EstadoFactura::class),
                TextColumn::make('fecha_emision')->dateTime()->sortable(),
            ])
            ->filters([
                FiltroEstado::make(EstadoFactura::class),
                SelectFilter::make('factura_serie_id')->relationship('serie', 'codigo')->label('Serie'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('imprimir')
                        ->label('Imprimir Factura')
                        ->icon(Heroicon::Printer)
                        ->color('success')
                        ->url(fn (Factura $record): string => route('admin.facturacion.facturas.imprimir', $record))
                        ->openUrlInNewTab(),
                    Action::make('descargar_pdf')
                        ->label('Descargar PDF')
                        ->icon(Heroicon::ArrowDownTray)
                        ->color('gray')
                        ->url(fn (Factura $record): string => route('admin.facturacion.facturas.pdf', $record))
                        ->openUrlInNewTab(),
                    Action::make('anular')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->visible(fn (Factura $record): bool => $record->estado === EstadoFactura::Emitida)
                        ->schema([
                            Textarea::make('motivo')->required()->rows(3),
                        ])
                        ->action(function (Factura $record, array $data): Factura {
                            $usuarioId = auth()->id();

                            return app(AnularFacturaFiscal::class)
                                ->ejecutar($record, (string) $data['motivo'], is_int($usuarioId) ? $usuarioId : null);
                        }),
                ])->icon(Heroicon::EllipsisVertical),
            ])
            ->recordUrl(fn (Factura $record): string => self::getUrl('view', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'cliente.persona.personaNatural',
                'cliente.persona.personaJuridica',
                'serie',
                'moneda',
                'detalles',
                'venta',
                'cuenta',
                'emisor',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturas::route('/'),
            'view' => Pages\ViewFactura::route('/{record}'),
        ];
    }
}
