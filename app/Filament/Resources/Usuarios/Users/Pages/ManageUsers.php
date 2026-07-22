<?php

namespace App\Filament\Resources\Usuarios\Users\Pages;

use App\Filament\Resources\Usuarios\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'admins' => Tab::make('Usuarios Admins')
                ->modifyQueryUsing(fn ($query) => $query->where('is_admin', true)),
            'clientes' => Tab::make('Usuarios Clientes')
                ->modifyQueryUsing(fn ($query) => $query->where('is_admin', false)),
        ];
    }
}
