<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Recepciones;

use App\Repository\Persistencia\Compras\RecepcionRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class GenerarCodigoRecepcion
{
    public function __construct(
        private RecepcionRepositorioInterface $recepcionRepositorio,
    ) {}

    public function ejecutar(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year): string {
            $ultimo = $this->recepcionRepositorio->obtenerUltimoCodigoPorAno($year);
            $secuencia = 0;

            if ($ultimo && preg_match('/\-(\d+)$/', $ultimo, $coincidencias)) {
                $secuencia = (int) $coincidencias[1];
            }

            return "REC-{$year}-".str_pad((string) ($secuencia + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
