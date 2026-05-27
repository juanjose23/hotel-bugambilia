<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\PrefijoCodigo;

use App\Filament\Resources\Activos\PrefijoCodigo\Pages\ListPrefijoCodigos;
use App\Filament\Resources\Activos\PrefijoCodigo\Schemas\PrefijoCodigoForm;
use App\Filament\Resources\Activos\PrefijoCodigo\Tables\PrefijoCodigoTable;
use App\Models\Activos\PrefijoCodigo;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrefijoCodigoResource extends Resource
{
    protected static ?string $model = PrefijoCodigo::class;

    protected static ?string $slug = 'activos/prefijos';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::BookmarkSquare;

    protected static \UnitEnum|string|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Prefijos de Códigos';

    protected static ?string $modelLabel = 'Prefijo de Código';

    protected static ?string $pluralModelLabel = 'Prefijos de Códigos';

    public static function form(Schema $schema): Schema
    {
        return PrefijoCodigoForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return PrefijoCodigoTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrefijoCodigos::route('/'),
        ];
    }
}
