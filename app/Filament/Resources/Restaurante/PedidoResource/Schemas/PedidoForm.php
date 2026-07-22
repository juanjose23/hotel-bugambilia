<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Schemas;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PedidoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Pedido')
                    ->columns(3)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Codigo')
                            ->required()
                            ->maxLength(20)
                            ->prefixIcon(Heroicon::Key),

                        Select::make('mesa_id')
                            ->label('Mesa')
                            ->options(fn () => Espacio::where('tipo', 'mesa')->pluck('nombre', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->prefixIcon(Heroicon::TableCells),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPedido::class)
                            ->default('abierto')
                            ->required()
                            ->prefixIcon(Heroicon::ArrowPath),

                        Select::make('mesero_id')
                            ->label('Mesero')
                            ->options(fn () => Colaborador::with('persona')->get()->mapWithKeys(fn ($c) => [$c->id => $c->persona->nombre_completo ?? "Colab. #{$c->id}"])->toArray())
                            ->searchable()
                            ->prefixIcon(Heroicon::User),

                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('C$')
                            ->disabled(),

                        Textarea::make('notas')
                            ->label('Notas')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Section::make('Items del Pedido')
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->schema([
                                Select::make('plato_id')
                                    ->label('Plato / Bebida')
                                    ->options(fn () => Plato::activos()->pluck('nombre', 'id')->toArray())
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(4),

                                TextInput::make('cantidad')
                                    ->label('Cant.')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(2),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('C$')
                                    ->columnSpan(3),

                                Textarea::make('notas')
                                    ->label('Notas')
                                    ->columnSpan(3)
                                    ->placeholder('Sin cebolla, termino medio...'),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Plato'),
                    ]),
            ]);
    }
}
