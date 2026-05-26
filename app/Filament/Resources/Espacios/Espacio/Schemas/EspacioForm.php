<?php

declare(strict_types=1);

namespace App\Filament\Resources\Espacios\Espacio\Schemas;

use App\Enums\CatalogoTipo;
use App\Enums\Espacios\EstadoEspacio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class EspacioForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información General')
                ->description('Datos básicos de identificación y clasificación del espacio')
                ->icon(Heroicon::InformationCircle)
                ->columns(2)
                ->schema([
                    TextInput::make('codigo')
                        ->label('Código')
                        ->prefixIcon(Heroicon::Hashtag)
                        ->required()
                        ->unique(ignorable: fn ($record) => $record)
                        ->maxLength(30)
                        ->placeholder('Ej. ESP-2026-0001')
                        ->helperText('Código único del espacio.'),

                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->prefixIcon(Heroicon::Tag)
                        ->required()
                        ->maxLength(150)
                        ->placeholder('Ej. Restaurante Los Jardines'),

                    Select::make('tipo_espacio_id')
                        ->label('Tipo de Espacio')
                        ->placeholder('Seleccione un tipo')
                        ->relationship(
                            name: 'tipoEspacio',
                            titleAttribute: 'nombre',
                            modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                'catalogoTipo',
                                fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_ESPACIO->value)
                            )
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false)
                        ->prefixIcon(Heroicon::RectangleStack)
                        ->helperText('Clasificación del espacio (Restaurante, Gimnasio, Salón de Eventos, etc.).'),

                    Select::make('ubicacion_id')
                        ->label('Ubicación Física')
                        ->placeholder('Seleccione ubicación')
                        ->relationship('ubicacion', 'nombre')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->prefixIcon(Heroicon::MapPin)
                        ->helperText('Ubicación del espacio dentro del hotel.'),

                    Select::make('estado')
                        ->label('Estado')
                        ->placeholder('Seleccione un estado')
                        ->options(EstadoEspacio::class)
                        ->required()
                        ->native(false)
                        ->prefixIcon(Heroicon::ArrowPath)
                        ->default(EstadoEspacio::Activo->value),
                ]),

            Section::make('Capacidad y Horario')
                ->description('Especifique la capacidad de personas y el horario de operación')
                ->icon(Heroicon::Clock)
                ->columns(2)
                ->schema([
                    TextInput::make('capacidad')
                        ->label('Capacidad Máxima')
                        ->prefixIcon(Heroicon::Users)
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Ej. 150')
                        ->helperText('Número máximo de personas que puede albergar.'),

                    TextInput::make('horario')
                        ->label('Horario de Operación')
                        ->prefixIcon(Heroicon::Clock)
                        ->maxLength(100)
                        ->placeholder('Ej. Lun-Dom 7:00 AM - 10:00 PM')
                        ->helperText('Horario regular de funcionamiento.'),
                ]),

            Section::make('Descripción')
                ->description('Detalles y notas adicionales sobre el espacio')
                ->icon(Heroicon::DocumentText)
                ->schema([
                    Textarea::make('descripcion')
                        ->hiddenLabel()
                        ->placeholder('Describa las características principales del espacio, servicios incluidos, restricciones, etc.')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
