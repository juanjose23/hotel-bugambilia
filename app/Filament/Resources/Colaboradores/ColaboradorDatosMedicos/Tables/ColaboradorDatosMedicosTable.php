<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\Tables;

use App\UseCases\Colaboradores\ObtenerNombreCompleto;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ColaboradorDatosMedicosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('colaborador.codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('colaborador.id')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => app(ObtenerNombreCompleto::class)
                        ->nombreCompletoConCodigo($record->colaborador)),

                TextColumn::make('tipo_sangre')
                    ->label('Sangre')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('alergias')
                    ->label('Alergias')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),

                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar información médica')
                    ->modalWidth('3xl'),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ]);
    }
}
