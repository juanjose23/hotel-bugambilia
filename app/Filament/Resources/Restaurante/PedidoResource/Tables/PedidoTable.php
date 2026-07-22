<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Tables;

use App\Enums\Restaurante\EstadoPedido;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PedidoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('mesa.nombre')->label('Mesa')->searchable()->sortable(),
                TextColumn::make('mesero.persona.nombre_completo')->label('Mesero')->placeholder('—'),
                TextColumn::make('estado')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state): string => EstadoPedido::from($state)->label())
                    ->color(fn (string $state): string => EstadoPedido::from($state)->color()),
                TextColumn::make('total')->label('Total')->money('NIO')->sortable(),
                TextColumn::make('created_at')->label('Creado')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoPedido::class),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
