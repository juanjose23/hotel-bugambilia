<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonInterface;
use Database\Seeders\Activos\Historico\HistoricoActivosMasivoSeeder;
use Database\Seeders\Compras\Historico\HistoricoComprasMasivoSeeder;
use Database\Seeders\Inventario\Historico\HistoricoInventarioMasivoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Orquestador del histórico masivo concordante 2026.
 *
 * Construye en orden la cadena completa P2P + inventario + activos:
 *
 *   Compras    → Solicitud → Cotización (única elegida) → OC → Recepción
 *   Inventario → Lote por recepción item consumible → MOV_ENTRADA → CONSUMO
 *                → BAJA_CADUCIDAD → stock consistente → devoluciones (~4%)
 *   Activos    → Registro de individualización por item tipo 3 → N activos
 *                unitarios → asignación física → mantenimientos → bajas (5%)
 *
 * Garantías:
 *   - Idempotente: al iniciar elimina TODO lo marcado con prefijos /[HST]/.
 *   - Autosecuenciado: al final sincroniza las secuencias pgsql de las 19
 *     tablas tocadas para que el alta vía interactors siga funcionando.
 */
final class HistoricoComprasInventarioActivosSeeder extends Seeder
{
    public const TABLAS_TOCADAS = [
        'solicitudes_compra',
        'solicitud_items',
        'cotizaciones',
        'cotizacion_items',
        'ordenes_compra',
        'orden_compra_items',
        'recepciones_compra',
        'recepcion_items',
        'compra_historial',
        'devoluciones_compra',
        'devolucion_items',
        'inv_lotes',
        'inv_stock',
        'inv_movimientos',
        'inv_registro_individualizacion',
        'inv_activos',
        'inv_activo_asignaciones',
        'inv_mantenimientos',
        'inv_activo_bajas',
    ];

    private CarbonInterface $marca;

    private int $almacenId = 62;

    public function run(): void
    {
        $this->marca = now();

        $this->cargarReferenciaAlmacen();

        $this->limpiarHistorico();

        (new HistoricoComprasMasivoSeeder)->run();
        (new HistoricoInventarioMasivoSeeder)->run();
        (new HistoricoActivosMasivoSeeder)->run();

        $this->sincronizarSecuencias();

        $this->info('✓ Histórico masivo concordante completado.');
    }

    private function cargarReferenciaAlmacen(): void
    {
        $almacen = DB::table('ubicaciones')->where('tipo', 'almacen')->value('id');
        if (is_numeric($almacen)) {
            $this->almacenId = (int) $almacen;
        }
    }

    private function limpiarHistorico(): void
    {
        // 1. Trazabilidad de estados (hijo de todo).
        DB::table('compra_historial')->where('comentario', 'like', '%[HST]%')->delete();

        // 2. Devoluciones (hijo de OC/recepción/lotes).
        DB::table('devolucion_items')
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('devoluciones_compra as d')
                    ->whereColumn('d.id', 'devolucion_items.devolucion_id')
                    ->where('d.codigo', 'like', 'DEV-HST-%');
            })
            ->delete();
        DB::table('devoluciones_compra')->where('codigo', 'like', 'DEV-HST-%')->delete();

        // 3. Hijos directos de activos HST.
        foreach (['inv_activo_bajas', 'inv_mantenimientos', 'inv_activo_asignaciones'] as $tabla) {
            DB::table($tabla)
                ->whereExists(function ($q) use ($tabla): void {
                    $q->select(DB::raw(1))
                        ->from('inv_activos as a')
                        ->whereColumn('a.id', "{$tabla}.activo_id")
                        ->where('a.codigo_inventario', 'like', 'HST-%');
                })
                ->delete();
        }

        // 4. Movimientos de inventario (por lote HST o por individualización de activo HST).
        $activosHst = DB::table('inv_activos')->where('codigo_inventario', 'like', 'HST-%')->pluck('id')->all();
        DB::table('inv_movimientos')
            ->where(function ($q) use ($activosHst): void {
                $q->whereExists(function ($q2): void {
                    $q2->select(DB::raw(1))
                        ->from('inv_lotes as l')
                        ->whereColumn('l.id', 'inv_movimientos.lote_id')
                        ->where('l.codigo_lote', 'like', 'HST-LOTE-%');
                });
                if ($activosHst !== []) {
                    $q->orWhere(fn ($q3) => $q3->where('documento_tipo', 'inv_activos')->whereIn('documento_id', $activosHst));
                }
            })
            ->delete();

        // 5. Stock: por lote HST o por agregación de activos sin lote creada por este seeder.
        DB::table('inv_stock')
            ->where(function ($q): void {
                $q->whereExists(function ($q2): void {
                    $q2->select(DB::raw(1))
                        ->from('inv_lotes as l')
                        ->whereColumn('l.id', 'inv_stock.lote_id')
                        ->where('l.codigo_lote', 'like', 'HST-LOTE-%');
                })->orWhere(function ($q3): void {
                    $q3->whereNull('lote_id')
                        ->where('ubicacion_id', $this->almacenId)
                        ->where('created_at', '>=', $this->marca->toDateTimeString())
                        ->whereExists(function ($q4): void {
                            $q4->select(DB::raw(1))
                                ->from('inv_activos as a')
                                ->where('a.codigo_inventario', 'like', 'HST-%')
                                ->whereColumn('a.producto_id', 'inv_stock.producto_id')
                                ->whereRaw('COALESCE(a.producto_variante_id, 0) = COALESCE(inv_stock.producto_variante_id, 0)');
                        });
                });
            })
            ->delete();

        // 6. Activos HST (sus hijos ya fueron removidos).
        DB::table('inv_activos')->where('codigo_inventario', 'like', 'HST-%')->delete();

        // 7. Registros de individualización HST.
        DB::table('inv_registro_individualizacion')
            ->where(function ($q): void {
                $q->where('notes', 'like', '%[HST]%')->orWhereExists(function ($q2): void {
                    $q2->select(DB::raw(1))
                        ->from('recepcion_items as ri')
                        ->join('recepciones_compra as rc', 'rc.id', '=', 'ri.recepcion_id')
                        ->whereColumn('ri.id', 'inv_registro_individualizacion.recepcion_item_id')
                        ->where('rc.codigo', 'like', 'RC-HST-%');
                });
            })
            ->delete();

        // 8. Lotes HST.
        DB::table('inv_lotes')->where('codigo_lote', 'like', 'HST-LOTE-%')->delete();

        // 9. Recepciones y su detalle.
        DB::table('recepcion_items')
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('recepciones_compra as rc')
                    ->whereColumn('rc.id', 'recepcion_items.recepcion_id')
                    ->where('rc.codigo', 'like', 'RC-HST-%');
            })
            ->delete();
        DB::table('recepciones_compra')->where('codigo', 'like', 'RC-HST-%')->delete();

        // 10. Órdenes de compra y su detalle.
        DB::table('orden_compra_items')
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('ordenes_compra as oc')
                    ->whereColumn('oc.id', 'orden_compra_items.orden_compra_id')
                    ->where('oc.codigo', 'like', 'OC-HST-%');
            })
            ->delete();
        DB::table('ordenes_compra')->where('codigo', 'like', 'OC-HST-%')->delete();

        // 11. Cotizaciones y su detalle (vía solicitudes HST).
        DB::table('cotizacion_items')
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('cotizaciones as c')
                    ->join('solicitudes_compra as s', 's.id', '=', 'c.solicitud_id')
                    ->whereColumn('c.id', 'cotizacion_items.cotizacion_id')
                    ->where('s.codigo', 'like', 'SOL-HST-%');
            })
            ->delete();
        DB::table('cotizaciones')
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('solicitudes_compra as s')
                    ->whereColumn('s.id', 'cotizaciones.solicitud_id')
                    ->where('s.codigo', 'like', 'SOL-HST-%');
            })
            ->delete();

        // 12. Solicitudes y su detalle.
        DB::table('solicitud_items')
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('solicitudes_compra as s')
                    ->whereColumn('s.id', 'solicitud_items.solicitud_id')
                    ->where('s.codigo', 'like', 'SOL-HST-%');
            })
            ->delete();
        DB::table('solicitudes_compra')->where('codigo', 'like', 'SOL-HST-%')->delete();
    }

    private function sincronizarSecuencias(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLAS_TOCADAS as $tabla) {
            DB::select(
                "SELECT setval(pg_get_serial_sequence(?, 'id'), "
                ."(SELECT COALESCE(MAX(id), 1) FROM \"{$tabla}\"), "
                ."(SELECT EXISTS (SELECT 1 FROM \"{$tabla}\")))",
                [$tabla]
            );
        }
    }

    private function info(string $mensaje): void
    {
        if ($this->command !== null) {
            $this->command->info($mensaje);
        } else {
            echo $mensaje.PHP_EOL;
        }
    }
}
