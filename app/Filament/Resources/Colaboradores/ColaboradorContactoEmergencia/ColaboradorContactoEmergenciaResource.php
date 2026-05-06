<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia;

use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\Pages\ManageColaboradorContactoEmergencias;
use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\Schemas\ColaboradorContactoEmergenciaForm;
use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\Tables\ColaboradorContactoEmergenciaTable;
use App\Models\Colaboradores\ColaboradorContactoEmergencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ColaboradorContactoEmergenciaResource extends Resource
{
    protected static ?string $model = ColaboradorContactoEmergencia::class;

    protected static ?string $modelLabel = 'Contacto de Emergencia';
    protected static ?string $pluralModelLabel = 'Contactos de Emergencia';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Colaboradores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::Users;
    }

    public static function getNavigationLabel(): string
    {
        return 'Contactos';
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorContactoEmergenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorContactoEmergenciaTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['colaborador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageColaboradorContactoEmergencias::route('/'),
        ];
    }
}
