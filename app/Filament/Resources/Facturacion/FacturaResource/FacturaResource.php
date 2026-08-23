<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaResource;

use App\Enums\Facturacion\EstadoFactura;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Filters\FiltroEstado;
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

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['cliente.persona', 'serie', 'moneda']))
            ->defaultSort('fecha_emision', 'desc')
            ->columns([
                TextColumn::make('numero')->label('Factura')->searchable()->sortable()->copyable()->weight('bold'),
                TextColumn::make('serie.codigo')->label('Serie'),
                TextColumn::make('venta.numero_venta')->label('Venta')->searchable()->placeholder('-'),
                TextColumn::make('cuenta.numero_cuenta')->label('Cuenta')->searchable()->placeholder('-'),
                TextColumn::make('cliente.nombre_completo')->label('Cliente')->searchable()->placeholder('-'),
                TextColumn::make('moneda.codigo')->label('Moneda'),
                MontoMonedaColumn::make('iva_total')->label('IVA'),
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
