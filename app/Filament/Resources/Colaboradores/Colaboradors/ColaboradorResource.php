<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors;

use App\Filament\Resources\Colaboradores\Colaboradors\Pages\CarnetColaborador;
use App\Filament\Resources\Colaboradores\Colaboradors\Pages\CreateColaborador;
use App\Filament\Resources\Colaboradores\Colaboradors\Pages\EditColaborador;
use App\Filament\Resources\Colaboradores\Colaboradors\Pages\ListColaboradors;
use App\Filament\Resources\Colaboradores\Colaboradors\Pages\ViewColaborador;
use App\Filament\Resources\Colaboradores\Colaboradors\Schemas\ColaboradorForm;
use App\Filament\Resources\Colaboradores\Colaboradors\Schemas\ColaboradorInfolist;
use App\Filament\Resources\Colaboradores\Colaboradors\Tables\ColaboradorsTable;
use App\Models\Personas\Persona;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ColaboradorResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $modelLabel = 'Colaboradores';

    protected static ?string $pluralModelLabel = 'Colaboradores';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Colaboradores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::UserGroup;
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ColaboradorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tipo_persona', 'natural')
            ->whereHas('colaborador')
            ->with([
                'personaNatural',
                'pais',
                'colaborador.imagen',
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColaboradors::route('/'),
            'create' => CreateColaborador::route('/create'),
            'edit' => EditColaborador::route('/{record}/edit'),
            'view' => ViewColaborador::route('/{record}'),
            'carnet' => CarnetColaborador::route('/{record}/carnet'),
        ];
    }
}
