<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDocumento;

use App\Filament\Resources\Colaboradores\ColaboradorDocumento\Pages\ManageColaboradorDocumentos;
use App\Filament\Resources\Colaboradores\ColaboradorDocumento\Schemas\ColaboradorDocumentoForm;
use App\Filament\Resources\Colaboradores\ColaboradorDocumento\Tables\ColaboradorDocumentoTable;
use App\Models\Colaboradores\ColaboradorDocumento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ColaboradorDocumentoResource extends Resource
{
    protected static ?string $model = ColaboradorDocumento::class;

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Expediente Digital';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Colaboradores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::FolderOpen;
    }

    public static function getNavigationLabel(): string
    {
        return 'Documentos';
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorDocumentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorDocumentoTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['colaborador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageColaboradorDocumentos::route('/'),
        ];
    }
}
