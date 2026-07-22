<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;

trait TieneAccionesCrudEstandar
{
    /** @return array<int, Action> */
    protected function getStandardHeaderActions(?string $label = null): array
    {
        return [
            CreateAction::make()
                ->label($label ?? $this->getCreateActionLabel())
                ->icon(Heroicon::Plus),
        ];
    }

    /** @return array<int, Action> */
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
