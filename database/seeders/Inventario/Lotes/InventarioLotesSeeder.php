<?php

declare(strict_types=1);

namespace Database\Seeders\Inventario\Lotes;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Garantiza que TODO producto consumible (tipo 1 y 2) tenga inventario
 * inicial en el almacén cuando aún no posee ningún lote registrado.
 *
 * Respeta los lotes ya creados por otros módulos (StockInicialPackSeeder,
 * recepciones de compra, menú, etc.) y conserva los lotes de demostración
 * Disponible / Cuarentena / Vencido para los escenarios de inventario.
 */
class InventarioLotesSeeder extends Seeder
{
    public function run(): void
    {
        $this->ejecutar();
    }

    public function ejecutar(): void
    {
        $almacen = Ubicacion::where('tipo', 'almacen')->first() ?? Ubicacion::where('estado', 1)->first();
        if (! $almacen) {
            return;
        }

        $proveedor = Proveedor::first();
        $admin = User::first();
        $fechaLote = now()->subDays(5)->format('Y-m-d');

        /** @var Collection<int, Producto> $productos */
        $productos = Producto::with('variantes')
            ->where('tipo', '!=', 3)
            ->orderBy('id')
            ->get();

        if ($productos->isEmpty()) {
            return;
        }

        // ─── FASE 1: Disponible para todo producto/variante sin inventario ───
        $cubiertos = [];

        foreach ($productos as $producto) {
            /** @var list<int|null> $varianteIds */
            $varianteIds = $producto->variantes->isNotEmpty()
                ? $producto->variantes->map(fn ($variante) => (int) $variante->id)->all()
                : [null];

            foreach ($varianteIds as $varId) {
                $tieneLote = $this->existeLote($producto->id, $varId);

                if ($tieneLote) {
                    continue;
                }

                $codigoLote = 'LOTE-INICIAL-'.$producto->id.'-'.($varId ?? 'P').'-'.now()->format('Ymd');
                $costoUnitario = 10.00;
                $cantidad = 100.0;

                $lote = Lote::create([
                    'codigo_lote' => $codigoLote,
                    'producto_id' => $producto->id,
                    'producto_variante_id' => $varId,
                    'estado' => EstadoLote::Disponible,
                    'cantidad_disponible' => $cantidad,
                    'cantidad_inicial' => $cantidad,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoUnitario * $cantidad,
                    'ubicacion_id' => $almacen->id,
                    'fecha_vencimiento' => now()->addMonths(12)->format('Y-m-d'),
                    'fecha_recepcion' => $fechaLote,
                    'proveedor_id' => $proveedor?->id,
                ]);

                $this->crearStock($producto->id, $varId, $lote->id, $almacen->id, $cantidad, $costoUnitario, $admin?->id, $fechaLote);

                $cubiertos[] = ['producto_id' => $producto->id, 'variante_id' => $varId];
            }
        }

        // ─── FASE 2: Lotes de Cuarentena (3 productos recién cubiertos) ───
        foreach (array_slice($cubiertos, 0, 3) as $entry) {
            $this->crearLoteEspecial($entry['producto_id'], $entry['variante_id'], EstadoLote::Cuarentena, $almacen->id, $proveedor?->id, $admin?->id);
        }

        // ─── FASE 3: Lotes Vencidos (2 productos recién cubiertos) ───
        foreach (array_slice($cubiertos, 3, 2) as $entry) {
            $this->crearLoteEspecial($entry['producto_id'], $entry['variante_id'], EstadoLote::Vencido, $almacen->id, $proveedor?->id, $admin?->id);
        }
    }

    /**
     * @param  int|string|null  $varId
     */
    private function existeLote(int $productoId, $varId): bool
    {
        return $varId !== null
            ? Lote::where('producto_id', $productoId)->where('producto_variante_id', $varId)->exists()
            : Lote::where('producto_id', $productoId)->whereNull('producto_variante_id')->exists();
    }

    /**
     * @param  int|string|null  $varId
     */
    private function crearStock(
        int $productoId,
        $varId,
        int $loteId,
        int $ubicacionId,
        float $cantidad,
        float $costoUnitario,
        ?int $creadoPorId,
        string $fechaLote,
    ): void {
        DB::table('inv_stock')->insert([
            'producto_id' => $productoId,
            'producto_variante_id' => $varId,
            'lote_id' => $loteId,
            'ubicacion_id' => $ubicacionId,
            'cantidad' => $cantidad,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        DB::table('inv_movimientos')->insert([
            'tipo' => 'MOV_ENTRADA',
            'lote_id' => $loteId,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoUnitario * $cantidad,
            'ubicacion_origen_id' => null,
            'ubicacion_destino_id' => $ubicacionId,
            'documento_tipo' => 'inventario_inicial',
            'referencia' => 'Stock inicial de sistema',
            'notas' => 'Garantiza inventario de todo producto del catálogo',
            'creado_por_id' => $creadoPorId,
            'created_at' => $fechaLote,
        ]);
    }

    /**
     * @param  int|string|null  $varId
     */
    private function crearLoteEspecial(
        int $productoId,
        $varId,
        EstadoLote $estado,
        int $ubicacionId,
        ?int $proveedorId,
        ?int $creadoPorId,
    ): void {
        $sufijo = $estado === EstadoLote::Cuarentena ? 'CUAR' : 'VENC';
        $codigoLote = 'LOTE-'.$sufijo.'-'.$productoId.'-'.($varId ?? 'P');

        if (Lote::where('codigo_lote', $codigoLote)->exists()) {
            return;
        }

        $cantidadInicial = 50.0;
        $cantidadDisponible = $estado === EstadoLote::Vencido ? 0.0 : 50.0;
        $costoUnitario = 8.00;

        $loteId = DB::table('inv_lotes')->insertGetId([
            'codigo_lote' => $codigoLote,
            'producto_id' => $productoId,
            'producto_variante_id' => $varId,
            'estado' => $estado->value,
            'cantidad_disponible' => $cantidadDisponible,
            'cantidad_inicial' => $cantidadInicial,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoUnitario * $cantidadInicial,
            'ubicacion_id' => $ubicacionId,
            'fecha_vencimiento' => $estado === EstadoLote::Vencido
                ? now()->subDays(10)->format('Y-m-d')
                : now()->addMonths(12)->format('Y-m-d'),
            'fecha_recepcion' => $estado === EstadoLote::Vencido
                ? now()->subMonths(3)->format('Y-m-d')
                : now()->subDays(2)->format('Y-m-d'),
            'proveedor_id' => $proveedorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($estado === EstadoLote::Cuarentena) {
            DB::table('inv_stock')->insert([
                'producto_id' => $productoId,
                'producto_variante_id' => $varId,
                'lote_id' => $loteId,
                'ubicacion_id' => $ubicacionId,
                'cantidad' => $cantidadInicial,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);
        }

        DB::table('inv_movimientos')->insert([
            'tipo' => 'MOV_ENTRADA',
            'lote_id' => $loteId,
            'producto_id' => $productoId,
            'cantidad' => $cantidadInicial,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoUnitario * $cantidadInicial,
            'ubicacion_origen_id' => null,
            'ubicacion_destino_id' => $ubicacionId,
            'documento_tipo' => 'inventario_inicial',
            'referencia' => $estado === EstadoLote::Cuarentena ? 'Entrada en Cuarentena' : 'Entrada Histórica',
            'notas' => $estado === EstadoLote::Cuarentena ? 'Lote requiere inspección' : null,
            'creado_por_id' => $creadoPorId,
            'created_at' => $estado === EstadoLote::Vencido ? now()->subMonths(3) : now()->subDays(2),
        ]);

        if ($estado === EstadoLote::Vencido) {
            DB::table('inv_movimientos')->insert([
                'tipo' => 'BAJA_CADUCIDAD',
                'lote_id' => $loteId,
                'producto_id' => $productoId,
                'cantidad' => -$cantidadInicial,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * $cantidadInicial,
                'ubicacion_origen_id' => $ubicacionId,
                'ubicacion_destino_id' => null,
                'documento_tipo' => 'inventario_inicial',
                'referencia' => 'Consumo Completo / Vencido',
                'creado_por_id' => $creadoPorId,
                'created_at' => now()->subMonths(1),
            ]);
        }
    }
}
