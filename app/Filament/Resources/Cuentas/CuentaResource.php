<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Filament\Resources\Cuentas\CuentaResource\Pages;
use App\Filament\Resources\Cuentas\CuentaResource\RelationManagers\DetallesRelationManager;
use App\Filament\Resources\Cuentas\CuentaResource\RelationManagers\PagosRelationManager;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Personas\Persona;
use App\Support\MonedaHelper;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class CuentaResource extends Resource
{
    protected static ?string $model = Cuenta::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::FolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Facturación & Finanzas';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Cuenta';

    protected static ?string $pluralModelLabel = 'Cuentas';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            // ── Sección 1: Identificación ────────────────────────────────────
            Section::make('Identificación')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    TextEntry::make('numero_cuenta')
                        ->label('Número de Cuenta')
                        ->copyable()
                        ->weight(FontWeight::Bold),

                    TextEntry::make('tipo_cuenta')
                        ->label('Tipo de Cuenta')
                        ->badge(),

                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge(),

                    TextEntry::make('moneda.codigo')
                        ->label('Moneda')
                        ->placeholder('Predeterminada'),

                    TextEntry::make('limite_autorizado')
                        ->label('Límite de Crédito')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda))
                        ->placeholder('Sin límite'),

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
                ]),

            // ── Sección 2: Cliente y Origen ──────────────────────────────────
            Section::make('Cliente y Origen')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    TextEntry::make('cliente.nombre_completo')
                        ->label('Cliente')
                        ->placeholder('Cliente de mostrador'),

                    TextEntry::make('estancia.habitacion.nombre')
                        ->label('Habitación')
                        ->placeholder('—')
                        ->visible(fn (Cuenta $record): bool => $record->tipo_cuenta === TipoCuenta::ESTANCIA),

                    TextEntry::make('reserva.nombre_cliente')
                        ->label('Reserva / Titular')
                        ->placeholder('—')
                        ->visible(fn (Cuenta $record): bool => $record->tipo_cuenta === TipoCuenta::ESTANCIA),

                    TextEntry::make('reserva.fecha_check_in')
                        ->label('Check-In')
                        ->date('d/m/Y')
                        ->placeholder('—')
                        ->visible(fn (Cuenta $record): bool => $record->tipo_cuenta === TipoCuenta::ESTANCIA),

                    TextEntry::make('reserva.fecha_check_out')
                        ->label('Check-Out')
                        ->date('d/m/Y')
                        ->placeholder('—')
                        ->visible(fn (Cuenta $record): bool => $record->tipo_cuenta === TipoCuenta::ESTANCIA),
                ]),

            // ── Sección 3: Estado Financiero ────────────────────────────────
            Section::make('Estado Financiero')
                ->icon('heroicon-o-banknotes')
                ->columns(4)
                ->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda)),

                    TextEntry::make('descuento_total')
                        ->label('Descuentos')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda)),

                    TextEntry::make('impuesto_total')
                        ->label('Impuestos')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda)),

                    TextEntry::make('cargo_servicio_total')
                        ->label('Cargo Servicio')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda)),

                    TextEntry::make('propina_total')
                        ->label('Propinas')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda)),

                    TextEntry::make('recargo_total')
                        ->label('Recargos')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda)),

                    TextEntry::make('total')
                        ->label('TOTAL')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda))
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large),

                    TextEntry::make('total_pagado')
                        ->label('Ya Pagado')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda))
                        ->color('success'),

                    TextEntry::make('saldo')
                        ->label('SALDO PENDIENTE')
                        ->money(fn (Cuenta $record): string => MonedaHelper::codigo($record->moneda))
                        ->weight(FontWeight::Bold)
                        ->color(fn (Cuenta $record): string => (float) $record->saldo > 0 ? 'danger' : 'success')
                        ->columnSpan(2),
                ]),

            // ── Sección 4: Desglose de Consumos (Ítems) ───────────────────────
            Section::make('Desglose de Consumos')
                ->icon('heroicon-o-shopping-bag')
                ->schema([
                    RepeatableEntry::make('detalles')
                        ->hiddenLabel()
                        ->columns(5)
                        ->schema([
                            TextEntry::make('concepto')
                                ->label('Concepto')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('cantidad')
                                ->label('Cant.'),
                            TextEntry::make('precio_unitario')
                                ->label('Precio Unitario')
                                ->money(fn ($record): string => MonedaHelper::codigo($record->cuenta?->moneda)),
                            TextEntry::make('subtotal')
                                ->label('Subtotal')
                                ->money(fn ($record): string => MonedaHelper::codigo($record->cuenta?->moneda)),
                            TextEntry::make('estado')
                                ->label('Estado')
                                ->badge(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['moneda', 'cliente', 'estancia.habitacion']))
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
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('cliente', fn (Builder $clienteQuery): Builder => Persona::filtrarPorNombre($clienteQuery, $search))),

                TextColumn::make('estancia.habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('limite_autorizado')
                    ->label('Límite')
                    ->money(fn (?Cuenta $record): string => MonedaHelper::codigo($record?->moneda))
                    ->placeholder('Sin límite')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn (?Cuenta $record): string => MonedaHelper::codigo($record?->moneda))
                    ->sortable(),

                TextColumn::make('total_pagado')
                    ->label('Pagado')
                    ->money(fn (?Cuenta $record): string => MonedaHelper::codigo($record?->moneda))
                    ->sortable(),

                TextColumn::make('saldo')
                    ->label('Saldo Pendiente')
                    ->money(fn (?Cuenta $record): string => MonedaHelper::codigo($record?->moneda))
                    ->sortable()
                    ->color(fn (?Cuenta $record): string => (float) ($record->saldo ?? 0) > 0 ? 'danger' : 'success'),

                TextColumn::make('abierta_at')
                    ->label('Abierta el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                CobrarCuentaAction::makeTableAction()->size('sm'),
            ])
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
