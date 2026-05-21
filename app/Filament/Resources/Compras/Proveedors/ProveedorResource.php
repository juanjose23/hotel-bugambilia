<?php

namespace App\Filament\Resources\Compras\Proveedors;

use App\Filament\Resources\Compras\Proveedors\Pages\CreateProveedor;
use App\Filament\Resources\Compras\Proveedors\Pages\EditProveedor;
use App\Filament\Resources\Compras\Proveedors\Pages\ListProveedors;
use App\Filament\Resources\Compras\Proveedors\Pages\ViewProveedor;
use App\Filament\Resources\Compras\Proveedors\Schemas\ProveedorForm;
use App\Filament\Resources\Compras\Proveedors\Schemas\ProveedorInfolist;
use App\Filament\Resources\Compras\Proveedors\Tables\ProveedorTable;
use App\Models\Compras\Proveedor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?string $slug = 'compras/proveedores';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static ?string $recordTitleAttribute = 'codigo';

    public static function form(Schema $schema): Schema
    {
        return ProveedorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProveedorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProveedorTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProveedorContactosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProveedors::route('/'),
            'create' => CreateProveedor::route('/create'),
            'view' => ViewProveedor::route('/{record}'),
            'edit' => EditProveedor::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'persona',
                'tipoProveedor',
                'contactoPrincipal',
            ]);
    }
}
