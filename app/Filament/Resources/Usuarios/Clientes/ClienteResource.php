<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes;

use App\Filament\Resources\Usuarios\Clientes\Pages\CreateCliente;
use App\Filament\Resources\Usuarios\Clientes\Pages\EditCliente;
use App\Filament\Resources\Usuarios\Clientes\Pages\ListClientes;
use App\Filament\Resources\Usuarios\Clientes\Pages\ViewCliente;
use App\Filament\Resources\Usuarios\Clientes\Schemas\ClienteForm;
use App\Filament\Resources\Usuarios\Clientes\Schemas\ClienteInfolist;
use App\Filament\Resources\Usuarios\Clientes\Tables\ClientesTable;
use App\Repository\Models\Personas\Persona;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ClienteResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $slug = 'usuarios/clientes';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static string|UnitEnum|null $navigationGroup = 'Clientes & Usuarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ClienteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClienteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('cliente')
            ->with([
                'personaNatural',
                'personaJuridica',
                'pais',
                'cliente.tipoCliente',
                'user',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientes::route('/'),
            'create' => CreateCliente::route('/create'),
            'view' => ViewCliente::route('/{record}'),
            'edit' => EditCliente::route('/{record}/edit'),
        ];
    }
}
