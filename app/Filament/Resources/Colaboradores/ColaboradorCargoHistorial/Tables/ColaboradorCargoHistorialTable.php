<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\Tables;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\ColaboradorNombreColumn;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use App\Repository\Models\Colaboradores\ColaboradorCargoHistorial;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ColaboradorCargoHistorialTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(title: fn (ColaboradorCargoHistorial $record): string => $record->cargo->nombre ?? 'Cargo')
            ->columns([
                ColaboradorNombreColumn::make('colaborador.persona.nombre_completo'),
                TextColumn::make('cargo.nombre')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('departamento.nombre')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->placeholder('Activo'),
                EstadoBadgeColumn::make(EstadoGeneral::class),
            ])
            ->filters([
                FiltroEliminados::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar asignación de cargo')
                    ->modalWidth('3xl'),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
