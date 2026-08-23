<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaFolioResource;

use App\Enums\Facturacion\EstadoFolioFactura;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Facturacion\FacturaFolio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class FacturaFolioResource extends Resource
{
    protected static ?string $model = FacturaFolio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Folios fiscales';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'facturacion/folios';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('numero')->searchable()->sortable()->copyable()->weight('bold'),
                TextColumn::make('serie.codigo')->label('Serie'),
                TextColumn::make('numero_correlativo')->label('Correlativo')->numeric()->sortable(),
                TextColumn::make('factura.numero')->label('Factura')->placeholder('-'),
                EstadoBadgeColumn::make(EstadoFolioFactura::class),
                TextColumn::make('reservado_at')->dateTime()->sortable(),
                TextColumn::make('emitido_at')->dateTime()->placeholder('-'),
                TextColumn::make('motivo')->limit(40)->placeholder('-'),
            ])
            ->filters([
                FiltroEstado::make(EstadoFolioFactura::class),
                SelectFilter::make('factura_serie_id')->relationship('serie', 'codigo')->label('Serie'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturaFolios::route('/'),
        ];
    }
}
