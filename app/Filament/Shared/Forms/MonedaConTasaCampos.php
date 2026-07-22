<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;
use App\Support\CachedOptions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;

class MonedaConTasaCampos
{
    public function __construct() {}

    /**
     * Retorna los campos de selector de moneda e input oculto de tasa de cambio.
     *
     * @return array<int, Component>
     */
    public function make(
        string $monedaColumn = 'moneda_id',
        string $tasaColumn = 'tasa_cambio',
        string $monedaLabel = 'Moneda'
    ): array {
        return [
            Select::make($monedaColumn)
                ->label($monedaLabel)
                ->options(fn () => CachedOptions::monedas())
                ->required()
                ->default(fn () => Moneda::where('codigo', 'USD')->first()?->id)
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state, $set) use ($tasaColumn) {
                    if (! $state) {
                        return;
                    }
                    $moneda = Moneda::find((int) $state);
                    if ($moneda) {
                        if ($moneda->codigo === 'NIO') {
                            $set($tasaColumn, 1.0000);
                        } else {
                            $tasa = TasaCambio::obtenerTasa(now(), $moneda->codigo, 'NIO');
                            $set($tasaColumn, $tasa);
                        }
                    }
                })
                ->prefixIcon(Heroicon::Banknotes)
                ->native(false),

            Hidden::make($tasaColumn)
                ->default(function ($get) use ($monedaColumn) {
                    $monedaId = $get($monedaColumn);
                    if ($monedaId) {
                        $moneda = Moneda::find((int) $monedaId);
                        if ($moneda) {
                            if ($moneda->codigo === 'NIO') {
                                return 1.0000;
                            }

                            return TasaCambio::obtenerTasa(now(), $moneda->codigo, 'NIO');
                        }
                    }

                    return 1.0000;
                }),
        ];
    }
}
