<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ZonaDeliveryResource\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ZonaDeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Departamento / Zona')
                    ->description('Configura los datos del departamento para el servicio de entrega a domicilio.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->placeholder('Ej: EST, MAD, MGA')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),

                        TextInput::make('nombre')
                            ->label('Nombre del Departamento')
                            ->placeholder('Ej: Estelí, Madriz, Managua')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('costo_envio')
                            ->label('Costo de Envío (C$)')
                            ->numeric()
                            ->prefix('C$')
                            ->minValue(0)
                            ->default(50.00)
                            ->required(),

                        TextInput::make('orden')
                            ->label('Orden de Visualización')
                            ->numeric()
                            ->default(1)
                            ->minValue(0),

                        Toggle::make('activo')
                            ->label('Servicio de Delivery Habilitado')
                            ->helperText('Si se desactiva, los clientes no podrán seleccionar este departamento en el checkout.')
                            ->default(true)
                            ->columnSpan(2),
                    ]),

                Section::make('Municipios y Zonas Permitidas')
                    ->description('Lista de municipios, ciudades o sectores cubiertos dentro de este departamento.')
                    ->schema([
                        TagsInput::make('municipios')
                            ->label('Municipios / Sectores')
                            ->placeholder('Escribe un municipio y presiona Enter...')
                            ->helperText('Ingresa cada municipio o localidad donde se realizan entregas.')
                            ->required(),
                    ]),
            ]);
    }
}
