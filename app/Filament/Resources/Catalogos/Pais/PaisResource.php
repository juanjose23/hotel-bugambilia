<?php

namespace App\Filament\Resources\Catalogos\Pais;

use App\Filament\Resources\Catalogos\Pais\Pages\ListPais;
use App\Filament\Resources\Catalogos\Pais\Schemas\PaisForm;
use App\Filament\Resources\Catalogos\Pais\Schemas\PaisInfolist;
use App\Filament\Resources\Catalogos\Pais\Tables\PaisTable;
use App\Models\Catalogos\Pais;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PaisResource extends Resource
{
    protected static ?string $model = Pais::class;
    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Catálogos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::GlobeAmericas;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return PaisForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaisInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPais::route('/'),
        ];
    }
}
