<?php

declare(strict_types=1);

namespace Database\Seeders\Tesoreria;

use App\Interactors\Tesoreria\GestionarCaja;
use App\Interactors\Tesoreria\GestionarFondos;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\FondoTesoreria;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class TesoreriaDemoSeeder extends Seeder
{
    public function run(GestionarCaja $cajas, GestionarFondos $fondos): void
    {
        $gerente = User::query()->where('email', 'gerente@hotel.com')->firstOrFail();
        $cajero = User::query()->where('email', 'recepcion@hotel.com')->firstOrFail();
        $moneda = Moneda::query()->where('codigo', 'NIO')->firstOrFail();
        $habilitada = config('tesoreria.habilitado');
        config(['tesoreria.habilitado' => true]);

        try {
            Carbon::withTestNow(now()->subDays(7)->setTime(7, 0), function () use ($cajas, $fondos, $gerente, $cajero, $moneda): void {
                $caja = $cajas->crear($gerente->id, 'RECEP-DEMO', 'Caja de recepción', 'Recepción', [$moneda->id]);
                $fondos->crear($gerente->id, 'BOVEDA-DEMO', 'Bóveda de demostración', 'boveda', $moneda->id, $gerente->id, 5000.0, 'Fondo inicial de demostración', 0.0, (string) Str::uuid());
                $fondos->crear($gerente->id, 'CHICA-DEMO', 'Caja chica de demostración', 'caja_chica', $moneda->id, $cajero->id, 0.0, 'Fondo abastecido desde bóveda', 300.0, (string) Str::uuid());
                $boveda = FondoTesoreria::query()->where('codigo', 'BOVEDA-DEMO')->firstOrFail();
                $chica = FondoTesoreria::query()->where('codigo', 'CHICA-DEMO')->firstOrFail();
                $fondos->transferir($gerente->id, $boveda->id, $chica->id, 1000.0, 'Dotación de caja chica', (string) Str::uuid());

                $conteo = [['moneda_id' => $moneda->id, 'importe' => 500.0]];
                $turno = $cajas->abrir($cajero->id, $caja->id, $conteo, 'Fondo de cambio entregado por administración', (string) Str::uuid());
                Carbon::withTestNow(now()->addHours(8), function () use ($cajas, $cajero, $turno, $conteo): void {
                    $cajas->cerrar($cajero->id, $turno->id, $conteo);
                });
            });
        } finally {
            config(['tesoreria.habilitado' => $habilitada]);
        }
    }
}
