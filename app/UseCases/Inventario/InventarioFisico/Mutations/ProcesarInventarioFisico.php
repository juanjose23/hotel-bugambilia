<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\InventarioFisico\Mutations;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\InventarioFisico;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcesarInventarioFisico
{
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
            if (! isset($datosHoja['sheets']) || empty($datosHoja['sheets'])) {
                throw new RuntimeException('La hoja de cálculo no contiene datos válidos.');
            }

            // Get first sheet
            $sheet = reset($datosHoja['sheets']);
            $cellData = $sheet['cellData'] ?? [];

            $lotesAjustados = 0;

            foreach ($cellData as $rowIndex => $row) {
                // Skip header row
                if ((string) $rowIndex === '0' || ! is_array($row)) {
                    continue;
                }

                /** @var array<int|string, mixed> $row */
                $loteIdCell = $row[0] ?? $row['0'] ?? null;
                $loteIdVal = is_array($loteIdCell) && isset($loteIdCell['v']) ? $loteIdCell['v'] : null;
                if ($loteIdVal === null || ! is_numeric($loteIdVal)) {
                    continue;
                }

                $loteId = (int) $loteIdVal;
                $lote = Lote::find($loteId);
                if (! $lote) {
                    continue;
                }

                $cantidadSistemaCell = $row[4] ?? $row['4'] ?? null;
                $cantidadFisicaCell = $row[5] ?? $row['5'] ?? null;
                $notasCell = $row[7] ?? $row['7'] ?? null;

                $cantidadSistemaVal = is_array($cantidadSistemaCell) && isset($cantidadSistemaCell['v']) ? $cantidadSistemaCell['v'] : 0.0;
                $cantidadFisicaVal = is_array($cantidadFisicaCell) && isset($cantidadFisicaCell['v']) ? $cantidadFisicaCell['v'] : 0.0;

                $cantidadSistema = is_numeric($cantidadSistemaVal) ? (float) $cantidadSistemaVal : 0.0;
                $cantidadFisica = is_numeric($cantidadFisicaVal) ? (float) $cantidadFisicaVal : 0.0;
                $notas = is_array($notasCell) && isset($notasCell['v']) && is_string($notasCell['v']) ? $notasCell['v'] : '';

                if (abs($cantidadFisica - $cantidadSistema) < 0.0001) {
                    continue; // Perfect match, no adjustments needed
                }

                $discrepancia = $cantidadFisica - $cantidadSistema;

                // Update the lote available quantity
                $lote->cantidad_disponible = $cantidadFisica;
                if ($lote->cantidad_disponible <= 0) {
                    $lote->estado = EstadoLote::Agotado;
                }
                $lote->save();

                // Sincronizar el stock en inv_stock
                $stock = Stock::where([
                    'lote_id' => $lote->id,
                    'ubicacion_id' => $lote->ubicacion_id,
                ])->first();

                if ($stock) {
                    if ($cantidadFisica <= 0.0) {
                        $stock->delete();
                    } else {
                        $stock->cantidad = $cantidadFisica;
                        $stock->save();
                    }
                } elseif ($cantidadFisica > 0.0) {
                    Stock::create([
                        'producto_id' => $lote->producto_id,
                        'producto_variante_id' => $lote->producto_variante_id,
                        'lote_id' => $lote->id,
                        'ubicacion_id' => $lote->ubicacion_id,
                        'cantidad' => $cantidadFisica,
                    ]);
                }

                // Create stock movement (MOV_AJUSTE)
                $cantidadMovimiento = abs($discrepancia);

                MovimientoStock::create([
                    'tipo' => 'MOV_AJUSTE',
                    'lote_id' => $lote->id,
                    'producto_id' => $lote->producto_id,
                    'cantidad' => $cantidadMovimiento,
                    // If discrepancia > 0 (sobrante): origen is null, destino is Lote's location
                    // If discrepancia < 0 (faltante): origen is Lote's location, destino is null
                    'ubicacion_origen_id' => $discrepancia < 0 ? $lote->ubicacion_id : null,
                    'ubicacion_destino_id' => $discrepancia > 0 ? $lote->ubicacion_id : null,
                    'documento_tipo' => 'inventario_fisico',
                    'documento_id' => $inventario->id,
                    'referencia' => "Ajuste Conciliación Física {$inventario->codigo}",
                    'creado_por_id' => $creadoPorId,
                    'notas' => trim('Ajuste por inventario físico. Faltante/Sobrante: '.$discrepancia.'. Notas: '.$notas),
                ]);

                $lotesAjustados++;
            }

            // Mark session as processed
            $inventario->update([
                'estado' => 'procesado',
            ]);

            // Dispatch database notification to processor
            $user = User::find($creadoPorId) ?? auth()->user();
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
