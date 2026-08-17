<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Politicas;

use App\Filament\Resources\Catalogos\Politicas\Pages\CreatePoliticas;
use App\Filament\Resources\Catalogos\Politicas\Pages\EditPoliticas;
use App\Filament\Resources\Catalogos\Politicas\Pages\ListPoliticas;
use App\Filament\Resources\Catalogos\Politicas\Pages\ViewPoliticas;
use App\Filament\Resources\Catalogos\Politicas\Schemas\PoliticasForm;
use App\Filament\Resources\Catalogos\Politicas\Schemas\PoliticasInfolist;
use App\Filament\Resources\Catalogos\Politicas\Tables\PoliticasTable;
use App\Repository\Models\Politicas\Politica;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PoliticasResource extends Resource
{
    protected static ?string $model = Politica::class;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración & Auditoría';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsVertical;

    protected static ?int $navigationSort = 60;

    protected static ?string $recordTitleAttribute = 'titulo';

    protected static ?string $slug = 'catalogos/politicas';

    public static function form(Schema $schema): Schema
    {
        return PoliticasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PoliticasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PoliticasTable::configure($table);
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
            'index' => ListPoliticas::route('/'),
            'create' => CreatePoliticas::route('/create'),
            'view' => ViewPoliticas::route('/{record}'),
            'edit' => EditPoliticas::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
