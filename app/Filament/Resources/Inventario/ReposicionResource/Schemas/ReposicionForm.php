<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ReposicionResource\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReposicionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Metadatos de la Reposición')
                    ->description('Detalle general de la solicitud de reposición.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->disabled(),

                        Select::make('origen_id')
                            ->label('Bodega Origen')
                            ->relationship('origen', 'nombre')
                            ->disabled(),

                        Select::make('destino_id')
                            ->label('Bodega Destino')
                            ->relationship('destino', 'nombre')
                            ->disabled(),

                        TextInput::make('estado')
                            ->label('Estado')
                            ->disabled(),

                        TextInput::make('creadoPor.name')
                            ->label('Creado por')
                            ->disabled()
                            ->placeholder('Sistema (PAR Stock)'),

                        TextInput::make('procesadoPor.name')
                            ->label('Procesado por')
                            ->disabled()
                            ->placeholder('No procesado'),

                        TextInput::make('fecha_proceso')
                            ->label('Fecha Procesado')
                            ->disabled()
                            ->placeholder('Pendiente de procesar'),

                        Textarea::make('notas')
                            ->label('Notas')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Artículos a Reponer')
                    ->description('Listado de productos y cantidades requeridas para alcanzar el stock objetivo.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->disabled()
                            ->columns(4)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre')
                                    ->disabled()
                                    ->columnSpan(2),

                                TextInput::make('cantidad_solicitada')
                                    ->label('Cantidad Solicitada')
                                    ->numeric()
                                    ->disabled(),

                                TextInput::make('cantidad_surtida')
                                    ->label('Cantidad Surtida')
                                    ->numeric()
                                    ->disabled(),
                            ])
                            ->columnSpanFull()
                            ->hiddenLabel(),
                    ]),
            ]);
    }
}
