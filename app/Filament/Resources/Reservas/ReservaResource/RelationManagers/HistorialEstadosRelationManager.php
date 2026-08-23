<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use App\Filament\Shared\Columns\FechaStandardColumn;
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
                FechaStandardColumn::make('created_at', 'Fecha'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
