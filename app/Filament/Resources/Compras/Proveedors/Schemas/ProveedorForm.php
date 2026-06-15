<?php

namespace App\Filament\Resources\Compras\Proveedors\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\EstadoCatalogo;
use App\Enums\Personas\TipoIdentificacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ProveedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Persona')
                    ->description('Información general del proveedor (persona natural o jurídica)')
                    ->columns()
                    ->columnSpanFull()
                    ->schema([
                        Select::make('tipo_persona')
                            ->label('Tipo de Persona')
                            ->options([
                                'natural' => 'Persona Natural',
                                'juridica' => 'Persona Jurídica',
                            ])
                            ->default('natural')
                            ->required()
                            ->live()
                            ->prefixIcon(Heroicon::User)
                            ->helperText('Seleccione si el proveedor es una persona física o una empresa.'),

                        TextInput::make('persona.primer_nombre')
                            ->label('Primer Nombre / Razón Social')
                            ->placeholder('Nombre o razón social')
                            ->maxLength(100)
                            ->required()
                            ->prefixIcon(Heroicon::Tag),

                        TextInput::make('persona.segundo_nombre')
                            ->label('Segundo Nombre')
                            ->placeholder('Opcional')
                            ->maxLength(100)
                            ->nullable()
                            ->prefixIcon(Heroicon::Tag),

                        TextInput::make('personaNatural.primer_apellido')
                            ->label('Primer Apellido')
                            ->placeholder('Apellido')
                            ->maxLength(100)
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'natural')
                            ->required(fn ($get): bool => $get('tipo_persona') === 'natural')
                            ->prefixIcon(Heroicon::Identification),

                        TextInput::make('personaNatural.segundo_apellido')
                            ->label('Segundo Apellido')
                            ->placeholder('Opcional')
                            ->maxLength(100)
                            ->nullable()
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'natural')
                            ->prefixIcon(Heroicon::Identification),

                        TextInput::make('personaJuridica.razon_social')
                            ->label('Razón Social')
                            ->placeholder('Nombre legal de la empresa')
                            ->maxLength(150)
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'juridica')
                            ->required(fn ($get): bool => $get('tipo_persona') === 'juridica')
                            ->prefixIcon(Heroicon::BuildingOffice2),

                        Select::make('persona.pais_id')
                            ->label('País')
                            ->placeholder('Seleccionar país')
                            ->relationship('persona.pais', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon(Heroicon::GlobeAmericas),

                        Select::make('personaNatural.tipo_identificacion')
                            ->label('Tipo de Identificación')
                            ->options(TipoIdentificacion::options())
                            ->nullable()
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'natural')
                            ->prefixIcon(Heroicon::Identification),

                        TextInput::make('personaNatural.numero_identificacion')
                            ->label('Número de Identificación')
                            ->placeholder('Número de documento')
                            ->maxLength(30)
                            ->nullable()
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'natural'),

                        Select::make('personaJuridica.tipo_identificacion')
                            ->label('Tipo de Identificación Fiscal')
                            ->options(TipoIdentificacion::options())
                            ->nullable()
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'juridica')
                            ->prefixIcon(Heroicon::Identification),

                        TextInput::make('personaJuridica.numero_identificacion')
                            ->label('Número de Identificación Fiscal')
                            ->placeholder('RUC, NIT, etc.')
                            ->maxLength(30)
                            ->nullable()
                            ->hidden(fn ($get): bool => $get('tipo_persona') !== 'juridica'),

                        TextInput::make('persona.telefono')
                            ->label('Teléfono')
                            ->placeholder('Teléfono de contacto')
                            ->maxLength(20)
                            ->nullable()
                            ->prefixIcon(Heroicon::Phone),

                    ]),

                Section::make('Información Comercial')
                    ->description('Datos específicos del proveedor')
                    ->columns()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->placeholder('Se autogenera si se deja vacío')
                            ->maxLength(20)
                            ->required(fn (string $operation): bool => $operation !== 'create')
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->dehydrated(fn (string $operation): bool => $operation !== 'edit')
                            ->prefixIcon(Heroicon::QrCode)
                            ->helperText('Código único. En creación se genera automáticamente si se deja en blanco.'),

                        Select::make('tipo_proveedor_id')
                            ->label('Tipo de Proveedor')
                            ->placeholder('Seleccionar tipo')
                            ->relationship(
                                name: 'tipoProveedor',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_PROVEEDOR->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon(Heroicon::GlobeAlt),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoCatalogo::options())
                            ->default(EstadoCatalogo::Activo->value)
                            ->required()
                            ->columnSpanFull()
                            ->prefixIcon(Heroicon::CheckCircle),

                        Section::make('Datos Comerciales')
                            ->description('Dirección fiscal y notas internas del proveedor')
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Textarea::make('direccion_fiscal')
                                    ->label('Dirección Fiscal')
                                    ->placeholder('Dirección fiscal del proveedor')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpanFull(),

                                Textarea::make('notas')
                                    ->label('Notas')
                                    ->placeholder('Observaciones adicionales')
                                    ->nullable()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
