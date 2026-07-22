<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Roles;

use App\Filament\Resources\Usuarios\Roles\Pages\CreateRole;
use App\Filament\Resources\Usuarios\Roles\Pages\EditRole;
use App\Filament\Resources\Usuarios\Roles\Pages\ListRoles;
use App\Filament\Resources\Usuarios\Roles\Pages\ViewRole;
use BackedEnum;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use BezhanSalleh\PluginEssentials\Concerns\Resource as Essentials;
use Filament\Clusters\Cluster;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

class RoleResource extends Resource
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;
    use Essentials\HasNavigation;
    use HasShieldFormComponents;
    use HasShieldFormComponents;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Roles';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Seguridad';

    protected static ?string $pluralModelLabel = 'Roles';

    #[Override]
    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Seguridad';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return Schemas\RoleForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return Tables\RoleTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [

            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    #[Override]
    public static function getModel(): string
    {
        /** @var class-string<Model> $model */
        $model = Utils::getRoleModel();

        return $model;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return Utils::getResourceSlug();
    }

    public static function getCluster(): ?string
    {
        $cluster = Utils::getResourceCluster();

        /** @var class-string<Cluster>|null $cluster */
        return $cluster;
    }

    public static function getEssentialsPlugin(): ?FilamentShieldPlugin
    {
        return FilamentShieldPlugin::get();
    }
}
