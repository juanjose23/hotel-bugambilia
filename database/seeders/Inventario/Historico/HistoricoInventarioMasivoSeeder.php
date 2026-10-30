<?php

declare(strict_types=1);

namespace Database\Seeders\Inventario\Historico;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generador masivo del histórico concordante del módulo Inventario.
 *
 * Por cada ítem de recepción HST de productos consumibles (tipo 1 y 2) se
 * crea UN lote con la "foto histórica" de su ciclo de vida:
 *   1. MOV_ENTRADA  → entrada física (fecha de recepción)
 *   2. CONSUMO      → 1..3 consumos posteriores (concordancia: por eso se
 *                     vuelve a comprar el producto en meses siguientes)
 *   3. BAJA_CADUCIDAD→ merma automática de perecederos vencidos
 *   4. DEVOLUCION_PROVEEDOR → devolución al proveedor (≈4% de recepciones)
 * Se mantiene consistente: inv_lotes.cantidad_disponible = Σ consumos - Σ bajas,
 * e inv_stock solo existe mientras la cantidad > 0 (regla de dominio).
 */
final class HistoricoInventarioMasivoSeeder extends Seeder
{
    private const CHUNK_SIZE = 2000;

    /** @var array<int, int> */
    private array $usuarioIds = [];

    /** @var array<int, int> */
    private array $estanteIds = [];

    private int $adminId = 1;

    private int $almacenId = 62;

    private int $nextLoteId = 0;

    private int $nextStockId = 0;

    private int $nextMovimientoId = 0;

    private int $nextDevolucionId = 0;

    private int $nextDevolucionItemId = 0;

    private int $seqLote = 0;

    private int $seqDevolucion = 0;

    /** @var array<int, array<string, mixed>> */
    private array $loteBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $stockBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $movBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $devBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $devItemBatch = [];

    public function run(): void
    {
        mt_srand(2026100102);

        $this->cargarMetadatos();
        $this->inicializarContadores();
        $this->generarDesdeRecepciones();
        $this->generarDevoluciones();
        $this->flush();

        $this->info("✓ Inventario masivo: {$this->seqLote} lotes generados.");
    }

    private function cargarMetadatos(): void
    {
        $this->usuarioIds = $this->aEnteros(
            DB::table('users')
                ->where(fn ($q) => $q->where('persona_id', '<=', 18)->orWhere('email', 'like', '%@hotel.com'))
                ->pluck('id')
                ->all()
        );

        $admin = DB::table('users')->where('email', 'admin@hotel.com')->first();
        if ($admin !== null) {
            $this->adminId = (int) $admin->id;
        }

        $almacen = DB::table('ubicaciones')->where('tipo', 'almacen')->first();
        if ($almacen !== null) {
            $this->almacenId = (int) $almacen->id;
        }

        $this->estanteIds = $this->aEnteros(
            DB::table('ubicaciones')->where('tipo', 'estante')->pluck('id')->all()
        );
    }

    private function inicializarContadores(): void
    {
        $this->nextLoteId = $this->maxId('inv_lotes');
        $this->nextStockId = $this->maxId('inv_stock');
        $this->nextMovimientoId = $this->maxId('inv_movimientos');
        $this->nextDevolucionId = $this->maxId('devoluciones_compra');
        $this->nextDevolucionItemId = $this->maxId('devolucion_items');
    }

    private function maxId(string $tabla): int
    {
        $valor = DB::table($tabla)->max('id');

        return is_numeric($valor) ? (int) $valor : 0;
    }

    private function enteroDe(object $fila, string $clave): int
    {
        return is_numeric($fila->{$clave}) ? (int) $fila->{$clave} : 0;
    }

    private function enteroNuloDe(object $fila, string $clave): ?int
    {
        return is_numeric($fila->{$clave}) ? (int) $fila->{$clave} : null;
    }

    private function decimalDe(object $fila, string $clave): float
    {
        return is_numeric($fila->{$clave}) ? (float) $fila->{$clave} : 0.0;
    }

    private function fechaDe(object $fila, string $clave): CarbonInterface
    {
        $valor = $fila->{$clave};

        return Carbon::parse(is_scalar($valor) ? (string) $valor : '');
    }

    private function fechaNulaDe(object $fila, string $clave): ?CarbonInterface
    {
        $valor = $fila->{$clave};
        if ($valor === null || $valor === '') {
            return null;
        }

        return Carbon::parse(is_scalar($valor) ? (string) $valor : '');
    }

    private function textoNuloDe(object $fila, string $clave): ?string
    {
        $valor = $fila->{$clave};

        return is_scalar($valor) ? (string) $valor : null;
    }

    private function generarDesdeRecepciones(): void
    {
        $ahora = now();

        DB::table('recepciones_compra as rc')
            ->join('recepcion_items as ri', 'ri.recepcion_id', '=', 'rc.id')
            ->join('orden_compra_items as oi', 'oi.id', '=', 'ri.orden_item_id')
            ->join('ordenes_compra as oc', 'oc.id', '=', 'rc.orden_compra_id')
            ->join('productos as p', 'p.id', '=', 'ri.producto_id')
            ->where('rc.codigo', 'like', 'RC-HST-%')
            ->whereIn('p.tipo', [1, 2])
            ->select([
                'ri.id',
                'ri.recepcion_id',
                'ri.producto_id',
                'ri.producto_variante_id',
                'ri.cantidad_recibida',
                'ri.lote_proveedor',
                'ri.fecha_vencimiento',
                'ri.unidad_medida_id',
                'rc.fecha_recepcion',
                'rc.orden_compra_id',
                'oi.precio_unitario',
                'oc.proveedor_id',
            ])
            ->orderBy('ri.id')
            ->chunk(1000, function ($filas) use ($ahora): void {
                foreach ($filas as $fila) {
                    $this->procesarRecepcionItem($fila, $ahora);
                }

                $this->flush();
            });
    }

    private function procesarRecepcionItem(object $fila, CarbonInterface $ahora): void
    {
        $loteId = ++$this->nextLoteId;
        $seq = ++$this->seqLote;
        $codigoLote = 'HST-LOTE-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);

        $productoId = $this->enteroDe($fila, 'producto_id');
        $varianteId = $this->enteroNuloDe($fila, 'producto_variante_id');
        $recepcionItemId = $this->enteroDe($fila, 'id');
        $cantidadInicial = $this->decimalDe($fila, 'cantidad_recibida');
        $costoUnitario = $this->decimalDe($fila, 'precio_unitario');
        $costoTotal = round($cantidadInicial * $costoUnitario, 2);
        $proveedorId = $this->enteroDe($fila, 'proveedor_id');
        $fechaRecepcion = $this->fechaDe($fila, 'fecha_recepcion');
        $fechaVencimiento = $this->fechaNulaDe($fila, 'fecha_vencimiento');
        $loteProveedor = $this->textoNuloDe($fila, 'lote_proveedor');

        $this->movBatch[] = $this->movimiento(
            tipo: 'MOV_ENTRADA',
            loteId: $loteId,
            productoId: $productoId,
            cantidad: $cantidadInicial,
            costoUnitario: $costoUnitario,
            costoTotal: $costoTotal,
            origenId: null,
            destinoId: $this->almacenId,
            documentoTipo: 'recepcion_item',
            documentoId: $recepcionItemId,
            referencia: "Lote {$codigoLote} — Disponible",
            fecha: $fechaRecepcion->format('Y-m-d H:i:s'),
        );

        $restante = $cantidadInicial;
        $fechaHoy = $ahora->copy()->startOfDay();
        $fechaLimiteConsumo = $fechaHoy->copy();
        if ($fechaVencimiento !== null && $fechaVencimiento->lessThan($fechaHoy)) {
            $fechaLimiteConsumo = $fechaVencimiento;
        }

        $edadMeses = (int) max(0, round($fechaRecepcion->startOfDay()->diffInMonths($fechaHoy)));

        $fraccionConsumo = match (true) {
            $edadMeses >= 4 => mt_rand(65, 100) / 100,
            $edadMeses >= 2 => mt_rand(35, 85) / 100,
            $edadMeses >= 1 => mt_rand(10, 40) / 100,
            default => mt_rand(0, 25) / 100,
        };

        $objetivoConsumo = round($cantidadInicial * $fraccionConsumo, 2);
        $nConsumos = ($objetivoConsumo <= 0 || $restante <= 0) ? 0 : mt_rand(1, 3);

        for ($k = 0; $k < $nConsumos; $k++) {
            if ($restante <= 0) {
                break;
            }

            $minDia = $fechaRecepcion->copy()->addDay()->startOfDay();
            if ($k === $nConsumos - 1) {
                $consumo = $objetivoConsumo;
            } else {
                $consumo = round($objetivoConsumo * (mt_rand(25, 70) / 100), 2);
            }
            $consumo = min($consumo, $restante);
            $consumo = max(0.0, $consumo);

            if ($consumo <= 0) {
                $objetivoConsumo = 0.0;

                continue;
            }

            $fechaMov = $this->fechaEntre($minDia, $fechaLimiteConsumo);
            if ($fechaMov->greaterThan($ahora)) {
                $fechaMov = $ahora->copy();
            }

            $this->movBatch[] = $this->movimiento(
                tipo: 'CONSUMO',
                loteId: $loteId,
                productoId: $productoId,
                cantidad: -$consumo,
                costoUnitario: $costoUnitario,
                costoTotal: round($consumo * $costoUnitario, 2),
                origenId: $this->almacenId,
                destinoId: null,
                documentoTipo: null,
                documentoId: null,
                referencia: "Consumo operativo del lote {$codigoLote}",
                fecha: $fechaMov->format('Y-m-d H:i:s'),
            );

            $restante = round($restante - $consumo, 2);
            $objetivoConsumo = round($objetivoConsumo - $consumo, 2);
        }

        if ($restante <= 0) {
            $estadoLote = 0; // Agotado
        } elseif ($fechaVencimiento !== null && $fechaVencimiento->lessThan($fechaHoy) && $fechaVencimiento->greaterThan($fechaRecepcion)) {
            // Merma automática por caducidad
            $this->movBatch[] = $this->movimiento(
                tipo: 'BAJA_CADUCIDAD',
                loteId: $loteId,
                productoId: $productoId,
                cantidad: -$restante,
                costoUnitario: $costoUnitario,
                costoTotal: round($restante * $costoUnitario, 2),
                origenId: $this->almacenId,
                destinoId: null,
                documentoTipo: 'inv_lotes',
                documentoId: $loteId,
                referencia: "Merma por vencimiento del lote {$codigoLote}",
                fecha: $fechaVencimiento->format('Y-m-d 12:00:00'),
            );

            $restante = 0.0;
            $estadoLote = 3; // Vencido
        } else {
            $estadoLote = 1; // Disponible
        }

        $loteRow = [
            'id' => $loteId,
            'codigo_lote' => $codigoLote,
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'estado' => $estadoLote,
            'cantidad_disponible' => $restante,
            'cantidad_inicial' => $cantidadInicial,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'ubicacion_id' => $this->almacenId,
            'ubicacion_detalle_id' => $this->random($this->estanteIds),
            'fecha_vencimiento' => $fechaVencimiento?->format('Y-m-d'),
            'lote_proveedor' => $loteProveedor,
            'proveedor_id' => $proveedorId,
            'fecha_recepcion' => $fechaRecepcion->format('Y-m-d'),
            'recepcion_item_id' => $recepcionItemId,
            'created_at' => $fechaRecepcion->format('Y-m-d H:i:s'),
            'updated_at' => $ahora->format('Y-m-d H:i:s'),
        ];
        $this->loteBatch[] = $loteRow;

        if ($restante > 0) {
            $this->stockBatch[] = [
                'id' => ++$this->nextStockId,
                'producto_id' => $productoId,
                'producto_variante_id' => $varianteId,
                'lote_id' => $loteId,
                'ubicacion_id' => $this->almacenId,
                'ubicacion_detalle_id' => $loteRow['ubicacion_detalle_id'],
                'cantidad' => $restante,
                'created_at' => $ahora->format('Y-m-d H:i:s'),
                'updated_at' => $ahora->format('Y-m-d H:i:s'),
            ];
        }
    }

    private function generarDevoluciones(): void
    {
        $ahora = now();
        $corte = $ahora->copy()->subMonths(3)->format('Y-m-d');

        $recepciones = DB::table('recepciones_compra as rc')
            ->join('recepcion_items as ri', 'ri.recepcion_id', '=', 'rc.id')
            ->where('rc.codigo', 'like', 'RC-HST-%')
            ->whereIn('rc.estado', [1, 2])
            ->where('rc.fecha_recepcion', '<=', $corte)
            ->select('rc.id', 'rc.orden_compra_id', 'rc.fecha_recepcion')
            ->distinct()
            ->get(['id', 'orden_compra_id', 'fecha_recepcion']);

        foreach ($recepciones as $recepcion) {
            if (mt_rand(1, 100) > 4) {
                continue;
            }

            $lotes = DB::table('inv_lotes')
                ->whereIn('recepcion_item_id', function ($q) use ($recepcion): void {
                    $q->select('id')->from('recepcion_items')->where('recepcion_id', $recepcion->id);
                })
                ->where('estado', 1)
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('id')
                ->limit(3)
                ->get();

            if ($lotes->isEmpty()) {
                continue;
            }

            $devolucionId = ++$this->nextDevolucionId;
            $seq = ++$this->seqDevolucion;

            $fechaBase = Carbon::parse((string) $recepcion->fecha_recepcion);
            $fechaDevolucion = $fechaBase->copy()->addDays(mt_rand(2, 7))->format('Y-m-d');
            if ($fechaDevolucion > $ahora->format('Y-m-d')) {
                $fechaDevolucion = $ahora->format('Y-m-d');
            }

            $motivo = $this->motivoDevolucion();

            $this->devBatch[] = [
                'id' => $devolucionId,
                'codigo' => 'DEV-HST-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
                'orden_compra_id' => (int) $recepcion->orden_compra_id,
                'recepcion_compra_id' => (int) $recepcion->id,
                'fecha_devolucion' => $fechaDevolucion,
                'estado' => mt_rand(1, 100) <= 90 ? 1 : 0,
                'motivo' => $motivo,
                'documento_externo' => 'GUIA-RET-HST-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT),
                'creado_por_id' => $this->random($this->usuarioIds) ?? $this->adminId,
                'created_at' => $fechaDevolucion.' '.sprintf('%02d:%02d:00', mt_rand(8, 16), mt_rand(0, 59)),
                'updated_at' => $fechaDevolucion.' '.sprintf('%02d:%02d:00', mt_rand(8, 16), mt_rand(0, 59)),
            ];

            foreach ($lotes as $lote) {
                $cantidadDevolver = min(
                    (float) $lote->cantidad_disponible,
                    round((float) $lote->cantidad_inicial * (mt_rand(10, 40) / 100), 2)
                );
                if ($cantidadDevolver <= 0) {
                    continue;
                }

                $nuevaCantidad = round((float) $lote->cantidad_disponible - $cantidadDevolver, 2);

                $this->devItemBatch[] = [
                    'id' => ++$this->nextDevolucionItemId,
                    'devolucion_id' => $devolucionId,
                    'lote_id' => (int) $lote->id,
                    'recepcion_item_id' => $lote->recepcion_item_id,
                    'producto_id' => (int) $lote->producto_id,
                    'producto_variante_id' => $lote->producto_variante_id,
                    'unidad_medida_id' => null,
                    'cantidad_devolver' => $cantidadDevolver,
                    'created_at' => $fechaDevolucion.' 10:00:00',
                    'updated_at' => $fechaDevolucion.' 10:00:00',
                ];

                $this->movBatch[] = $this->movimiento(
                    tipo: 'DEVOLUCION_PROVEEDOR',
                    loteId: (int) $lote->id,
                    productoId: (int) $lote->producto_id,
                    cantidad: -$cantidadDevolver,
                    costoUnitario: (float) $lote->costo_unitario,
                    costoTotal: round($cantidadDevolver * (float) $lote->costo_unitario, 2),
                    origenId: $this->almacenId,
                    destinoId: null,
                    documentoTipo: 'devolucion',
                    documentoId: $devolucionId,
                    referencia: 'Devolución de mercancía al proveedor',
                    fecha: $fechaDevolucion.' '.sprintf('%02d:%02d:00', mt_rand(9, 14), mt_rand(0, 59)),
                );

                DB::table('inv_lotes')
                    ->where('id', $lote->id)
                    ->update([
                        'cantidad_disponible' => $nuevaCantidad,
                        'estado' => $nuevaCantidad <= 0 ? 0 : 1,
                    ]);

                if ($nuevaCantidad <= 0) {
                    DB::table('inv_stock')->where('lote_id', $lote->id)->delete();
                } else {
                    DB::table('inv_stock')
                        ->where('lote_id', $lote->id)
                        ->update(['cantidad' => $nuevaCantidad]);
                }
            }
        }
    }

    /**
     * @return array<string, scalar|null>
     */
    private function movimiento(
        string $tipo,
        int $loteId,
        int $productoId,
        float $cantidad,
        float $costoUnitario,
        float $costoTotal,
        ?int $origenId,
        ?int $destinoId,
        ?string $documentoTipo,
        ?int $documentoId,
        string $referencia,
        string $fecha
    ): array {
        return [
            'id' => ++$this->nextMovimientoId,
            'tipo' => $tipo,
            'lote_id' => $loteId,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'ubicacion_origen_id' => $origenId,
            'ubicacion_destino_id' => $destinoId,
            'documento_tipo' => $documentoTipo,
            'documento_id' => $documentoId,
            'referencia' => $referencia,
            'creado_por_id' => $this->random($this->usuarioIds) ?? $this->adminId,
            'notas' => null,
            'created_at' => $fecha,
        ];
    }

    private function fechaEntre(CarbonInterface $min, CarbonInterface $max): CarbonInterface
    {
        $minTs = $min->copy()->addMinutes(mt_rand(360, 1800));
        $maxTs = $max->copy()->addMinutes(mt_rand(360, 1800));

        $rango = (int) max(0, $minTs->diffInSeconds($maxTs));
        $offset = mt_rand(0, $rango);

        return $minTs->copy()->addSeconds($offset);
    }

    private function motivoDevolucion(): string
    {
        $motivos = [
            'Calidad deficiente detectada en inspección de recibido.',
            'Daños en tránsito constatados al abrir el embalaje.',
            'Excedente no aceptado en conteo posterior de almacén.',
            'Producto equivocado respecto a la orden de compra.',
            'Fecha de vencimiento demasiado próxima para su rotación.',
        ];

        return $motivos[mt_rand(0, count($motivos) - 1)];
    }

    private function flush(): void
    {
        if ($this->loteBatch !== []) {
            foreach (array_chunk($this->loteBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_lotes')->insert($chunk);
            }
            $this->loteBatch = [];
        }
        if ($this->stockBatch !== []) {
            foreach (array_chunk($this->stockBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_stock')->insert($chunk);
            }
            $this->stockBatch = [];
        }
        if ($this->movBatch !== []) {
            foreach (array_chunk($this->movBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_movimientos')->insert($chunk);
            }
            $this->movBatch = [];
        }
        if ($this->devBatch !== []) {
            foreach (array_chunk($this->devBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('devoluciones_compra')->insert($chunk);
            }
            $this->devBatch = [];
        }
        if ($this->devItemBatch !== []) {
            foreach (array_chunk($this->devItemBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('devolucion_items')->insert($chunk);
            }
            $this->devItemBatch = [];
        }
    }

    /**
     * @param  array<int, int>  $items
     */
    private function random(array $items): ?int
    {
        if ($items === []) {
            return null;
        }

        return $items[mt_rand(0, count($items) - 1)];
    }

    /**
     * @param  array<array-key, mixed>  $items
     * @return array<int, int>
     */
    private function aEnteros(array $items): array
    {
        $resultado = [];
        foreach ($items as $item) {
            if (is_numeric($item)) {
                $resultado[] = (int) $item;
            }
        }

        return $resultado;
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
