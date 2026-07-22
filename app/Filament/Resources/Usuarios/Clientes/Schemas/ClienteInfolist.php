<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClienteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos Personales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('primer_nombre')
                                    ->label('Primer Nombre'),
                                TextEntry::make('segundo_nombre')
                                    ->label('Segundo Nombre')
                                    ->placeholder('—'),
                                TextEntry::make('personaNatural.primer_apellido')
                                    ->label('Primer Apellido'),
                                TextEntry::make('personaNatural.segundo_apellido')
                                    ->label('Segundo Apellido')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Section::make('Identificación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('personaNatural.tipo_identificacion')
                                    ->label('Tipo de Identificación')
                                    ->placeholder('—'),
                                TextEntry::make('personaNatural.numero_identificacion')
                                    ->label('Número de Identificación')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Section::make('Contacto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('telefono')
                                    ->label('Teléfono')
                                    ->placeholder('—'),
                                TextEntry::make('direccion')
                                    ->label('Dirección')
                                    ->placeholder('—'),
                                TextEntry::make('user.email')
                                    ->label('Correo Electrónico')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Section::make('Tipo de Cliente')
                    ->schema([
                        TextEntry::make('cliente.tipoCliente.nombre')
                            ->label('Tipo de Cliente')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
