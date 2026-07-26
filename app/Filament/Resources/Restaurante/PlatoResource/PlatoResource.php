<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Filament\Resources\Restaurante\PlatoResource\RelationManagers\RecetaRelationManager;
use App\Filament\Resources\Restaurante\PlatoResource\Schemas\PlatoForm;
use App\Filament\Resources\Restaurante\PlatoResource\Schemas\PlatoInfolist;
use App\Filament\Resources\Restaurante\PlatoResource\Tables\PlatoTable;
use App\Filament\Shared\RelationManagers\PoliticasRelationManager;
use App\Filament\Shared\RelationManagers\PreciosRelationManager;
use App\Repository\Models\Restaurante\Plato;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class PlatoResource extends Resource
{
    protected static ?string $model = Plato::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Platos';

    protected static ?string $modelLabel = 'Plato';

    protected static ?string $pluralModelLabel = 'Platos';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'restaurante/platos';

    public static function form(Schema $schema): Schema
    {
        return PlatoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PlatoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlatoTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RecetaRelationManager::class,
            PreciosRelationManager::class,
            PoliticasRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatos::route('/'),
            'create' => Pages\CreatePlato::route('/create'),
            'edit' => Pages\EditPlato::route('/{record}/edit'),
            'view' => Pages\ViewPlato::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return app(VerificarRestauranteActivo::class)->estaActivo()
            && parent::shouldRegisterNavigation();
    }

    public static function canViewAny(): bool
    {
        return app(VerificarRestauranteActivo::class)->estaActivo()
            && parent::canViewAny();
    }
}
