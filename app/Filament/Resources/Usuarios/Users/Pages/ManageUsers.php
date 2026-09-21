<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Users\Pages;

use App\Filament\Resources\Usuarios\Users\UserResource;
use App\Repository\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Usuario')
                ->icon(Heroicon::UserPlus)
                ->modalWidth('2xl'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->icon(Heroicon::Users)
                ->badge(fn () => User::query()->count()),

            'admins' => Tab::make('Administradores')
                ->icon(Heroicon::ShieldCheck)
                ->badge(fn () => User::query()->where('is_admin', true)->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_admin', true)),

            'colaboradores' => Tab::make('Colaboradores')
                ->icon(Heroicon::Briefcase)
                ->badge(fn () => User::query()->whereHas('persona.colaborador')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('persona.colaborador')),

            'clientes' => Tab::make('Clientes')
                ->icon(Heroicon::UserGroup)
                ->badge(fn () => User::query()->whereHas('persona.cliente')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('persona.cliente')),
        ];
    }
}
