<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class HistorialEstadosRelationManager extends RelationManager
{
    protected static string $relationship = 'historialEstados';

    protected static ?string $title = 'Historial de estados';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('estado_anterior')->label('Estado anterior')->badge()->placeholder('Creación'),
                TextColumn::make('estado_nuevo')->label('Estado nuevo')->badge(),
                TextColumn::make('motivo')->label('Motivo')->wrap()->placeholder('—'),
                TextColumn::make('usuario.name')->label('Usuario')->placeholder('Sistema'),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
