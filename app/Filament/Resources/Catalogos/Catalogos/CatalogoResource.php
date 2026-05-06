<?php

namespace App\Filament\Resources\Catalogos\Catalogos;

use App\Filament\Resources\Catalogos\Catalogos\Pages\EditCatalogo;
use App\Filament\Resources\Catalogos\Catalogos\Pages\ListCatalogos;
use App\Filament\Resources\Catalogos\Catalogos\Pages\ViewCatalogo;
use App\Filament\Resources\Catalogos\Catalogos\Schemas\CatalogoForm;
use App\Filament\Resources\Catalogos\Catalogos\Schemas\CatalogoInfolist;
use App\Filament\Resources\Catalogos\Catalogos\Tables\CatalogosTable;
use App\Models\Catalogos\Catalogo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CatalogoResource extends Resource
{
    protected static ?string $model = Catalogo::class;


    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Catálogos';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::BookmarkSquare;
    }

    protected static ?string $modelLabel = 'Catálogos';
    protected static ?string $pluralModelLabel = 'Catálogos';

    public static function form(Schema $schema): Schema
    {
        return CatalogoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatalogoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogosTable::configure($table);
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
            'index' => ListCatalogos::route('/'),
        ];
    }


    /** @return Builder<Catalogo> */
    public static function getEloquentQuery(): Builder
    {
        return Catalogo::query()->orderByDesc('id');
    }
}
