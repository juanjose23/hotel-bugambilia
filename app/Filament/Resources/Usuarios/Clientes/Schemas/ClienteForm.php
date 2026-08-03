<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Schemas;

use App\Enums\Personas\TipoIdentificacion;
use App\Repository\Models\Catalogos\Pais;
use App\Support\CachedOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tipo de Cliente y Entidad')
                    ->icon(Heroicon::UserGroup)
                    ->description('Seleccione si el cliente es Persona Natural o Empresa / Persona Jurídica.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('tipo_persona')
                                    ->label('Tipo de Entidad')
                                    ->options([
                                        'natural' => 'Persona Natural (Física)',
                                        'juridica' => 'Persona Jurídica (Empresa / Sociedad)',
                                    ])
                                    ->default('natural')
                                    ->required()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::User),

                                Select::make('catalogo_id')
                                    ->label('Clasificación / Tipo de Cliente')
                                    ->placeholder('Seleccione clasificación')
                                    ->prefixIcon(Heroicon::Tag)
                                    ->options(fn () => CachedOptions::catalogosPorVarios(['TIPO_CLIENTE', 'tipo_cliente']))
                                    ->required()
                                    ->native(false)
                                    ->searchable(),
                            ]),
                    ]),

                Section::make('Información Personal (Persona Natural)')
                    ->icon(Heroicon::User)
                    ->description('Nombres y apellidos completos.')
                    ->visible(fn (Get $get): bool => $get('tipo_persona') !== 'juridica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('primer_nombre')
                                    ->label('Primer Nombre')
                                    ->placeholder('ej. Juan')
                                    ->required(fn (Get $get): bool => $get('tipo_persona') !== 'juridica')
                                    ->prefixIcon(Heroicon::User)
                                    ->maxLength(100),

                                TextInput::make('segundo_nombre')
                                    ->label('Segundo Nombre')
                                    ->placeholder('ej. Carlos')
                                    ->prefixIcon(Heroicon::User)
                                    ->maxLength(100),

                                TextInput::make('primer_apellido')
                                    ->label('Primer Apellido')
                                    ->placeholder('ej. Pérez')
                                    ->required(fn (Get $get): bool => $get('tipo_persona') !== 'juridica')
                                    ->prefixIcon(Heroicon::User)
                                    ->maxLength(100),

                                TextInput::make('segundo_apellido')
                                    ->label('Segundo Apellido')
                                    ->placeholder('ej. Gómez')
                                    ->prefixIcon(Heroicon::User)
                                    ->maxLength(100),
                            ]),
                    ]),

                Section::make('Información de la Empresa (Persona Jurídica)')
                    ->icon(Heroicon::BuildingOffice2)
                    ->description('Razón social y nombre comercial de la sociedad.')
                    ->visible(fn (Get $get): bool => $get('tipo_persona') === 'juridica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('razon_social')
                                    ->label('Razón Social / Nombre Legal')
                                    ->placeholder('ej. Inversiones Bugambilias S.A.')
                                    ->required(fn (Get $get): bool => $get('tipo_persona') === 'juridica')
                                    ->prefixIcon(Heroicon::BuildingOffice2)
                                    ->maxLength(150),

                                TextInput::make('primer_nombre')
                                    ->label('Nombre Comercial')
                                    ->placeholder('ej. Hotel & Suites Bugambilias')
                                    ->required(fn (Get $get): bool => $get('tipo_persona') === 'juridica')
                                    ->prefixIcon(Heroicon::Tag)
                                    ->maxLength(100),
                            ]),
                    ]),

                Section::make('Documentación e Identificación Legal')
                    ->icon(Heroicon::Identification)
                    ->description('Documentos de identidad o cédula / RUC.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('tipo_identificacion')
                                    ->label('Tipo de Documento')
                                    ->options(TipoIdentificacion::class)
                                    ->prefixIcon(Heroicon::DocumentText)
                                    ->native(false),

                                TextInput::make('numero_identificacion')
                                    ->label('Número de Identificación / RUC')
                                    ->placeholder('ej. 001-123456-0000X o RUC J03100000123')
                                    ->prefixIcon(Heroicon::Hashtag)
                                    ->maxLength(30),

                                Select::make('pais_id')
                                    ->label('País de Origen / Nacionalidad')
                                    ->placeholder('Seleccione un país')
                                    ->prefixIcon(Heroicon::GlobeAmericas)
                                    ->options(Pais::pluck('nombre', 'id'))
                                    ->searchable()
                                    ->native(false),

                                DatePicker::make('fecha_nacimiento')
                                    ->label('Fecha de Nacimiento / Constitución')
                                    ->placeholder('dd/mm/aaaa')
                                    ->prefixIcon(Heroicon::Calendar)
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Contacto y Dirección')
                    ->icon(Heroicon::Phone)
                    ->description('Teléfono, correo electrónico y domicilio fiscal.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('telefono')
                                    ->label('Teléfono / Celular')
                                    ->placeholder('ej. +505 8888 8888')
                                    ->prefixIcon(Heroicon::Phone)
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('email')
                                    ->label('Correo Electrónico (Opcional)')
                                    ->placeholder('ej. cliente@ejemplo.com')
                                    ->prefixIcon(Heroicon::Envelope)
                                    ->email()
                                    ->nullable()
                                    ->unique(table: 'users', column: 'email', ignorable: fn ($record) => $record?->user),

                                TextInput::make('direccion')
                                    ->label('Dirección de Domicilio / Fiscal')
                                    ->placeholder('ej. Frente al parque central, Managua')
                                    ->prefixIcon(Heroicon::MapPin)
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
