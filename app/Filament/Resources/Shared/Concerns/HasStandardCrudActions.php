<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Concerns;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;

trait HasStandardCrudActions
{
    /**
     * @return array<int, mixed>
     */
    protected function getStandardHeaderActions(?string $label = null): array
    {
        return [
            CreateAction::make()
                ->label($label ?? $this->getCreateActionLabel())
                ->icon(Heroicon::Plus),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getStandardRowActions(): array
    {
        return [
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ];
    }

    protected function getCreateActionLabel(): string
    {
        return 'Agregar';
    }
}
