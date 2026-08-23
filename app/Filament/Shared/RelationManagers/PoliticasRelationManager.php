<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PoliticasRelationManager extends RelationManager
{
    protected static string $relationship = 'politicas';

    protected static ?string $title = 'Políticas Asociadas';

    protected static ?string $label = 'Política';

    protected static ?string $pluralLabel = 'Políticas';

    protected static ?string $recordTitleAttribute = 'titulo';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(100)
                    ->wrap()
                    ->placeholder('-'),

                EstadoBadgeColumn::make(EstadoGeneral::class),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['titulo', 'descripcion']),
            ])
            ->actions([
                DetachAction::make()->iconButton(),
            ]);
    }
}
