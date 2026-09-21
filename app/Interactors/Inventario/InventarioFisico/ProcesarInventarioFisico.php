<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\InventarioFisico;

use App\BusinessLogic\Inventario\ConciliadorInventarioFisico;
use App\Enums\Inventario\EstadoInventarioFisico;
use App\Events\Inventario\InventarioFisicoProcesado;
use App\Repository\Models\Inventario\InventarioFisico;
use App\Repository\Persistencia\Inventario\InventarioFisicoRepositorioInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class ProcesarInventarioFisico
{
    public function __construct(
        private ConciliadorInventarioFisico $conciliador,
        private InventarioFisicoRepositorioInterface $repositorio,
    ) {}

    /**
     * Executes the reconciliation process of physical vs system inventory.
     * Computes discrepancies, updates Lotes, records stock movements (MOV_AJUSTE) and closes the session.
     */
    public function ejecutar(InventarioFisico $inventario, int $creadoPorId): void
    {
        if ($inventario->estado === EstadoInventarioFisico::Procesado) {
            throw new RuntimeException("El inventario físico {$inventario->codigo} ya ha sido procesado y no puede modificarse.");
        }

        DB::transaction(function () use ($inventario, $creadoPorId): void {
            $datosHoja = $inventario->datos_hoja;
            if (! is_array($datosHoja)) {
                throw new RuntimeException('El inventario físico no tiene datos de hoja válidos.');
            }

            $this->conciliador->conciliar(
                datosHoja: $datosHoja,
                inventarioId: $inventario->id,
                codigo: $inventario->codigo,
                creadoPorId: $creadoPorId,
            );

            $this->repositorio->actualizarEstado($inventario, EstadoInventarioFisico::Procesado);

            event(new InventarioFisicoProcesado(
                inventarioFisico: $inventario,
                procesadoPorId: $creadoPorId,
            ));
        });
    }
}
