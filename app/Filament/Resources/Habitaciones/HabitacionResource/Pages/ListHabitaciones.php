<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListHabitaciones extends ListRecords
{
    protected static string $resource = HabitacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Habitación')
                ->icon(Heroicon::Plus)
                ->url(fn (): string => HabitacionResource::getUrl('create')),
        ];
    }
}
