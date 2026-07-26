<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Filament\Resources\Cuentas\CuentaResource\Pages;
use App\Filament\Resources\Cuentas\CuentaResource\RelationManagers\DetallesRelationManager;
use App\Filament\Resources\Cuentas\CuentaResource\RelationManagers\PagosRelationManager;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Cuentas\Cuenta;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class CuentaResource extends Resource
{
    protected static ?string $model = Cuenta::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::FolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Cuenta';

    protected static ?string $pluralModelLabel = 'Cuentas';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Datos de la Cuenta')
                ->schema([
                    TextEntry::make('numero_cuenta')
                        ->label('Número de Cuenta')
                        ->copyable(),
                    TextEntry::make('tipo_cuenta')
                        ->label('Tipo')
                        ->badge(),
                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge(),
                    TextEntry::make('cliente.nombre_completo')
                        ->label('Cliente')
                        ->placeholder('—'),
                    TextEntry::make('estancia.habitacion.nombre')
                        ->label('Habitación')
                        ->placeholder('—'),
                    TextEntry::make('reserva.nombre_cliente')
                        ->label('Reserva / Cliente')
                        ->placeholder('—'),
                    TextEntry::make('limite_autorizado')
                        ->label('Límite autorizado')
                        ->money('NIO')
                        ->placeholder('Sin límite'),
                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->money('NIO'),
                    TextEntry::make('descuento_total')
                        ->label('Descuentos')
                        ->money('NIO'),
                    TextEntry::make('impuesto_total')
                        ->label('Impuestos')
                        ->money('NIO'),
                    TextEntry::make('total')
                        ->label('Total')
                        ->money('NIO')
                        ->weight(FontWeight::Bold),
                    TextEntry::make('total_pagado')
                        ->label('Total pagado')
                        ->money('NIO'),
                    TextEntry::make('saldo')
                        ->label('Saldo Pendiente')
                        ->money('NIO')
                        ->color(fn (Cuenta $record): string => (float) $record->saldo > 0 ? 'danger' : 'success'),
                    TextEntry::make('abierta_at')
                        ->label('Abierta el')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('cerrada_at')
                        ->label('Cerrada el')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('usuarioQueAbrio.name')
                        ->label('Abierta por')
                        ->placeholder('—'),
                    TextEntry::make('usuarioQueCerro.name')
                        ->label('Cerrada por')
                        ->placeholder('—'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_cuenta')
                    ->label('Número de Cuenta')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('tipo_cuenta')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('estancia.habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('limite_autorizado')
                    ->label('Límite')
                    ->money('NIO')
                    ->placeholder('Sin límite')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('NIO')
                    ->sortable(),

                TextColumn::make('total_pagado')
                    ->label('Pagado')
                    ->money('NIO')
                    ->sortable(),

                TextColumn::make('saldo')
                    ->label('Saldo Pendiente')
                    ->money('NIO')
                    ->sortable()
                    ->color(fn (Cuenta $record): string => (float) $record->saldo > 0 ? 'danger' : 'success'),

                TextColumn::make('abierta_at')
                    ->label('Abierta el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                FiltroEstado::make(EstadoCuenta::class),
                SelectFilter::make('tipo_cuenta')
                    ->label('Tipo de Cuenta')
                    ->options(TipoCuenta::class)
                    ->native(false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentas::route('/'),
            'view' => Pages\ViewCuenta::route('/{record}'),
        ];
    }

    /**
     * @return array<class-string>
     */
    public static function getRelationManagers(): array
    {
        return [
            DetallesRelationManager::class,
            PagosRelationManager::class,
        ];
    }
}
