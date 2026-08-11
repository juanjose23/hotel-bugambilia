<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Repository\Models\Cuentas\Cuenta;
use App\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CuentasRelationManager extends RelationManager
{
    protected static string $relationship = 'cuentas';

    protected static ?string $title = 'Cuentas de la reserva';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('moneda'))
            ->columns([
                TextColumn::make('numero_cuenta')
                    ->label('Cuenta')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('tipo_cuenta')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda)),
                TextColumn::make('total_pagado')
                    ->label('Pagado')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda)),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda))
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'success'),
                TextColumn::make('abierta_at')
                    ->label('Abierta')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                CobrarCuentaAction::makeTableAction()->size('sm'),
                Action::make('verCuenta')
                    ->label('Ver cuenta')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => $record instanceof Cuenta ? CuentaResource::getUrl('view', ['record' => $record]) : '#')
                    ->openUrlInNewTab(),
            ]);
    }
}
