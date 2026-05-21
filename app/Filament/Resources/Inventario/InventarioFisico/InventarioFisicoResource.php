<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico;

use App\Filament\Resources\Inventario\InventarioFisico\Pages\CreateInventarioFisico;
use App\Filament\Resources\Inventario\InventarioFisico\Pages\EditInventarioFisico;
use App\Filament\Resources\Inventario\InventarioFisico\Pages\ListInventariosFisicos;
use App\Filament\Resources\Inventario\InventarioFisico\Pages\ViewInventarioFisico;
use App\Filament\Resources\Inventario\InventarioFisico\Schemas\InventarioFisicoForm;
use App\Filament\Resources\Inventario\InventarioFisico\Tables\InventarioFisicoTable;
use App\Models\Inventario\InventarioFisico;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InventarioFisicoResource extends Resource
{
    protected static ?string $model = InventarioFisico::class;

    protected static ?string $slug = 'inventario/tomas-fisicas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Tomas de Inventario';

    protected static ?string $modelLabel = 'Toma de Inventario';

    protected static ?string $pluralModelLabel = 'Tomas de Inventario';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return InventarioFisicoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventarioFisicoTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventariosFisicos::route('/'),
            'create' => CreateInventarioFisico::route('/create'),
            'view' => ViewInventarioFisico::route('/{record}'),
            'edit' => EditInventarioFisico::route('/{record}/edit'),
        ];
    }
}
