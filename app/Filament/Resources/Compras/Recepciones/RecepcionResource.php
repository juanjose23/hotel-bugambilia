<?php

namespace App\Filament\Resources\Compras\Recepciones;

use App\Filament\Resources\Compras\Recepciones\Pages\CreateRecepcion;
use App\Filament\Resources\Compras\Recepciones\Pages\EditRecepcion;
use App\Filament\Resources\Compras\Recepciones\Pages\ListRecepciones;
use App\Filament\Resources\Compras\Recepciones\Pages\ViewRecepcion;
use App\Filament\Resources\Compras\Recepciones\Schemas\RecepcionForm;
use App\Filament\Resources\Compras\Recepciones\Schemas\RecepcionInfolist;
use App\Filament\Resources\Compras\Recepciones\Tables\RecepcionTable;
use App\Models\Compras\RecepcionCompra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RecepcionResource extends Resource
{
    protected static ?string $model = RecepcionCompra::class;

    protected static ?string $slug = 'compras/recepciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Recepciones';

    protected static ?string $modelLabel = 'Recepción de Mercancía';

    protected static ?string $pluralModelLabel = 'Recepciones';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return RecepcionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecepcionTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RecepcionInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['ordenCompra', 'receptor']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecepciones::route('/'),
            'create' => CreateRecepcion::route('/create'),
            'view' => ViewRecepcion::route('/{record}'),
            'edit' => EditRecepcion::route('/{record}/edit'),
        ];
    }
}
