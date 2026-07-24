<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias;

use App\Enums\Estancias\EstadoEstancia;
use App\Filament\Resources\Estancias\EstanciaResource\Pages\ListEstancias;
use App\Filament\Resources\Estancias\EstanciaResource\Pages\ViewEstancia;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Estancias\Estancia;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class EstanciaResource extends Resource
{
    protected static ?string $model = Estancia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $modelLabel = 'Estancia';

    protected static ?string $pluralModelLabel = 'Estancias y Ocupación';

    protected static ?int $navigationSort = 4;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Datos de la Estancia')
                ->schema([
                    TextEntry::make('reserva.codigo_reserva')->label('Código Reserva'),
                    TextEntry::make('reserva.nombre_cliente')->label('Cliente'),
                    TextEntry::make('habitacion.nombre')->label('Habitación')->placeholder('—'),
                    TextEntry::make('estado')->label('Estado')->badge(),
                    TextEntry::make('cantidad_llaves')->label('Llaves entregadas'),
                    TextEntry::make('check_in_at')->label('Check-In realizado')->dateTime('d/m/Y H:i'),
                    TextEntry::make('check_out_at')->label('Check-Out realizado')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('usuarioCheckIn.name')->label('Usuario Check-In')->placeholder('—'),
                    TextEntry::make('usuarioCheckOut.name')->label('Usuario Check-Out')->placeholder('—'),
                    TextEntry::make('observaciones_entrada')->label('Observaciones Entrada')->placeholder('Sin observaciones')->columnSpanFull(),
                    TextEntry::make('observaciones_salida')->label('Observaciones Salida')->placeholder('Sin observaciones')->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reserva.codigo_reserva')
                    ->label('Código Reserva')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('reserva.nombre_cliente')
                    ->label('Cliente / Huésped')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('check_in_at')
                    ->label('Check-In')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('check_out_at')
                    ->label('Check-Out')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En estancia')
                    ->sortable(),

                TextColumn::make('cantidad_llaves')
                    ->label('Llaves')
                    ->numeric(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('cuenta.saldo')
                    ->label('Saldo Cuenta')
                    ->money('NIO')
                    ->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                FiltroEstado::make(EstadoEstancia::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEstancias::route('/'),
            'view' => ViewEstancia::route('/{record}'),
        ];
    }
}
