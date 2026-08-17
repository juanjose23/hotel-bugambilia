<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\BeneficioClienteResource\Tables;

use App\Enums\Promociones\TipoBeneficioCliente;
use App\Filament\Shared\Filters\FiltroEliminados;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BeneficioClienteTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Beneficio')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('segmentoCliente.nombre')
                    ->label('Segmento')
                    ->searchable()
                    ->placeholder('Todos'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof TipoBeneficioCliente ? $state->getLabel() : (string) $state)
                    ->color(fn ($state): string => $state instanceof TipoBeneficioCliente ? $state->getColor() : 'gray'),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state, $record): string => $state === null ? '-' : ($record->es_porcentaje ? "{$state}%" : "C$ {$state}")),

                IconColumn::make('combinable')
                    ->label('Comb.')
                    ->boolean(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('activo')
                    ->label('Activo'),

                FiltroEliminados::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
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
