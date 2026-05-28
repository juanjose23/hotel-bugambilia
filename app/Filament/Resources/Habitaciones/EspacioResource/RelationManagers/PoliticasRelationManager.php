<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PoliticasRelationManager extends RelationManager
{
    protected static string $relationship = 'politicas';

    protected static ?string $title = 'Políticas';

    protected static ?string $label = 'Política';

    protected static ?string $pluralLabel = 'Políticas';

    protected static ?string $inverseRelationship = 'espacios';

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
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['titulo'])
                    ->icon(Heroicon::PaperClip),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
