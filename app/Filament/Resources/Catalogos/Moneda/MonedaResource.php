<?php

namespace App\Filament\Resources\Catalogos\Moneda;

use App\Filament\Resources\Catalogos\Moneda\Pages\CreateMoneda;
use App\Filament\Resources\Catalogos\Moneda\Pages\EditMoneda;
use App\Filament\Resources\Catalogos\Moneda\Pages\ListMonedas;
use App\Filament\Resources\Catalogos\Moneda\Schemas\MonedaForm;
use App\Filament\Resources\Catalogos\Moneda\Tables\MonedaTable;
use App\Models\Monedas\Moneda;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MonedaResource extends Resource
{
    protected static ?string $model = Moneda::class;

    protected static ?string $slug = 'catalogos/monedas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Catálogos';

    protected static ?string $navigationLabel = 'Monedas';

    protected static ?string $modelLabel = 'Moneda';

    protected static ?string $pluralModelLabel = 'Monedas';

    protected static ?int $navigationSort = 55;

    public static function form(Schema $schema): Schema
    {
        return MonedaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonedaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonedas::route('/'),
            'create' => CreateMoneda::route('/create'),
            'edit' => EditMoneda::route('/{record}/edit'),
        ];
    }
}
