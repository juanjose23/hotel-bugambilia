<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class GenerarCodigoOrdenCompra
{
    public function __construct(
        private OrdenCompraRepositorioInterface $ordenCompraRepositorio,
    ) {}

    public function ejecutar(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year): string {
            $latest = $this->ordenCompraRepositorio->obtenerUltimoCodigoPorAno($year);
            $last = 0;

            if ($latest && preg_match('/\-(\d+)$/', $latest, $matches)) {
                $last = (int) $matches[1];
            }

            return "OC-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
