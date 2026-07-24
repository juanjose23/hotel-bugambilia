<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\TipoTitular;
use App\Filament\Resources\Estancias\CuentaEstanciaResource\Pages;
use App\Filament\Resources\Estancias\CuentaEstanciaResource\RelationManagers\MovimientosRelationManager;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Estancias\CuentaEstancia;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class CuentaEstanciaResource extends Resource
{
    protected static ?string $model = CuentaEstancia::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::FolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Cuenta de estancia';

    protected static ?string $pluralModelLabel = 'Cuentas de estancia';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Datos de la cuenta')
                ->schema([
                    TextEntry::make('numero_cuenta')
                        ->label('Número de Cuenta')
                        ->placeholder(fn ($record): string => $record->numero_folio ?? '—'),
                    TextEntry::make('numero_folio')->label('Folio'),
                    TextEntry::make('tipo_titular')
                        ->label('Tipo de Titular')
                        ->badge(),
                    TextEntry::make('estado')->label('Estado')->badge(),
                    TextEntry::make('estancia.habitacion.nombre')
                        ->label('Habitación')
                        ->placeholder('—'),
                    TextEntry::make('reserva.nombre_cliente')
                        ->label('Cliente')
                        ->placeholder('—'),
                    TextEntry::make('limite_autorizado')
                        ->label('Límite autorizado')
                        ->money('NIO')
                        ->placeholder('Sin límite'),
                    TextEntry::make('total_cargos')->label('Total cargos')->money('NIO'),
                    TextEntry::make('total_pagos')->label('Total pagos')->money('NIO'),
                    TextEntry::make('saldo')->label('Saldo actual')->money('NIO'),
                    TextEntry::make('abierta_at')->label('Abierta el')->dateTime('d/m/Y H:i'),
                    TextEntry::make('cerrada_at')->label('Cerrada el')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('abiertaPor.name')->label('Abierta por')->placeholder('—'),
                    TextEntry::make('cerradaPor.name')->label('Cerrada por')->placeholder('—'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_cuenta')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn ($record): string => $record->numero_folio ?? '—'),

                TextColumn::make('tipo_titular')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('estancia.habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('reserva.nombre_cliente')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('limite_autorizado')
                    ->label('Límite')
                    ->money('NIO')
                    ->placeholder('Sin límite')
                    ->sortable(),

                TextColumn::make('total_cargos')
                    ->label('Cargos')
                    ->money('NIO')
                    ->sortable(),

                TextColumn::make('total_pagos')
                    ->label('Pagos')
                    ->money('NIO')
                    ->sortable(),

                TextColumn::make('saldo')
                    ->label('Saldo Pendiente')
                    ->money('NIO')
                    ->sortable(),

                TextColumn::make('abierta_at')
                    ->label('Abierta el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                FiltroEstado::make(EstadoCuentaEstancia::class),
                SelectFilter::make('tipo_titular')
                    ->label('Tipo de Titular')
                    ->options(TipoTitular::class)
                    ->native(false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentasEstancia::route('/'),
            'view' => Pages\ViewCuentaEstancia::route('/{record}'),
        ];
    }

    /**
     * @return array<class-string>
     */
    public static function getRelationManagers(): array
    {
        return [
            MovimientosRelationManager::class,
        ];
    }
}
