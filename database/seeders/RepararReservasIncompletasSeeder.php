<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RepararReservasIncompletasSeeder extends Seeder
{
    public function __construct(
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    public function run(): void
    {
        $reparadas = 0;
        $omitidas = 0;

        Reserva::query()
            ->doesntHave('detalles')
            ->orderBy('id')
            ->chunkById(100, function ($reservas) use (&$reparadas, &$omitidas): void {
                foreach ($reservas as $reserva) {
                    try {
                        DB::transaction(function () use ($reserva, &$reparadas): void {
                            $this->reservas->detallePrincipalDe($reserva);
                            $this->asegurarHistorialInicial($reserva);
                            $reparadas++;
                        });
                    } catch (InvalidArgumentException) {
                        $omitidas++;
                    }
                }
            });

        $this->command->info("Reservas incompletas reparadas: {$reparadas}. Omitidas por falta de recurso principal: {$omitidas}.");
    }

    private function asegurarHistorialInicial(Reserva $reserva): void
    {
        if (ReservaEstadoHistorial::query()->where('reserva_id', $reserva->id)->exists()) {
            return;
        }

        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => null,
            'estado_nuevo' => $reserva->estado,
            'motivo' => 'Reserva reparada desde datos legacy',
        ]);
    }
}
