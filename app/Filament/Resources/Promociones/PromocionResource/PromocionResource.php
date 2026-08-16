<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource;

use App\Filament\Resources\Promociones\PromocionResource\Pages\CreatePromocion;
use App\Filament\Resources\Promociones\PromocionResource\Pages\EditPromocion;
use App\Filament\Resources\Promociones\PromocionResource\Pages\ListPromociones;
use App\Filament\Resources\Promociones\PromocionResource\Pages\ViewPromocion;
use App\Filament\Resources\Promociones\PromocionResource\Schemas\PromocionForm;
use App\Filament\Resources\Promociones\PromocionResource\Schemas\PromocionInfolist;
use App\Filament\Resources\Promociones\PromocionResource\Tables\PromocionTable;
use App\Filament\Shared\RelationManagers\PoliticasRelationManager;
use App\Filament\Shared\RelationManagers\StocksRelationManager;
use App\Repository\Models\Promociones\Promocion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PromocionResource extends Resource
{
    protected static ?string $model = Promocion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Gift;

    protected static ?string $slug = 'promociones/promociones';

    protected static string|UnitEnum|null $navigationGroup = 'Servicios & Promociones';

    protected static ?string $navigationLabel = 'Promociones';

    protected static ?string $modelLabel = 'Promoción';

    protected static ?string $pluralModelLabel = 'Promociones';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PromocionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PromocionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromocionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StocksRelationManager::class,
            PoliticasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromociones::route('/'),
            'create' => CreatePromocion::route('/create'),
            'view' => ViewPromocion::route('/{record}'),
            'edit' => EditPromocion::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
