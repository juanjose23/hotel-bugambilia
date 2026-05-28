<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Schemas;

use App\Enums\CatalogoTipo;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Catalogos\Catalogo;
use App\UseCases\Habitaciones\Mutations\GenerarCodigoHabitacion;
use App\UseCases\Habitaciones\Mutations\GenerarSlugHabitacion;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class HabitacionForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información Básica')
                    ->columnSpanFull()
                    ->description('Datos básicos de identificación y clasificación de la habitación')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(3)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre / Identificador')
                            ->placeholder('Ej. Habitación Presidencial')
                            ->prefixIcon(Heroicon::Home)
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, string $operation) {
                                if ($operation !== 'create') {
                                    return;
                                }

                                $set('slug', app(GenerarSlugHabitacion::class)->execute((string) $state));
                                $set('codigo', app(GenerarCodigoHabitacion::class)->execute());
                            })
                            ->columnSpan(2),

                        TextInput::make('numero')
                            ->label('Número')
                            ->placeholder('Ej. 101')
                            ->prefixIcon(Heroicon::Hashtag)
                            ->maxLength(10)
                            ->helperText('Número visible de la habitación (opcional).')
                            ->columnSpan(1),

                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->placeholder('Seleccione una categoría')
                            ->relationship('categoria', 'nombre', fn (Builder $query) => $query->whereHas(
                                'catalogoTipo',
                                fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_HABITACION->value)
                            ))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::RectangleStack)
                            ->columnSpan(1),

                        Select::make('ubicacion_id')
                            ->label('Ubicación')
                            ->placeholder('Seleccione una ubicación')
                            ->relationship('ubicacion', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::MapPin)
                            ->columnSpan(1),

                        Select::make('estado')
                            ->label('Estado')
                            ->placeholder('Seleccione un estado')
                            ->options(EstadoHabitacion::class)
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::ArrowPath)
                            ->columnSpan(1),

                        Hidden::make('codigo')
                            ->default(fn () => app(GenerarCodigoHabitacion::class)->execute()),

                        Hidden::make('slug'),
                    ]),

                Section::make('Capacidad y Dimensiones')
                    ->columnSpanFull()
                    ->description('Especifique la capacidad de huéspedes y las medidas de la habitación')
                    ->icon(Heroicon::Users)
                    ->relationship('detalle')
                    ->columns(3)
                    ->schema([
                        TextInput::make('capacidad_adultos')
                            ->label('Capacidad Adultos')
                            ->placeholder('Ej. 2')
                            ->prefixIcon(Heroicon::User)
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(2),

                        TextInput::make('capacidad_ninos')
                            ->label('Capacidad Niños')
                            ->placeholder('Ej. 0')
                            ->prefixIcon(Heroicon::UserGroup)
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('medidas')
                            ->label('Metros Cuadrados')
                            ->placeholder('Ej. 25.50')
                            ->prefixIcon(Heroicon::ArrowsPointingOut)
                            ->numeric()
                            ->step(0.01)
                            ->suffix('m²')
                            ->helperText('Superficie útil de la habitación.'),

                        Select::make('vistas')
                            ->label('Vistas Disponibles')
                            ->placeholder('Seleccione las vistas')
                            ->multiple()
                            ->options(fn () => Catalogo::whereHas(
                                'catalogoTipo',
                                fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_VISTA->value)
                            )->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->prefixIcon(Heroicon::Photo)
                            ->columnSpanFull()
                            ->helperText('Vistas panorámicas visibles desde la habitación.'),
                    ]),
                Section::make('Descripción')
                    ->columnSpanFull()
                    ->description('Detalle las notas, características especiales y detalles de confort.')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        RichEditor::make('descripcion')
                            ->hiddenLabel()
                            ->placeholder('Describa las características principales de la habitación (ej: Jacuzzi, cama King Size, balcón con vista directa)...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
