<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoBaja\Tables;

use App\Enums\Activos\TipoBaja;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivoBajaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['activo', 'aprobadoPor', 'creadoPor']))
            ->columns([
                TextColumn::make('codigo')
                    ->label('Folio')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('activo.codigo_inventario')
                    ->label('Activo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('activo.nombre_descriptivo')
                    ->label('Nombre Descriptivo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha_baja')
                    ->label('Fecha Baja')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('motivo_tipo')
                    ->label('Motivo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('valor_residual')
                    ->label('Valor Residual')
                    ->money('NIO')
                    ->sortable()
                    ->placeholder('0.00'),

                TextColumn::make('creadoPor.name')
                    ->label('Registrado Por')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('motivo_tipo')
                    ->options(TipoBaja::class),
            ]);
    }
}
