<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote;

use App\Filament\Resources\Inventario\Lote\Pages\ListLotes;
use App\Filament\Resources\Inventario\Lote\Pages\ViewLote;
use App\Filament\Resources\Inventario\Lote\RelationManagers\MovimientosRelationManager;
use App\Filament\Resources\Inventario\Lote\Tables\LoteTable;
use App\Filament\Resources\Inventario\Lote\Widgets\LoteStatsOverview;
use App\Models\Inventario\Lote;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LoteResource extends Resource
{
    protected static ?string $model = Lote::class;

    protected static ?string $slug = 'inventario/lotes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Lote')
                    ->icon(Heroicon::Cube)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('codigo_lote')->label('Código'),
                        TextEntry::make('producto.nombre')->label('Producto'),
                        TextEntry::make('variante.nombre_variante')->label('Variante')->placeholder('Sin variante / Única'),
                        TextEntry::make('estado')->badge(),
                        TextEntry::make('cantidad_disponible')->label('Disponible')->numeric(decimalPlaces: 2),
                        TextEntry::make('ubicacion.nombre')->label('Ubicación'),
                        TextEntry::make('fecha_vencimiento')->label('Vence')->date('d/m/Y'),
                        TextEntry::make('fecha_recepcion')->label('Recibido')->date('d/m/Y'),
                        TextEntry::make('lote_proveedor')->label('Lote Proveedor')->placeholder('No definido'),
                    ]),

                Section::make('Otros productos en el mismo lote / recepción')
                    ->description('Productos que ingresaron al almacén bajo el mismo lote del proveedor o en el mismo documento de recepción.')
                    ->icon(Heroicon::ArchiveBox)
                    ->schema([
                        RepeatableEntry::make('siblingLotes')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('codigo_lote')->label('Lote interno')->weight('bold'),
                                TextEntry::make('producto.nombre')->label('Producto'),
                                TextEntry::make('cantidad_disponible')->label('Stock Disponible')->numeric(decimalPlaces: 2),
                                TextEntry::make('estado')->badge(),
                            ])
                            ->columns(4),
                    ])
                    ->visible(fn (Lote $record) => $record->siblingLotes->isNotEmpty()),

            ]);
    }

    public static function table(Table $table): Table
    {
        return LoteTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovimientosRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LoteStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLotes::route('/'),
            'view' => ViewLote::route('/{record}'),
        ];
    }
}
