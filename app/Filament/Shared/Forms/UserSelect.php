<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\User;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class UserSelect
{
    public static function make(string $column, string $label = 'Responsable'): Select
    {
        return Select::make($column)
            ->label($label)
            ->options(fn (): Collection => User::pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::User);
    }
}
