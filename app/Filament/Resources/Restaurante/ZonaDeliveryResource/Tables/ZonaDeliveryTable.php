<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ZonaDeliveryResource\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ZonaDeliveryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orden')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Departamento / Zona')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('costo_envio')
                    ->label('Costo de Envío')
                    ->money('NIO')
                    ->sortable(),

                TextColumn::make('municipios')
                    ->label('Municipios Cubiertos')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || empty($state)) {
                            return 'Sin municipios';
                        }
                        /** @var list<string> $municipiosList */
                        $municipiosList = array_values(array_filter($state, fn ($m): bool => is_string($m) && $m !== ''));
                        $count = count($municipiosList);
                        $preview = implode(', ', array_slice($municipiosList, 0, 3));

                        return $count > 3 ? "{$preview} (+".($count - 3).' más)' : $preview;
                    })
                    ->badge()
                    ->color('gray'),

                IconColumn::make('activo')
                    ->label('Habilitado')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('orden', 'asc')
            ->filters([
                TernaryFilter::make('activo')
                    ->label('Habilitado para Delivery'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])->icon(Heroicon::EllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
