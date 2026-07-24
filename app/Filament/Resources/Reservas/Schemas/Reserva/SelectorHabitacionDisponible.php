<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

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
                ->label('Ambiente / Espacio / Mesa')
                ->placeholder('Seleccione ambiente, espacio o mesa')
                ->options(function () {
                    return Espacio::all()->mapWithKeys(fn ($e) => [$e->id => $e->getNombreCompleto()]);
                })
                ->searchable()
                ->preload()
                ->nullable()
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
                ->columnSpan($columnSpan),
        ];
    }
}
