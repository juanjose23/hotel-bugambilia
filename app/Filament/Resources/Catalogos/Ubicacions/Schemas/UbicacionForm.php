<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Schemas;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Enums\Catalogos\TipoUbicacion;
use App\Models\Catalogos\Ubicacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UbicacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos generales')
                    ->columnSpanFull()
                    ->columns()
                    ->schema([
                        Select::make('padre_id')
                            ->label('Padre')
                            ->nullable()
                            ->searchable()
                            ->helperText('Elemento categoría opcional para estructuras jerárquicas.')
                            ->options(
                                fn ($get) => Ubicacion::query()
                                    ->when($get('id'), fn ($q, $id) => $q->where('id', '!=', $id))
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                                    ->toArray()
                            ),

                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(200)
                            ->prefixIcon(Heroicon::Ticket)
                            ->helperText('Nombre legible que se mostrará en la interfaz.'),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->helperText('Descripción opcional para documentación interna.'),

                        Grid::make()
                            ->columns(3)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('tipo')
                                    ->label('Tipo')
                                    ->required()
                                    ->searchable()
                                    ->prefixIcon(Heroicon::Square3Stack3d)
                                    ->helperText('Nivel jerárquico físico: edificio, piso, sector o zona.')
                                    ->options(TipoUbicacion::options()),
                                TextInput::make('orden')
                                    ->label('Orden')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->rules([
                                        fn ($get, $component): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $component) {
                                            $exists = Ubicacion::where('padre_id', $get('padre_id'))
                                                ->where('orden', $value)
                                                ->when($component->getRecord()?->id, fn ($q, $id) => $q->where('id', '!=', $id))
                                                ->exists();

                                            if ($exists) {
                                                $fail('Ya existe una ubicación con este orden en el mismo nivel padre.');
                                            }
                                        },
                                    ])
                                    ->prefixIcon(Heroicon::ArrowDownCircle),
                                Select::make('estado')
                                    ->label('Estado')
                                    ->options(EstadoCatalogo::options())
                                    ->default(EstadoCatalogo::Activo->value)
                                    ->required()
                                    ->prefixIcon(Heroicon::CheckCircle)
                                    ->helperText('Controla si el elemento está activo y visible.'),
                            ]),
                    ]),
            ]);
    }
}
