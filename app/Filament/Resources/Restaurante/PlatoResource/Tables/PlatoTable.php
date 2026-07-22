<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Tables;

use App\Enums\Shared\EstadoGeneral;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PlatoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagenes.url')
                    ->label('')
                    ->circular()
                    ->limit(1),

                TextColumn::make('codigo')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('categoria.nombre')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => EstadoGeneral::tryFrom($state)?->label() ?? 'Desconocido')
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'warning',
                    }),

                IconColumn::make('web')
                    ->label('Web')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categoria_id')
                    ->label('Categoria')
                    ->relationship('categoria', 'nombre'),
                TernaryFilter::make('web')
                    ->label('Visible en Web'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])->icon(Heroicon::EllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
