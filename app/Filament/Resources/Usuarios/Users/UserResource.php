<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Users;

use App\Filament\Resources\Usuarios\Users\Pages\ManageUsers;
use App\Filament\Resources\Usuarios\Users\Schemas\UserForm;
use App\Filament\Resources\Usuarios\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $recordTitleAttribute = 'email';

    protected static string|UnitEnum|null $navigationGroup = 'Personas & Accesos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return app(UserForm::class)->configure($schema);
    }

    public static function table(Table $table): Table
    {
        return app(UsersTable::class)->configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
