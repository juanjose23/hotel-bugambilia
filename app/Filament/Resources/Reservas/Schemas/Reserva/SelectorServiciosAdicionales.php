<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Servicios\Servicio;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class SelectorServiciosAdicionales
{
    public static function make(): Section
    {
        return Section::make('Recursos adicionales')
            ->columnSpanFull()
            ->icon(Heroicon::PlusCircle)
            ->description('Agregue los servicios y espacios que forman parte de esta misma reserva.')
            ->columns(2)
            ->schema([
                Repeater::make('servicios_adicionales')
                    ->label('Servicios adicionales')
                    ->addActionLabel('Agregar servicio')
                    ->defaultItems(0)
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('servicio_id')
                            ->label('Servicio')
                            ->placeholder('Seleccione un servicio')
                            ->options(fn () => Servicio::query()
                                ->activos()
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->mapWithKeys(fn ($nombre, $id) => [(int) $id => $nombre])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione el servicio que desea agregar.',
                            ])
                            ->live()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->validationMessages([
                                'required' => 'Indique la cantidad del servicio.',
                                'integer' => 'La cantidad debe ser un número entero.',
                                'min' => 'La cantidad mínima es 1.',
                            ])
                            ->live(onBlur: true),
                    ]),

                Repeater::make('espacios_adicionales')
                    ->label('Espacios adicionales')
                    ->addActionLabel('Agregar espacio')
                    ->defaultItems(0)
                    ->collapsible()
                    ->columns(1)
                    ->schema([
                        Select::make('espacio_id')
                            ->label('Espacio')
                            ->placeholder('Seleccione un espacio')
                            ->options(fn () => Espacio::query()
                                ->with('padre.padre')
                                ->where('reservable', true)
                                ->where('estado', '!=', 0)
                                ->orderBy('nombre')
                                ->get()
                                ->mapWithKeys(fn (Espacio $espacio) => [$espacio->id => $espacio->getNombreCompleto()])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione la mesa o espacio adicional que se unirá a la reserva.',
                            ])
                            ->live()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                        Hidden::make('cantidad')->default(1),
                    ]),
            ]);
    }
}
