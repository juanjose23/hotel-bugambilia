<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Schemas;

use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Shared\Forms\SelectorCuenta;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class PedidoForm
{
    public static function configure(Schema $schema): Schema
    {
        $pedidoQuery = app(ObtenerDatosPedidoFormQuery::class);

        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos de la Comanda / Pedido')
                    ->description('Seleccione la mesa asignada y la cuenta de estancia en caso de consumo de huésped.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('codigo')
                                ->label('Código Comanda')
                                ->default(fn () => 'PED-'.strtoupper(substr(uniqid(), -6)))
                                ->required()
                                ->maxLength(20)
                                ->prefixIcon(Heroicon::Key),

                            Select::make('mesa_id')
                                ->label('Mesa de Servicio')
                                ->options(fn () => $pedidoQuery->mesasDisponibles())
                                ->searchable()
                                ->required()
                                ->prefixIcon(Heroicon::TableCells),

                            Select::make('estado')
                                ->label('Estado Comanda')
                                ->options(EstadoPedido::class)
                                ->default(EstadoPedido::ABIERTO)
                                ->required()
                                ->prefixIcon(Heroicon::ArrowPath),
                        ]),

                        Grid::make(2)->schema([
                            SelectorCuenta::make(columnSpan: 1),

                            TextInput::make('total')
                                ->label('Total Acumulado')
                                ->numeric()
                                ->prefix('C$')
                                ->default(0.00)
                                ->disabled(),
                        ]),

                        Textarea::make('notas')
                            ->label('Notas Generales de la Comanda')
                            ->columnSpanFull()
                            ->rows(2)
                            ->placeholder('Ej. Mesa VIP / Solicitud especial de cliente...'),
                    ]),

                Section::make('Platillos & Bebidas de la Comanda')
                    ->description('Seleccione el menú organizado por categorías (Bebidas, Desayunos, Almuerzos, Cenas, Platos Fuertes, etc.) con sus especificaciones de cocina o bar.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->schema([
                                Select::make('plato_id')
                                    ->label('Platillo / Bebida (Organizado por Categoría)')
                                    ->options(fn () => $pedidoQuery->platosActivosAgrupadosPorCategoria())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set) use ($pedidoQuery): void {
                                        if (is_numeric($state)) {
                                            $precio = $pedidoQuery->precioActualDePlato((int) $state);

                                            if ($precio !== null) {
                                                $set('precio_unitario', $precio);
                                            }
                                        }
                                    })
                                    ->columnSpan(5),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit. (C$)')
                                    ->numeric()
                                    ->default(0.00)
                                    ->prefix('C$')
                                    ->columnSpan(2),

                                TextInput::make('observaciones')
                                    ->label('Observaciones Cocina')
                                    ->columnSpan(3)
                                    ->placeholder('Ej. Sin cebolla, término medio...'),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Platillo / Bebida'),
                    ]),
            ]);
    }
}
