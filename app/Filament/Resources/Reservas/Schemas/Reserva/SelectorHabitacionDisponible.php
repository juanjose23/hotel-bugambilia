<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class SelectorHabitacionDisponible
{
    /** @return array<int, Select|TextInput> */
    public static function make(int $columnSpan = 1): array
    {
        return [
            Select::make('habitacion_id')
                ->label('Habitación Asignada')
                ->placeholder('Seleccione habitación')
                ->options(function () {
                    return Habitacion::with('categoria')->get()->mapWithKeys(function ($h) {
                        $cat = $h->categoria->nombre ?? 'Sin Categ.';

                        return [$h->id => "{$h->nombre} ({$cat})"];
                    });
                })
                ->searchable()
                ->preload()
                ->nullable()
                ->disabledOn('edit')
                ->native(false)
                ->visible(fn ($get) => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                ->columnSpan($columnSpan),

            Select::make('espacio_id')
                ->label(fn ($get): string => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value ? 'Mesa / Espacio del Restaurante' : 'Ambiente / Espacio del Hotel')
                ->placeholder('Seleccione mesa o espacio')
                ->options(function (): array {
                    $opcionesAgrupadas = [
                        'Mesas del Restaurante' => [],
                        'Espacios y Áreas del Hotel' => [],
                    ];

                    foreach (Espacio::with('padre')->get() as $espacio) {
                        $label = $espacio->getNombreCompleto();
                        if ($espacio->capacidad_personas > 0) {
                            $label .= " (Cap: {$espacio->capacidad_personas} pers.)";
                        }

                        if ($espacio->tipo === TipoEspacio::MESA || $espacio->tipo === TipoEspacio::RESTAURANTE) {
                            $opcionesAgrupadas['Mesas del Restaurante'][$espacio->id] = $label;
                        } else {
                            $opcionesAgrupadas['Espacios y Áreas del Hotel'][$espacio->id] = $label;
                        }
                    }

                    return array_filter($opcionesAgrupadas, fn (array $grupo): bool => $grupo !== []);
                })
                ->searchable()
                ->preload()
                ->nullable()
                ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value || $get('tipo_reserva') === TipoReserva::PAQUETE->value)
                ->disabledOn('edit')
                ->native(false)
                ->columnSpan($columnSpan),

            Select::make('servicio_id')
                ->label('Servicio Especial')
                ->placeholder('Seleccione servicio')
                ->options(fn () => Servicio::query()->pluck('nombre', 'id')->mapWithKeys(fn ($v, $k) => [(int) $k => $v])->all())
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::SERVICIO->value || $get('tipo_reserva') === TipoReserva::PAQUETE->value)
                ->disabledOn('edit')
                ->native(false)
                ->columnSpan($columnSpan),

            TextInput::make('total')
                ->label('Monto Total')
                ->numeric()
                ->prefix('C$')
                ->default(0.00)
                ->required()
                ->disabled()
                ->columnSpan($columnSpan),

            Select::make('promocion_id')
                ->label('Promoción aplicada')
                ->placeholder('Sin promoción')
                ->options(fn () => Promocion::query()->vigentes()->where('web', true)->orderBy('orden')->pluck('nombre', 'id')->mapWithKeys(fn ($nombre, $id) => [(int) $id => $nombre])->all())
                ->searchable()
                ->preload()
                ->nullable()
                ->native(false)
                ->live()
                ->columnSpan($columnSpan),
        ];
    }
}
