<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario\Tables;

use App\Enums\EstadoCatalogo;
use App\Models\Colaboradores\ColaboradorSalario;
use App\UseCases\Colaboradores\Queries\ObtenerNombreCompleto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ColaboradorSalarioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(fn (ColaboradorSalario $record): string => $record->colaborador->codigo ?? 'Salario')
            ->columns([
                TextColumn::make('colaborador')
                    ->label('Colaborador')
                    ->formatStateUsing(
                        fn ($record) => app(ObtenerNombreCompleto::class)
                            ->obtenerNombreCompleto($record->colaborador)
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salario')
                    ->label('Salario')
                    ->money('NIO')
                    ->sortable(),
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->placeholder('Activo'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state))
                    ->color(fn ($state): string => EstadoCatalogo::colorFor($state)),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar salario')
                    ->modalWidth('2xl'),
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
