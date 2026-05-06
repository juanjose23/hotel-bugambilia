<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario;

use App\Filament\Resources\Colaboradores\ColaboradorSalario\Pages\ManageColaboradorSalarios;
use App\Filament\Resources\Colaboradores\ColaboradorSalario\Schemas\ColaboradorSalarioForm;
use App\Filament\Resources\Colaboradores\ColaboradorSalario\Tables\ColaboradorSalarioTable;
use App\Models\Colaboradores\ColaboradorSalario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ColaboradorSalarioResource extends Resource
{
    protected static ?string $model = ColaboradorSalario::class;

    protected static ?string $modelLabel = 'Salario';
    protected static ?string $pluralModelLabel = 'Historial de Salarios';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Colaboradores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::Banknotes;
    }

    public static function getNavigationLabel(): string
    {
        return 'Salarios';
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorSalarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorSalarioTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['colaborador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageColaboradorSalarios::route('/'),
        ];
    }
}
