<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Monedas\ObtenerMonedasQuery;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class InformacionGeneralSeccion
{
    public static function make(): Section
    {
        return Section::make('Información de la Reserva')
            ->columnSpanFull()
            ->icon(Heroicon::InformationCircle)
            ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
            ->schema([
                TextInput::make('codigo_reserva')
                    ->label('Código de Reserva')
                    ->placeholder('Generación automática')
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(1),

                Select::make('tipo_reserva')
                    ->label('Tipo de Reserva')
                    ->options(TipoReserva::options())
                    ->default(TipoReserva::HABITACION->value)
                    ->required()
                    ->validationMessages([
                        'required' => 'Seleccione el tipo de reserva que desea registrar.',
                    ])
                    ->disabled(fn (string $operation): bool => $operation === 'create'
                        && filled(request()->query('tipo_reserva') ?? request()->query('tipo')))
                    ->live()
                    ->native(false)
                    ->columnSpan(1),

                Select::make('moneda_id')
                    ->label('Moneda de Cotización')
                    ->options(fn (): array => app(ObtenerMonedasQuery::class)->ejecutar()
                        ->mapWithKeys(fn ($moneda): array => [$moneda->id => sprintf('%s (%s)', $moneda->codigo, $moneda->simbolo ?: $moneda->codigo)])
                        ->all())
                    ->default(fn (): ?int => app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar()?->id)
                    ->required()
                    ->validationMessages([
                        'required' => 'Seleccione la moneda de cotización de la reserva.',
                    ])
                    ->live()
                    ->native(false)
                    ->columnSpan(1),

                Select::make('promocion_id')
                    ->label('Promoción / Oferta Aplicada')
                    ->placeholder('Sin promoción')
                    ->options(fn (): array => Promocion::query()
                        ->vigentes()
                        ->orderBy('orden')
                        ->pluck('nombre', 'id')
                        ->mapWithKeys(fn ($nombre, $id): array => [(int) $id => $nombre])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                        if (! is_numeric($state)) {
                            return;
                        }
                        $promocion = Promocion::with('items')->find((int) $state);
                        if (! $promocion instanceof Promocion) {
                            return;
                        }

                        // 1. Habitaciones del paquete (Principal y adicionales)
                        $habitacionesItems = $promocion->items->where('item_type', Habitacion::class)->values();
                        if ($habitacionesItems->isNotEmpty()) {
                            $set('tipo_reserva', TipoReserva::HABITACION->value);
                            $primeraHab = $habitacionesItems->first();
                            $set('habitacion_id', (int) $primeraHab->item_id);

                            if ($habitacionesItems->count() > 1) {
                                $habAdicionales = [];
                                for ($i = 1; $i < $habitacionesItems->count(); $i++) {
                                    $item = $habitacionesItems[$i];
                                    if ($item !== null) {
                                        $habAdicionales[] = ['habitacion_id' => (int) $item->item_id];
                                    }
                                }
                                $set('habitaciones_adicionales', $habAdicionales);
                            }
                        }

                        // 2. Espacios del paquete (Restaurante / Mesas / Eventos)
                        $espaciosItems = $promocion->items->where('item_type', Espacio::class)->values();
                        if ($espaciosItems->isNotEmpty()) {
                            if ($habitacionesItems->isEmpty()) {
                                $set('tipo_reserva', TipoReserva::RESTAURANTE->value);
                            }
                            $primerEspacio = $espaciosItems->first();
                            $set('espacio_id', (int) $primerEspacio->item_id);

                            if ($espaciosItems->count() > 1) {
                                $espAdicionales = [];
                                for ($i = 1; $i < $espaciosItems->count(); $i++) {
                                    $item = $espaciosItems[$i];
                                    if ($item !== null) {
                                        $espAdicionales[] = ['espacio_id' => (int) $item->item_id];
                                    }
                                }
                                $set('espacios_adicionales', $espAdicionales);
                            }
                        }

                        // 3. Servicios adicionales del paquete
                        $serviciosItems = $promocion->items->where('item_type', Servicio::class)->values();
                        if ($serviciosItems->isNotEmpty()) {
                            if ($habitacionesItems->isEmpty() && $espaciosItems->isEmpty()) {
                                $set('tipo_reserva', TipoReserva::SERVICIO->value);
                                $primerServicio = $serviciosItems->first();
                                $set('servicio_id', (int) $primerServicio->item_id);
                            }

                            $serviciosForm = is_array($get('servicios_adicionales')) ? $get('servicios_adicionales') : [];
                            /** @var array<int, int> $existentesIds */
                            $existentesIds = array_filter(array_map(
                                static fn (mixed $item): int => is_array($item) && is_numeric($item['servicio_id'] ?? null) ? (int) $item['servicio_id'] : 0,
                                $serviciosForm
                            ));

                            foreach ($serviciosItems as $sItem) {
                                if (! in_array((int) $sItem->item_id, $existentesIds, true)) {
                                    $serviciosForm[] = [
                                        'servicio_id' => (int) $sItem->item_id,
                                        'cantidad' => 1,
                                        'observaciones' => "Incluido en paquete: {$promocion->nombre}",
                                    ];
                                }
                            }
                            $set('servicios_adicionales', $serviciosForm);
                        }
                    })
                    ->columnSpan(1),

                Select::make('estado')
                    ->label('Estado Actual')
                    ->options(EstadoReserva::options())
                    ->default(EstadoReserva::PENDIENTE->value)
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->native(false)
                    ->columnSpan(1),
            ]);
    }
}
