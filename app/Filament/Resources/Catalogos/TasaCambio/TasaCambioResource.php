<?php

namespace App\Filament\Resources\Catalogos\TasaCambio;

use App\Filament\Resources\Catalogos\TasaCambio\Pages\CreateTasaCambio;
use App\Filament\Resources\Catalogos\TasaCambio\Pages\EditTasaCambio;
use App\Filament\Resources\Catalogos\TasaCambio\Pages\ListTasaCambios;
use App\Filament\Resources\Catalogos\TasaCambio\Schemas\TasaCambioForm;
use App\Filament\Resources\Catalogos\TasaCambio\Tables\TasaCambioTable;
use App\Filament\Resources\Catalogos\TasaCambio\Widgets\TasaCambioHoyWidget;
use App\Models\Monedas\TasaCambio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TasaCambioResource extends Resource
{
    protected static ?string $model = TasaCambio::class;

    protected static ?string $slug = 'catalogos/tasas-cambio';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Catálogos';

    protected static ?string $navigationLabel = 'Tasas de Cambio';

    protected static ?string $modelLabel = 'Tasa de Cambio';

    protected static ?string $pluralModelLabel = 'Tasas de Cambio';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return TasaCambioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasaCambioTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            TasaCambioHoyWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasaCambios::route('/'),
            'create' => CreateTasaCambio::route('/create'),
            'edit' => EditTasaCambio::route('/{record}/edit'),
        ];
    }
}
