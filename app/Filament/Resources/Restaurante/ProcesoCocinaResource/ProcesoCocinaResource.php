<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages\CreateProcesoCocina;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages\EditProcesoCocina;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages\ListProcesosCocina;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Schemas\ProcesoCocinaForm;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\Tables\ProcesoCocinaTable;
use App\Repository\Models\Restaurante\ProcesoCocina;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class ProcesoCocinaResource extends Resource
{
    protected static ?string $model = ProcesoCocina::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Scissors;

    protected static ?string $slug = 'restaurante/procesos-cocina';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Procesos Cocina';

    protected static ?string $modelLabel = 'Proceso';

    protected static ?string $pluralModelLabel = 'Procesos Cocina';

    public static function form(Schema $schema): Schema
    {
        return ProcesoCocinaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcesoCocinaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProcesosCocina::route('/'),
            'create' => CreateProcesoCocina::route('/create'),
            'edit' => EditProcesoCocina::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return app(VerificarRestauranteActivo::class)->estaActivo()
            && parent::canViewAny();
    }
}
