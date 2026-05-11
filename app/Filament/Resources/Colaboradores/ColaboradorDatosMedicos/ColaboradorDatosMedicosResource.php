<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos;

use App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\Pages\ManageColaboradorDatosMedicos;
use App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\Schemas\ColaboradorDatosMedicosForm;
use App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\Tables\ColaboradorDatosMedicosTable;
use App\Models\Colaboradores\ColaboradorDatosMedicos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ColaboradorDatosMedicosResource extends Resource
{
    protected static ?string $model = ColaboradorDatosMedicos::class;

    protected static ?string $modelLabel = 'Ficha Médica';

    protected static ?string $pluralModelLabel = 'Salud y Datos Médicos';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Colaboradores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::Heart;
    }

    public static function getNavigationLabel(): string
    {
        return 'Salud';
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorDatosMedicosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorDatosMedicosTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['colaborador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageColaboradorDatosMedicos::route('/'),
        ];
    }
}
