<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\ConflictosIdentidad\Tables;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConflictosIdentidadTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('persona.nombre_completo')
                    ->label('Persona')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('tipo_conflicto')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (TipoConflictoIdentidad $state): string => $state->getColor())
                    ->formatStateUsing(fn (TipoConflictoIdentidad $state): string => $state->getLabel()),
                EstadoBadgeColumn::make(EstadoConflictoIdentidad::class),
                TextColumn::make('creadoPor.name')
                    ->label('Reportado por')
                    ->placeholder('—')
                    ->toggleable(),
                FechaStandardColumn::make('created_at', 'Fecha'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                FiltroEstado::make(EstadoConflictoIdentidad::class),
                SelectFilter::make('tipo_conflicto')
                    ->label('Tipo de Conflicto')
                    ->options(TipoConflictoIdentidad::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ]);
    }
}
