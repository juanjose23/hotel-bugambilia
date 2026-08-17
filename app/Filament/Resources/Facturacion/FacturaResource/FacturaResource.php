<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaResource;

use App\Enums\Facturacion\EstadoFactura;
use App\Interactors\Facturacion\AnularFacturaFiscal;
use App\Repository\Models\Facturacion\Factura;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class FacturaResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Facturación & Finanzas';

    protected static ?string $navigationLabel = 'Facturas';

    protected static ?string $modelLabel = 'Factura';

    protected static ?string $pluralModelLabel = 'Facturas';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'facturacion/facturas';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_emision', 'desc')
            ->columns([
                TextColumn::make('numero')->label('Factura')->searchable()->sortable()->copyable()->weight('bold'),
                TextColumn::make('serie.codigo')->label('Serie'),
                TextColumn::make('venta.numero_venta')->label('Venta')->searchable()->placeholder('-'),
                TextColumn::make('cuenta.numero_cuenta')->label('Cuenta')->searchable()->placeholder('-'),
                TextColumn::make('cliente.nombre_completo')->label('Cliente')->searchable()->placeholder('-'),
                TextColumn::make('moneda.codigo')->label('Moneda'),
                TextColumn::make('iva_total')->label('IVA')->money('NIO')->sortable(),
                TextColumn::make('total')->money('NIO')->sortable(),
                TextColumn::make('estado')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EstadoFactura ? $state->getLabel() : '')
                    ->color(fn (mixed $state): string => $state instanceof EstadoFactura ? $state->getColor() : 'gray'),
                TextColumn::make('fecha_emision')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoFactura::class),
                SelectFilter::make('factura_serie_id')->relationship('serie', 'codigo')->label('Serie'),
            ])
            ->recordActions([
                ActionGroup::make([
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturas::route('/'),
        ];
    }
}
