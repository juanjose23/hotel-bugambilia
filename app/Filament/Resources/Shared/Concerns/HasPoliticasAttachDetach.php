<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Concerns;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

trait HasPoliticasAttachDetach
{
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? 'Activo' : 'Inactivo'),
            ])
            ->headerActions([
                $this->getAttachAction(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }

    protected function getAttachAction(): AttachAction
    {
        $action = AttachAction::make()
            ->preloadRecordSelect()
            ->recordSelectSearchColumns(['titulo']);

        $icon = $this->getAttachActionIcon();
        if ($icon !== null) {
            $action->icon($icon);
        }

        return $action;
    }

    protected function getAttachActionIcon(): Heroicon|string|null
    {
        return null;
    }
}
