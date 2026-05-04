<?php

namespace App\Filament\Resources\Catalogos\Pais\Tables;

use App\Enums\EstadoCatalogo;
use App\Models\Catalogos\Pais;
use App\UseCases\Pais\ActualizarPais;
use App\UseCases\Pais\EliminarPais;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class PaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_iso2')
                    ->label('Código Iso 2')
                    ->searchable(),
                TextColumn::make('codigo_iso3')
                    ->label('Código Iso 3')
                    ->searchable(),
                ImageColumn::make('codigo_iso2')
                    ->label('Bandera')
                    ->getStateUsing(fn($record) => asset('banderas/64X48/' . strtolower($record->codigo_iso2) . '.png'))
                    ->extraImgAttributes(['class' => 'rounded shadow-sm']),
                TextColumn::make('nombre')
                    ->label('País')
                    ->searchable()
                    ->description(fn($record) => $record->codigo_iso2)
                    ->weight('medium'),
                TextColumn::make('codigo_telefono')
                    ->label('Código de Telefono')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn($state): string => EstadoCatalogo::colorFor($state))
                    ->formatStateUsing(fn($state): string => EstadoCatalogo::labelFor($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->sortable()
                    ->label('Creado El')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Actualizado El')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->using(fn(Pais $record, array $data) => app(ActualizarPais::class)->execute($record, $data)),
                    DeleteAction::make()
                        ->using(fn(Pais $record) => app(EliminarPais::class)->execute($record)),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
