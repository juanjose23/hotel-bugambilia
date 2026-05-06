<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Pages;

use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewColaborador extends ViewRecord
{
    protected static string $resource = ColaboradorResource::class;

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('carnet')
                ->label('Carnet')
                ->icon('heroicon-o-identification')
                ->url(fn(): string => $this->getResourceUrl('carnet'))
                ->openUrlInNewTab(),
            EditAction::make()
                ->modalWidth('7xl'),
            DeleteAction::make(),
        ];
    }
}
