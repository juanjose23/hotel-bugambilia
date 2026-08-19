<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial;

use App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\Pages\ManageColaboradorCargoHistorials;
use App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\Schemas\ColaboradorCargoHistorialForm;
use App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\Tables\ColaboradorCargoHistorialTable;
use App\Repository\Models\Colaboradores\ColaboradorCargoHistorial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ColaboradorCargoHistorialResource extends Resource
{
    protected static ?string $model = ColaboradorCargoHistorial::class;

    protected static ?string $slug = 'colaboradores/historial-cargos';

    protected static ?string $modelLabel = 'Cargo';

    protected static ?string $pluralModelLabel = 'Historial de Cargos';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Personas & Accesos';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::Briefcase;
    }

    public static function getNavigationLabel(): string
    {
        return 'Cargos';
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorCargoHistorialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorCargoHistorialTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['colaborador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageColaboradorCargoHistorials::route('/'),
        ];
    }
}
