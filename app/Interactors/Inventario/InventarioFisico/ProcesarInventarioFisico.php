<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\InventarioFisico;

use App\BusinessLogic\Inventario\ConciliadorInventarioFisico;
use App\Enums\Inventario\EstadoInventarioFisico;
use App\Events\Inventario\InventarioFisicoProcesado;
use App\Repository\Models\Inventario\InventarioFisico;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcesarInventarioFisico
{
    public function __construct(
        private readonly ConciliadorInventarioFisico $conciliador,
    ) {}

    /**
     * Executes the reconciliation process of physical vs system inventory.
     * Computes discrepancies, updates Lotes, records stock movements (MOV_AJUSTE) and closes the session.
     */
    public function execute(InventarioFisico $inventario, int $creadoPorId): void
    {
        if ($inventario->estado === EstadoInventarioFisico::Procesado) {
            throw new RuntimeException("El inventario físico {$inventario->codigo} ya ha sido procesado y no puede modificarse.");
        }

        DB::transaction(function () use ($inventario, $creadoPorId) {
            $datosHoja = $inventario->datos_hoja;
            if (! is_array($datosHoja)) {
                throw new RuntimeException('El inventario físico no tiene datos de hoja válidos.');
            }
            $lotesAjustados = $this->conciliador->conciliar(
                datosHoja: $datosHoja,
                inventarioId: $inventario->id,
                codigo: $inventario->codigo,
                creadoPorId: $creadoPorId,
            );

            $inventario->update(['estado' => 'procesado']);

            event(new InventarioFisicoProcesado(
                inventarioFisico: $inventario,
                procesadoPorId: $creadoPorId,
            ));

            $user = User::query()->find($creadoPorId) ?? auth()->user();
            if ($user) {
                Notification::make()
                    ->title('Conciliación de Inventario Completada')
                    ->body("Se ha procesado exitosamente la toma {$inventario->codigo}. Se ajustaron {$lotesAjustados} lotes con diferencias.")
                    ->success()
                    ->sendToDatabase($user);
            }
        });
    }
}
