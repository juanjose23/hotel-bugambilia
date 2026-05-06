<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\Tables;

use App\Enums\EstadoCatalogo;
use App\Models\Colaboradores\ColaboradorContactoEmergencia;
use App\UseCases\Colaboradores\ObtenerNombreCompleto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
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

class ColaboradorContactoEmergenciaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(fn (ColaboradorContactoEmergencia $record): string => $record->nombre ?? 'Contacto')
            ->columns([
                TextColumn::make('colaborador')
                    ->label('Colaborador')
                    ->formatStateUsing(
                        fn ($record) => app(ObtenerNombreCompleto::class)
                            ->obtenerNombreCompleto($record->colaborador)
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('parentesco')
                    ->label('Parentesco')
                    ->searchable()
                    ->placeholder('N/A'),
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
                    ->modalHeading('Editar contacto')
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
