<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Roles\Schemas;

use App\Filament\Resources\Usuarios\Roles\RoleResource;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    use InyectaDesdeContenedor;

    public static function configure(Schema $schema): Schema
    {
        return static::make()->doConfigure($schema);
    }

    private function doConfigure(Schema $schema): Schema
    {
        $teamForeignKey = config('permission.column_names.team_foreign_key');
        $teamForeignKeyStr = is_string($teamForeignKey) ? $teamForeignKey : (is_numeric($teamForeignKey) ? (string) $teamForeignKey : 'team_id');

        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->maxLength(255),

                                Select::make($teamForeignKeyStr)
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    ->default(Filament::getTenant()?->getKey())
                                    ->options(fn (): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn (): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn (): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                                RoleResource::getSelectAllFormComponent(),

                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                RoleResource::getShieldFormComponents(),
            ]);
    }
}
