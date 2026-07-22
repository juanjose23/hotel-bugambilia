<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource;

use App\Filament\Resources\Inventario\PackResource\Pages\CreatePack;
use App\Filament\Resources\Inventario\PackResource\Pages\EditPack;
use App\Filament\Resources\Inventario\PackResource\Pages\ListPacks;
use App\Filament\Resources\Inventario\PackResource\Pages\ViewPack;
use App\Filament\Resources\Inventario\PackResource\Schemas\PackForm;
use App\Filament\Resources\Inventario\PackResource\Tables\PackTable;
use App\Repository\Models\Catalogos\Producto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PackResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $slug = 'inventario/packs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Gift;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Packs / Kits';

    protected static ?string $modelLabel = 'Pack';

    protected static ?string $pluralModelLabel = 'Packs / Kits';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return PackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PackTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->has('kitItems')
            ->withCount('kitItems');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPacks::route('/'),
            'create' => CreatePack::route('/create'),
            'view' => ViewPack::route('/{record}'),
            'edit' => EditPack::route('/{record}/edit'),
        ];
    }
}
