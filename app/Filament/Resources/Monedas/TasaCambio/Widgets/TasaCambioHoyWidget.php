<?php

namespace App\Filament\Resources\Monedas\TasaCambio\Widgets;

use App\Models\Monedas\Moneda;
use App\Models\Monedas\TasaCambio;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TasaCambioHoyWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $origen = Moneda::where('codigo', 'USD')->first();
        $destino = Moneda::where('codigo', 'NIO')->first();

        $tasa = 1.0;
        $fechaLabel = 'Sin tasa registrada';
        $description = 'Por favor, configure las monedas en el catálogo.';
        $color = 'danger';
        $icon = 'heroicon-m-x-circle';

        if ($origen && $destino) {
            $hoyString = now()->toDateString();
            $tasaRegistro = TasaCambio::where('fecha', $hoyString)
                ->where('moneda_origen_id', $origen->id)
                ->where('moneda_destino_id', $destino->id)
                ->first();

            if ($tasaRegistro) {
                $tasa = (float) $tasaRegistro->tasa;
                $fechaLabel = 'Tasa Oficial del Día ('.now()->format('d/m/Y').')';
                $description = 'Tasa registrada correctamente hoy.';
                $color = 'success';
                $icon = 'heroicon-m-check-circle';
            } else {
                $ultima = TasaCambio::where('moneda_origen_id', $origen->id)
                    ->where('moneda_destino_id', $destino->id)
                    ->orderBy('fecha', 'desc')
                    ->first();

                if ($ultima) {
                    $tasa = (float) $ultima->tasa;
                    $fechaLabel = "Tasa en Uso (Última: {$ultima->fecha->format('d/m/Y')})";
                    $description = 'No se ha registrado tasa para hoy. Utilizando fallback.';
                    $color = 'warning';
                    $icon = 'heroicon-m-exclamation-triangle';
                }
            }
        }

        $formatoTasaDirecta = '1 USD = '.number_format($tasa, 4).' NIO';
        $formatoTasaInversa = '1 NIO = '.number_format($tasa > 0 ? (1 / $tasa) : 0, 4).' USD';

        return [
            Stat::make($fechaLabel, $formatoTasaDirecta)
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color),

            Stat::make('Conversión Inversa', $formatoTasaInversa)
                ->description('Valor del Córdoba en Dólares Estadounidenses.')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('info'),
        ];
    }
}
