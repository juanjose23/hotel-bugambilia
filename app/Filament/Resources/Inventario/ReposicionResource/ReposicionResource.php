<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ReposicionResource;

use App\Filament\Resources\Inventario\ReposicionResource\Pages\ListReposiciones;
use App\Filament\Resources\Inventario\ReposicionResource\Pages\ViewReposicion;
use App\Filament\Resources\Inventario\ReposicionResource\Schemas\ReposicionForm;
use App\Filament\Resources\Inventario\ReposicionResource\Tables\ReposicionTable;
use App\Models\Inventario\Reposicion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReposicionResource extends Resource
{
    protected static ?string $model = Reposicion::class;

    protected static ?string $slug = 'inventario/reposiciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Órdenes de Reposición';

    protected static ?string $modelLabel = 'Reposición';

    protected static ?string $pluralModelLabel = 'Órdenes de Reposición';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return ReposicionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReposicionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReposiciones::route('/'),
            'view' => ViewReposicion::route('/{record}'),
        ];
    }
}
