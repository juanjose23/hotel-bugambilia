<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Historico;

use App\Enums\Inventario\EstadoLote;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\Plato;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Histórico masivo de la operación de cocina y de la merma de materia prima
 * (stock por vencer y vencido) para los últimos 365 días.
 *
 * Sigue el flujo real de la aplicación:
 *   - Lotes en Cocina Restaurante con vencimiento (FEFO).
 *   - Pedidos → CONSUMO de ingredientes por pedido_item.
 *   - Transformación de materia prima → TRANSFORMACION_SALIDA / _ENTRADA y MERMA_COCINA.
 *   - Procesos de cocina → CONSUMO con documento proceso_cocina.
 *   - Merma por proximidad de vencimiento → BAJA_CADUCIDAD.
 *   - Stock vencido → MOV_AJUSTE hacia la Zona de Merma (VerificarCaducidades).
 *
 * Al final sincroniza el estado vivo de inv_lotes y stocks (SharedStock), y
 * reconstruye la foto real de inv_stock de cocina (filas solo mientras cantidad > 0).
 */
final class MermaCocinaHistoricoMasivoSeeder extends Seeder
{
    private const DIAS = 365;

    private const PEDIDOS_MIN = 280;

    private const PEDIDOS_MAX = 480;

    private const FLUSH_STOCK = 5000;

    private const MAX_SEQ = 99999999;

    private const MAX_ACTIVE_LOTES = 40;

    /** @var list<array{id: int, nombre: string, area: string, precio: float, receta: list<array{variante_id: int, cantidad: float}>}> */
    private array $platos = [];

    /** @var array<string, list<array{id: int, nombre: string, area: string, precio: float, receta: list<array{variante_id: int, cantidad: float}>}>> */
    private array $platosPorArea = [];

    /** @var array<int, array{id: int, producto_id: int, nombre: string, tipo: int, peso: float|null}> */
    private array $variantes = [];

    /** @var array<int, float> */
    private array $costos = [];

    /** @var array<int, int> */
    private array $usuarioIds = [];

    /** @var array<int, int> */
    private array $mesaIds = [];

    /** @var array<int, array{cantidad: float, lotes: list<array{id: int, qty: float, venc: string|null, costo: float, codigo: string}>}> */
    private array $stockCocina = [];

    /** @var array<int, array<string, mixed>> */
    private array $loteBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $movBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $pedBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $pedItemBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $tmpBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $tmpItemBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $procBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $procItemBatch = [];

    private int $cocinaId = 0;

    private ?int $mermaId = null;

    private int $adminId = 1;

    private ?int $meseroId = null;

    private float $escala = 1.0;

    private int $seqLote = 0;

    private int $seqPed = 0;

    private int $seqPedItem = 0;

    private int $seqTrans = 0;

    private int $seqTransItem = 0;

    private int $seqProc = 0;

    private int $seqProcItem = 0;

    private CarbonInterface $inicio;

    public function run(): void
    {
        set_time_limit(0);
        mt_srand(2026092301);

        $escalaConfig = config('restaurante.historico.merma_cocina_escala', 1.0);
        $this->escala = max(0.5, is_numeric($escalaConfig) ? (float) $escalaConfig : 1.0);
        $this->inicio = now()->startOfDay()->subDays(self::DIAS - 1);

        $this->cargarMetadatos();

        if ($this->cocinaId <= 0 || empty($this->variantes)) {
            $this->info('Cocina restaurada en BD aún no operativa. Ejecute RestauranteSeeder/MenuRestauranteSeeder antes.');

            return;
        }

        $this->limpiarAnterior();

        $this->sincronizarSecuencias();

        $this->inicializarStock();

        foreach (range(0, self::DIAS - 1) as $dia) {
            $fecha = $this->inicio->copy()->addDays($dia);

            $this->procesarVencidos($fecha);
            $this->reabastecerDia($fecha);
            $this->generarPedidosDia($fecha);
            $this->generarTransformacionesDia($fecha);
            $this->generarProcesosDia($fecha);
            $this->mermasPorVencer($fecha);

            $this->flushDocumentos();

            if ($this->pendStock() >= self::FLUSH_STOCK || $dia % 7 === 6) {
                $this->flushStock();
            }

            if ($dia % 20 === 19) {
                $lotesActivos = 0;
                $lotesMax = 0;
                foreach ($this->stockCocina as $s) {
                    $n = count($s['lotes']);
                    $lotesActivos += $n;
                    $lotesMax = max($lotesMax, $n);
                }
                $this->info(sprintf(
                    '  - día %d mem=%dMB lotesBatch=%d movBatch=%d lotesActivos=%d lotesMax=%d',
                    $dia,
                    (int) (memory_get_usage(true) / 1048576),
                    count($this->loteBatch),
                    count($this->movBatch),
                    $lotesActivos,
                    $lotesMax
                ));
            }
        }

        $this->flushDocumentos();
        $this->flushStock();

        $this->sincronizarLotes();
        $this->sincronizarStocksCocina();
        $this->sincronizarSecuencias();
        $this->reportar();
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

        $cocina = DB::table('ubicaciones')->where('nombre', 'Cocina Restaurante')->first();
        if ($cocina === null) {
            $cocina = DB::table('ubicaciones')->where('nombre', 'Cocina')->first();
        }
        if ($cocina !== null) {
            $this->cocinaId = (int) $cocina->id;
        }

        $merma = DB::table('ubicaciones')->where('tipo', 'merma')->first();
        if ($merma !== null) {
            $this->mermaId = (int) $merma->id;
        }

        $this->mesaIds = $this->aEnteros(
            DB::table('espacios')->where('codigo', 'like', 'MESA-%')->pluck('id')->all()
        );

        $rawMesero = DB::table('colaboradores')->value('id');
        $this->meseroId = is_numeric($rawMesero) ? (int) $rawMesero : null;

        $this->cargarVariantes();
        $this->cargarCostos();
        $this->cargarPlatos();
    }

    private function cargarVariantes(): void
    {
        $variantes = DB::table('producto_variantes as pv')
            ->join('productos as p', 'p.id', '=', 'pv.producto_id')
            ->whereIn('pv.id', function ($q): void {
                $q->select('producto_variante_id')
                    ->from('stocks')
                    ->where('stockable_type', Ubicacion::class)
                    ->where('stockable_id', $this->cocinaId)
                    ->whereNotNull('producto_variante_id')
                    ->whereNull('deleted_at');
            })
            ->select(['pv.id', 'pv.producto_id', 'pv.nombre_variante', 'pv.peso', 'p.nombre as producto_nombre', 'p.tipo'])
            ->get();

        foreach ($variantes as $v) {
            $type = is_numeric($v->tipo) ? (int) $v->tipo : 1;
            $this->variantes[(int) $v->id] = [
                'id' => (int) $v->id,
                'producto_id' => (int) $v->producto_id,
                'nombre' => trim((string) $v->producto_nombre).' - '.trim((string) $v->nombre_variante),
                'tipo' => $type,
                'peso' => is_numeric($v->peso) ? (float) $v->peso : null,
            ];
        }
    }

    private function cargarCostos(): void
    {
        $costosVariante = DB::table('inv_lotes')
            ->whereNotNull('producto_variante_id')
            ->selectRaw('producto_variante_id, AVG(costo_unitario) as c')
            ->groupBy('producto_variante_id')
            ->pluck('c', 'producto_variante_id');

        $costosProducto = DB::table('inv_lotes')
            ->whereNull('producto_variante_id')
            ->selectRaw('producto_id, AVG(costo_unitario) as c')
            ->groupBy('producto_id')
            ->pluck('c', 'producto_id');

        foreach ($this->variantes as $varianteId => $variante) {
            $costo = null;
            if (isset($costosVariante[$varianteId]) && is_numeric($costosVariante[$varianteId])) {
                $costo = (float) $costosVariante[$varianteId];
            } elseif (isset($costosProducto[$variante['producto_id']]) && is_numeric($costosProducto[$variante['producto_id']])) {
                $costo = (float) $costosProducto[$variante['producto_id']];
            }

            $costo ??= match ($variante['tipo']) {
                1 => 45.0,
                2 => 60.0,
                default => 30.0,
            };

            $this->costos[$varianteId] = round(max(0.5, (float) $costo), 4);
        }
    }

    private function cargarPlatos(): void
    {
        $platos = Plato::query()
            ->where('estado', 1)
            ->with('precios')
            ->get();

        foreach ($platos as $p) {
            $precio = (float) $p->precios->first()?->precio;

            $receta = [];
            $kit = DB::table('producto_kit')->where('producto_padre_id', (int) $p->producto_receta_id)->get();
            foreach ($kit as $k) {
                $varianteId = (int) $k->producto_variante_id;
                if (! isset($this->variantes[$varianteId])) {
                    continue;
                }
                $receta[] = ['variante_id' => $varianteId, 'cantidad' => max(0.01, (float) $k->cantidad)];
            }

            $area = (string) $p->area_cocina?->value;
            if ($area === '') {
                $area = 'cocina';
            }

            $plato = [
                'id' => (int) $p->id,
                'nombre' => (string) $p->nombre,
                'area' => $area,
                'precio' => $precio,
                'receta' => $receta,
            ];

            $this->platos[] = $plato;
            $this->platosPorArea[$area][] = $plato;
        }
    }

    private function limpiarAnterior(): void
    {
        $pedidos = DB::table('pedidos')->where('codigo', 'like', 'KM-PDR-%')->pluck('id')->all();
        foreach (array_chunk($pedidos, 10000) as $chunk) {
            DB::table('pedido_items')->whereIn('pedido_id', $chunk)->delete();
        }
        foreach (array_chunk($pedidos, 10000) as $chunk) {
            DB::table('pedidos')->whereIn('id', $chunk)->delete();
        }

        $transformaciones = DB::table('restaurante_transformaciones_materia_prima')->where('codigo', 'like', 'KM-TRANS-%')->pluck('id')->all();
        foreach (array_chunk($transformaciones, 10000) as $chunk) {
            DB::table('restaurante_transformacion_materia_prima_items')->whereIn('transformacion_id', $chunk)->delete();
        }
        foreach (array_chunk($transformaciones, 10000) as $chunk) {
            DB::table('restaurante_transformaciones_materia_prima')->whereIn('id', $chunk)->delete();
        }

        $procesos = DB::table('procesos_cocina')->where('codigo', 'like', 'KM-PRC-%')->pluck('id')->all();
        foreach (array_chunk($procesos, 10000) as $chunk) {
            DB::table('proceso_items')->whereIn('proceso_id', $chunk)->delete();
        }
        foreach (array_chunk($procesos, 10000) as $chunk) {
            DB::table('procesos_cocina')->whereIn('id', $chunk)->delete();
        }

        $lotes = DB::table('inv_lotes')->where('codigo_lote', 'like', 'KM-%')->pluck('id')->all();
        foreach (array_chunk($lotes, 10000) as $chunk) {
            DB::table('inv_movimientos')->whereIn('lote_id', $chunk)->delete();
        }
        foreach (array_chunk($lotes, 10000) as $chunk) {
            DB::table('inv_stock')->whereIn('lote_id', $chunk)->delete();
        }
        foreach (array_chunk($lotes, 10000) as $chunk) {
            DB::table('stocks')->where('stockable_type', Ubicacion::class)
                ->where('stockable_id', $this->cocinaId)
                ->whereIn('lote_id', $chunk)
                ->delete();
        }
        foreach (array_chunk($lotes, 10000) as $chunk) {
            DB::table('inv_lotes')->whereIn('id', $chunk)->delete();
        }

        DB::table('inv_movimientos')
            ->whereNull('lote_id')
            ->where(fn ($q) => $q->where('documento_tipo', 'pedido_item')->where('documento_id', null)
                ->orWhere('referencia', 'like', 'KM-%')
                ->orWhere('referencia', 'like', 'Merma por vencimiento del lote KM-%')
                ->orWhere('referencia', 'like', 'Merma final de KM-%'))
            ->delete();

        $lotesIniciales = DB::table('inv_lotes')->where('codigo_lote', 'like', 'LOTE-INICIAL-%')->pluck('id')->all();
        foreach (array_chunk($lotesIniciales, 10000) as $chunk) {
            DB::table('inv_stock')->whereIn('lote_id', $chunk)->delete();
        }
        foreach (array_chunk($lotesIniciales, 10000) as $chunk) {
            DB::table('stocks')->where('stockable_type', Ubicacion::class)
                ->where('stockable_id', $this->cocinaId)
                ->whereIn('lote_id', $chunk)
                ->delete();
        }
        foreach (array_chunk($lotesIniciales, 10000) as $chunk) {
            DB::table('inv_lotes')->whereIn('id', $chunk)->delete();
        }
    }

    private function inicializarStock(): void
    {
        $this->seqLote = $this->maxId('inv_lotes');
        $this->seqPed = $this->maxId('pedidos');
        $this->seqPedItem = $this->maxId('pedido_items');
        $this->seqTrans = $this->maxId('restaurante_transformaciones_materia_prima');
        $this->seqTransItem = $this->maxId('restaurante_transformacion_materia_prima_items');
        $this->seqProc = $this->maxId('procesos_cocina');
        $this->seqProcItem = $this->maxId('proceso_items');

        foreach (array_keys($this->variantes) as $varianteId) {
            $cantidad = mt_rand(40, 120);
            $this->stockCocina[$varianteId] = ['cantidad' => $cantidad, 'lotes' => []];
            $this->crearLoteYEntrada($varianteId, $cantidad, $this->inicio->copy()->setHour(6)->setMinute(0));
        }
    }

    private function procesarVencidos(CarbonInterface $fecha): void
    {
        $fechaStr = $fecha->format('Y-m-d');

        foreach ($this->stockCocina as $varianteId => &$stock) {
            $quitar = false;

            foreach ($stock['lotes'] as $i => &$lote) {
                if ($lote['qty'] <= 0.0001 || $lote['venc'] === null || $lote['venc'] > $fechaStr) {
                    continue;
                }

                $cantidad = $lote['qty'];

                $this->registrarMov(
                    tipo: 'MOV_AJUSTE',
                    loteId: (int) $lote['id'],
                    productoId: $this->variantes[$varianteId]['producto_id'],
                    cantidad: $cantidad,
                    costoUnitario: (float) $lote['costo'],
                    costoTotal: round((float) $lote['costo'] * $cantidad, 2),
                    origenId: $this->cocinaId,
                    destinoId: $this->mermaId,
                    documentoTipo: null,
                    documentoId: null,
                    referencia: 'Vencimiento lote '.$lote['codigo'],
                    fecha: $fechaStr.' 05:30:00',
                );

                $lote['qty'] = 0.0;
                $stock['cantidad'] = round($stock['cantidad'] - $cantidad, 4);
                $quitar = true;
            }
            unset($lote);

            if ($quitar) {
                $this->quitarLotesVacios($varianteId);
            }
        }
        unset($stock);
    }

    private function reabastecerDia(CarbonInterface $fecha): void
    {
        $n = mt_rand(12, 26);

        for ($i = 0; $i < $n; $i++) {
            $varianteId = $this->randomVariante();
            if ($varianteId === null) {
                continue;
            }

            $stock = &$this->stockCocina[$varianteId];
            $objetivo = mt_rand(120, 220);

            if ($stock['cantidad'] >= $objetivo) {
                continue;
            }

            $this->crearLoteYEntrada($varianteId, mt_rand(80, 200), $fecha->copy()->setHour(6)->setMinute(0));
        }
    }

    private function generarPedidosDia(CarbonInterface $fecha): void
    {
        $nPedidos = (int) round(mt_rand(self::PEDIDOS_MIN, self::PEDIDOS_MAX) * $this->escala);

        for ($i = 0; $i < $nPedidos; $i++) {
            $hora = mt_rand(7, 23);
            $minuto = mt_rand(0, 59);
            $segundo = mt_rand(0, 59);
            $abierto = $fecha->setHour($hora)->setMinute($minuto)->setSecond($segundo);

            $duracion = mt_rand(15, 90);
            $cerrado = $abierto->copy()->addMinutes($duracion);

            $esRoomService = mt_rand(0, 99) < 15;
            $mesaId = ($esRoomService || empty($this->mesaIds)) ? null : $this->mesaIds[mt_rand(0, count($this->mesaIds) - 1)];

            $rand = mt_rand(1, 100);
            $estado = $rand <= 2
                ? EstadoPedido::CANCELADO->value
                : ($rand <= 6 ? EstadoPedido::EN_PREPARACION->value
                    : ($rand <= 15 ? EstadoPedido::SERVIDO->value : EstadoPedido::PAGADO->value));
            $cancelado = $estado === EstadoPedido::CANCELADO->value;

            $pedidoId = ++$this->seqPed;
            if ($pedidoId > self::MAX_SEQ) {
                $this->seqPed = $this->maxId('pedidos') + 1;
                $pedidoId = ++$this->seqPed;
            }

            $codigo = 'KM-PDR-'.str_pad((string) $pedidoId, 8, '0', STR_PAD_LEFT);
            $numItems = mt_rand(1, 4);
            $subtotal = 0.0;
            $items = [];

            for ($j = 0; $j < $numItems; $j++) {
                $plato = $this->randomPlato() ?? null;
                if ($plato === null) {
                    continue;
                }

                $cantidad = mt_rand(1, 3);
                $precio = $plato['precio'];
                $sub = round($precio * $cantidad, 2);
                $subtotal += $sub;

                $itemId = ++$this->seqPedItem;
                $itemEstado = $cancelado ? EstadoItemPedido::ANULADO->value : EstadoItemPedido::SERVIDO->value;

                $items[] = [
                    'id' => $itemId,
                    'pedido_id' => $pedidoId,
                    'plato_id' => $plato['id'],
                    'tipo_item' => 'plato',
                    'producto_id' => null,
                    'producto_variante_id' => null,
                    'area_cocina' => $plato['area'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $sub,
                    'estado' => $itemEstado,
                    'notas' => null,
                    'observaciones' => null,
                    'created_at' => $abierto->format('Y-m-d H:i:s'),
                    'updated_at' => $abierto->format('Y-m-d H:i:s'),
                ];

                if (! $cancelado) {
                    foreach ($plato['receta'] as $ingrediente) {
                        $necesario = round($ingrediente['cantidad'] * $cantidad, 4);
                        $this->consumir(
                            varianteId: $ingrediente['variante_id'],
                            cantidad: $necesario,
                            fecha: $abierto,
                            documentoTipo: 'pedido_item',
                            documentoId: $itemId,
                            referencia: "Consumo de ingrediente para pedido #{$pedidoId}",
                        );
                    }
                }
            }

            $this->pedBatch[] = [
                'id' => $pedidoId,
                'codigo' => $codigo,
                'mesa_id' => $mesaId,
                'mesero_id' => $this->meseroId,
                'cliente_id' => null,
                'cuenta_id' => null,
                'estado' => $estado,
                'subtotal' => $subtotal,
                'total' => $cancelado ? 0.0 : $subtotal,
                'padre_pedido_id' => null,
                'consecutivo_comanda' => 1,
                'abierto_en' => $abierto->format('Y-m-d H:i:s'),
                'cerrado_en' => $estado === EstadoPedido::PAGADO->value || $estado === EstadoPedido::SERVIDO->value ? $cerrado->format('Y-m-d H:i:s') : null,
                'cargado_en' => $estado === EstadoPedido::PAGADO->value ? $cerrado->format('Y-m-d H:i:s') : null,
                'notas' => $esRoomService ? 'Room service / Pedido a habitación.' : null,
                'created_at' => $abierto->format('Y-m-d H:i:s'),
                'updated_at' => $cerrado->format('Y-m-d H:i:s'),
            ];

            foreach ($items as $item) {
                $this->pedItemBatch[] = $item;
            }
        }
    }

    private function generarTransformacionesDia(CarbonInterface $fecha): void
    {
        $n = mt_rand(10, 24);

        for ($i = 0; $i < $n; $i++) {
            $origenId = $this->randomVariante();
            if ($origenId === null) {
                continue;
            }

            $stockOrigen = &$this->stockCocina[$origenId];
            if ($stockOrigen['cantidad'] < 50) {
                $this->crearLoteYEntrada($origenId, mt_rand(100, 200), $fecha->copy()->setHour(6)->setMinute(0));
            }

            $cantidadProcesada = (float) mt_rand(40, 120);
            $costoOrigen = $this->costos[$origenId];
            $fechaStr = $fecha->copy()->setHour(mt_rand(8, 17))->setMinute(mt_rand(0, 59))->setSecond(0)->format('Y-m-d H:i:s');

            $transId = ++$this->seqTrans;
            $codigoTrans = 'KM-TRANS-'.str_pad((string) $transId, 8, '0', STR_PAD_LEFT);

            $transformacion = [
                'id' => $transId,
                'codigo' => $codigoTrans,
                'producto_origen_id' => $this->variantes[$origenId]['producto_id'],
                'variante_origen_id' => $origenId,
                'ubicacion_origen_id' => $this->cocinaId,
                'cantidad_procesada' => $cantidadProcesada,
                'costo_total' => 0,
                'realizado_por' => $this->randomUsuario(),
                'observaciones' => 'Molienda / porcionado de materia prima en cocina.',
                'created_at' => $fechaStr,
                'updated_at' => $fechaStr,
                'deleted_at' => null,
            ];

            $this->consumir(
                varianteId: $origenId,
                cantidad: $cantidadProcesada,
                fecha: Carbon::parse($fechaStr),
                documentoTipo: 'transformacion_materia_prima',
                documentoId: $transId,
                referencia: "Salida por transformación {$codigoTrans}",
                tipoMovimiento: 'TRANSFORMACION_SALIDA',
            );

            $nItems = mt_rand(3, 8);
            $costoTotal = 0.0;

            for ($j = 0; $j < $nItems; $j++) {
                $destinoId = $this->randomVariante();
                if ($destinoId === null || $destinoId === $origenId) {
                    $destinoId = $this->randomVariante();
                }

                if ($destinoId === null) {
                    continue;
                }

                $esMerma = mt_rand(0, 99) < 30;
                $cantidad = $esMerma ? (float) mt_rand(2, 20) : (float) mt_rand(10, 60);
                $costoAsignado = round($costoOrigen * $cantidad * (mt_rand(80, 120) / 100), 2);
                $costoTotal += $costoAsignado;

                $this->tmpItemBatch[] = [
                    'id' => ++$this->seqTransItem,
                    'transformacion_id' => $transId,
                    'producto_destino_id' => $this->variantes[$destinoId]['producto_id'],
                    'variante_destino_id' => $destinoId,
                    'ubicacion_destino_id' => $esMerma ? null : $this->cocinaId,
                    'cantidad' => $cantidad,
                    'costo_asignado' => $costoAsignado,
                    'es_merma' => $esMerma,
                    'observaciones' => $esMerma ? 'Merma final de proceso (recortes, evaporación, sobrante).' : null,
                    'created_at' => $fechaStr,
                    'updated_at' => $fechaStr,
                ];

                if ($esMerma) {
                    $this->registrarMov(
                        tipo: 'MERMA_COCINA',
                        loteId: null,
                        productoId: $this->variantes[$destinoId]['producto_id'],
                        cantidad: -$cantidad,
                        costoUnitario: round($costoAsignado / $cantidad, 6),
                        costoTotal: $costoAsignado,
                        origenId: $this->cocinaId,
                        destinoId: null,
                        documentoTipo: 'transformacion_materia_prima',
                        documentoId: $transId,
                        referencia: "Merma final de {$codigoTrans}",
                        fecha: $fechaStr,
                    );
                } else {
                    $this->crearLoteTransformado(
                        varianteDestinoId: $destinoId,
                        cantidad: $cantidad,
                        costoAsignado: $costoAsignado,
                        fecha: Carbon::parse($fechaStr),
                        documentoTipo: 'transformacion_materia_prima',
                        documentoId: $transId,
                        codigoTransformacion: $codigoTrans,
                    );
                }
            }

            $transformacion['costo_total'] = round($costoTotal, 2);
            $this->tmpBatch[] = $transformacion;
        }
    }

    private function generarProcesosDia(CarbonInterface $fecha): void
    {
        $n = mt_rand(8, 18);

        for ($i = 0; $i < $n; $i++) {
            $plato = $this->randomPlato() ?? null;
            if ($plato === null || empty($plato['receta'])) {
                continue;
            }

            $cantidadPlatos = mt_rand(8, 40);
            $procId = ++$this->seqProc;
            $codigoProc = 'KM-PRC-'.str_pad((string) $procId, 8, '0', STR_PAD_LEFT);
            $fechaStr = $fecha->copy()->setHour(mt_rand(6, 10))->setMinute(mt_rand(0, 59))->setSecond(0)->format('Y-m-d H:i:s');

            $proceso = [
                'id' => $procId,
                'codigo' => $codigoProc,
                'plato_id' => $plato['id'],
                'cantidad_platos' => $cantidadPlatos,
                'producto_origen_id' => $this->productoRecetaId($plato),
                'variante_origen_id' => null,
                'cantidad_procesada' => $cantidadPlatos,
                'costo_total' => 0,
                'realizado_por' => $this->randomUsuario(),
                'observaciones' => "Producción de {$cantidadPlatos} porciones de {$plato['nombre']}.",
                'created_at' => $fechaStr,
                'updated_at' => $fechaStr,
                'deleted_at' => null,
            ];

            $costoTotal = 0.0;

            foreach ($plato['receta'] as $ingrediente) {
                $cantidad = round($ingrediente['cantidad'] * $cantidadPlatos, 4);
                $costoAsignado = round($this->costos[$ingrediente['variante_id']] * $cantidad, 2);
                $costoTotal += $costoAsignado;

                $this->procItemBatch[] = [
                    'id' => ++$this->seqProcItem,
                    'proceso_id' => $procId,
                    'producto_destino_id' => $this->variantes[$ingrediente['variante_id']]['producto_id'],
                    'variante_destino_id' => $ingrediente['variante_id'],
                    'cantidad' => $cantidad,
                    'peso_unitario' => $this->variantes[$ingrediente['variante_id']]['peso'],
                    'peso_total' => round($cantidad, 3),
                    'costo_asignado' => $costoAsignado,
                    'es_merma' => false,
                    'ubicacion_destino_id' => null,
                    'created_at' => $fechaStr,
                    'updated_at' => $fechaStr,
                ];

                $this->consumir(
                    varianteId: $ingrediente['variante_id'],
                    cantidad: $cantidad,
                    fecha: Carbon::parse($fechaStr),
                    documentoTipo: 'proceso_cocina',
                    documentoId: $procId,
                    referencia: "Proceso cocina #{$procId}: {$plato['nombre']}",
                );
            }

            if (mt_rand(0, 99) < 15) {
                $mermaDestinoId = $this->randomVariante();
                if ($mermaDestinoId !== null) {
                    $cantidadMerma = round(mt_rand(5, 25) / 10, 2);
                    $costoMerma = round($this->costos[$mermaDestinoId] * $cantidadMerma, 2);
                    $costoTotal += $costoMerma;

                    $this->procItemBatch[] = [
                        'id' => ++$this->seqProcItem,
                        'proceso_id' => $procId,
                        'producto_destino_id' => $this->variantes[$mermaDestinoId]['producto_id'],
                        'variante_destino_id' => $mermaDestinoId,
                        'cantidad' => $cantidadMerma,
                        'peso_unitario' => null,
                        'peso_total' => null,
                        'costo_asignado' => $costoMerma,
                        'es_merma' => true,
                        'ubicacion_destino_id' => null,
                        'created_at' => $fechaStr,
                        'updated_at' => $fechaStr,
                    ];
                }
            }

            $proceso['costo_total'] = round($costoTotal, 2);
            $this->procBatch[] = $proceso;
        }
    }

    private function mermasPorVencer(CarbonInterface $fecha): void
    {
        $fechaStr = $fecha->format('Y-m-d');
        $limite = $fecha->copy()->addDays(30)->format('Y-m-d');

        $candidatos = [];
        foreach ($this->stockCocina as $varianteId => $stock) {
            foreach ($stock['lotes'] as $lote) {
                if ($lote['qty'] > 0.0001 && $lote['venc'] !== null && $lote['venc'] > $fechaStr && $lote['venc'] <= $limite) {
                    $candidatos[] = ['variante' => $varianteId, 'lote' => $lote];
                }
            }
        }

        if (empty($candidatos)) {
            return;
        }

        $n = min(mt_rand(3, 8), count($candidatos));

        for ($i = 0; $i < $n; $i++) {
            $sel = $candidatos[mt_rand(0, count($candidatos) - 1)];
            $lote = $sel['lote'];
            $cantidad = round(mt_rand(20, 80) / 10, 2);
            $cantidad = min($cantidad, (float) $lote['qty']);

            if ($cantidad <= 0.0001) {
                continue;
            }

            $this->registrarMov(
                tipo: 'BAJA_CADUCIDAD',
                loteId: (int) $lote['id'],
                productoId: $this->variantes[$sel['variante']]['producto_id'],
                cantidad: -$cantidad,
                costoUnitario: (float) $lote['costo'],
                costoTotal: round((float) $lote['costo'] * $cantidad, 2),
                origenId: $this->cocinaId,
                destinoId: null,
                documentoTipo: 'inv_lotes',
                documentoId: (int) $lote['id'],
                referencia: 'Merma por vencimiento del lote '.$lote['codigo'],
                fecha: $fechaStr.' '.sprintf('%02d:%02d:00', mt_rand(6, 9), mt_rand(0, 59)),
            );

            foreach ($this->stockCocina[$sel['variante']]['lotes'] as &$loteRef) {
                if ($loteRef['id'] === $lote['id']) {
                    $loteRef['qty'] = round($loteRef['qty'] - $cantidad, 4);
                }
            }
            unset($loteRef);

            $this->stockCocina[$sel['variante']]['cantidad'] = round(
                $this->stockCocina[$sel['variante']]['cantidad'] - $cantidad,
                4
            );
            $this->quitarLotesVacios($sel['variante']);
        }
    }

    private function consumir(
        int $varianteId,
        float $cantidad,
        CarbonInterface $fecha,
        ?string $documentoTipo,
        ?int $documentoId,
        string $referencia,
        string $tipoMovimiento = 'CONSUMO'
    ): void {
        if (! isset($this->stockCocina[$varianteId]) || $cantidad <= 0.0001) {
            return;
        }

        $stock = &$this->stockCocina[$varianteId];

        while ($stock['cantidad'] < $cantidad - 0.0001) {
            $faltante = $cantidad - $stock['cantidad'];
            $tamano = max(mt_rand(80, 200), (int) ceil($faltante));
            $this->crearLoteYEntrada($varianteId, (float) $tamano, $fecha->copy()->setHour(6)->setMinute(0));
        }

        $restante = min($cantidad, $stock['cantidad']);
        $stock['cantidad'] = round($stock['cantidad'] - $restante, 4);

        $lotesOrdenados = $stock['lotes'];
        umasort($lotesOrdenados, fn ($a, $b): int => $this->ordenFefo($a, $b));
        $stock['lotes'] = $lotesOrdenados;

        if ($lotesOrdenados !== []) {
            foreach ($lotesOrdenados as $key => $lote) {
                if ($restante <= 0.0001) {
                    break;
                }
                if (! isset($stock['lotes'][$key]) || $lote['qty'] <= 0.0001) {
                    continue;
                }

                $toma = min($lote['qty'], $restante);
                $lote['qty'] = round($lote['qty'] - $toma, 4);
                $stock['lotes'][$key]['qty'] = $lote['qty'];
                $restante = round($restante - $toma, 4);

                $this->registrarMov(
                    tipo: $tipoMovimiento,
                    loteId: (int) $lote['id'],
                    productoId: $this->variantes[$varianteId]['producto_id'],
                    cantidad: -$toma,
                    costoUnitario: (float) $lote['costo'],
                    costoTotal: round((float) $lote['costo'] * $toma, 2),
                    origenId: $this->cocinaId,
                    destinoId: null,
                    documentoTipo: $documentoTipo,
                    documentoId: $documentoId,
                    referencia: $referencia,
                    fecha: $fecha->format('Y-m-d H:i:s'),
                );
            }
        }

        $this->quitarLotesVacios($varianteId);

        unset($stock);
    }

    private function crearLoteYEntrada(int $varianteId, float $cantidad, CarbonInterface $fecha): void
    {
        $variante = $this->variantes[$varianteId];
        $costo = $this->costos[$varianteId];
        $venc = $this->generarVenc(elTipo: $variante['tipo'], desde: $fecha);

        $loteId = ++$this->seqLote;
        $codigo = 'KM-LOTE-'.str_pad((string) ($loteId % self::MAX_SEQ), 8, '0', STR_PAD_LEFT);

        $this->loteBatch[] = $this->filaLote(
            id: $loteId,
            codigo: $codigo,
            varianteId: $varianteId,
            productoId: $variante['producto_id'],
            cantidad: $cantidad,
            costo: $costo,
            venc: $venc,
            fecha: $fecha,
        );

        $this->stockCocina[$varianteId]['cantidad'] = round(($this->stockCocina[$varianteId]['cantidad'] ?? 0.0) + $cantidad, 4);
        $this->agregarLoteActivo($varianteId, [
            'id' => $loteId,
            'qty' => $cantidad,
            'venc' => $venc,
            'costo' => $costo,
            'codigo' => $codigo,
        ]);

        $this->registrarMov(
            tipo: 'MOV_ENTRADA',
            loteId: $loteId,
            productoId: $variante['producto_id'],
            cantidad: $cantidad,
            costoUnitario: $costo,
            costoTotal: round($costo * $cantidad, 2),
            origenId: null,
            destinoId: $this->cocinaId,
            documentoTipo: 'recepcion_cocina',
            documentoId: null,
            referencia: 'Entrada de mercadería cocina '.$codigo,
            fecha: $fecha->format('Y-m-d 06:00:00'),
        );
    }

    private function crearLoteTransformado(
        int $varianteDestinoId,
        float $cantidad,
        float $costoAsignado,
        CarbonInterface $fecha,
        ?string $documentoTipo,
        ?int $documentoId,
        string $codigoTransformacion
    ): void {
        $variante = $this->variantes[$varianteDestinoId];
        $costoUnitario = $cantidad > 0 ? round($costoAsignado / $cantidad, 6) : 0.0;
        $venc = $this->generarVenc(elTipo: $variante['tipo'], desde: $fecha);

        $loteId = ++$this->seqLote;
        $codigo = 'KM-TMP-'.str_pad((string) ($loteId % self::MAX_SEQ), 8, '0', STR_PAD_LEFT);

        $this->loteBatch[] = $this->filaLote(
            id: $loteId,
            codigo: $codigo,
            varianteId: $varianteDestinoId,
            productoId: $variante['producto_id'],
            cantidad: $cantidad,
            costo: $costoUnitario,
            venc: $venc,
            fecha: $fecha,
        );

        $this->stockCocina[$varianteDestinoId]['cantidad'] = round(($this->stockCocina[$varianteDestinoId]['cantidad'] ?? 0.0) + $cantidad, 4);
        $this->agregarLoteActivo($varianteDestinoId, [
            'id' => $loteId,
            'qty' => $cantidad,
            'venc' => $venc,
            'costo' => $costoUnitario,
            'codigo' => $codigo,
        ]);

        $this->registrarMov(
            tipo: 'TRANSFORMACION_ENTRADA',
            loteId: $loteId,
            productoId: $variante['producto_id'],
            cantidad: $cantidad,
            costoUnitario: $costoUnitario,
            costoTotal: $costoAsignado > 0 ? $costoAsignado : null,
            origenId: null,
            destinoId: $this->cocinaId,
            documentoTipo: $documentoTipo,
            documentoId: $documentoId,
            referencia: "Materia prima creada por {$codigoTransformacion}",
            fecha: $fecha->format('Y-m-d H:i:s'),
        );
    }

    /** @return array<string, mixed> */
    private function filaLote(
        int $id,
        string $codigo,
        int $varianteId,
        int $productoId,
        float $cantidad,
        float $costo,
        ?string $venc,
        CarbonInterface $fecha
    ): array {
        $ahora = now()->format('Y-m-d H:i:s');

        return [
            'id' => $id,
            'codigo_lote' => $codigo,
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'estado' => EstadoLote::Disponible->value,
            'cantidad_disponible' => $cantidad,
            'cantidad_inicial' => $cantidad,
            'costo_unitario' => $costo,
            'costo_total' => round($costo * $cantidad, 2),
            'ubicacion_id' => $this->cocinaId,
            'ubicacion_detalle_id' => null,
            'fecha_vencimiento' => $venc,
            'lote_proveedor' => null,
            'proveedor_id' => null,
            'fecha_recepcion' => $fecha->format('Y-m-d'),
            'recepcion_item_id' => null,
            'created_at' => $fecha->format('Y-m-d 06:00:00'),
            'updated_at' => $ahora,
            'deleted_at' => null,
        ];
    }

    /**
     * @param  array{venc: string|null, id: int}  $a
     * @param  array{venc: string|null, id: int}  $b
     */
    private function ordenFefo(array $a, array $b): int
    {
        if ($a['venc'] === null && $b['venc'] === null) {
            return $a['id'] <=> $b['id'];
        }
        if ($a['venc'] === null) {
            return 1;
        }
        if ($b['venc'] === null) {
            return -1;
        }

        $c = $a['venc'] <=> $b['venc'];

        return $c !== 0 ? $c : $a['id'] <=> $b['id'];
    }

    private function quitarLotesVacios(int $varianteId): void
    {
        $activos = [];

        foreach ($this->stockCocina[$varianteId]['lotes'] ?? [] as $lote) {
            if ($lote['qty'] > 0.0001) {
                $activos[] = $lote;
            }
        }

        $this->stockCocina[$varianteId]['lotes'] = $activos;
    }

    /**
     * Agrega un lote con existencia viva manteniendo la lista acotada.
     * Si la variante supera MAX_ACTIVE_LOTES, fusiona la cantidad del lote
     * más antiguo dentro del más reciente para no perder stock.
     *
     * @param  array{id: int, qty: float, venc: string|null, costo: float, codigo: string}  $lote
     */
    private function agregarLoteActivo(int $varianteId, array $lote): void
    {
        $lotes = &$this->stockCocina[$varianteId]['lotes'];
        $lotes[] = $lote;

        while (count($lotes) > self::MAX_ACTIVE_LOTES) {
            $viejo = array_shift($lotes);
            $ultimo = count($lotes) - 1;
            $lotes[$ultimo]['qty'] = round((float) $lotes[$ultimo]['qty'] + (float) $viejo['qty'], 4);
        }
    }

    private function generarVenc(int $elTipo, CarbonInterface $desde): ?string
    {
        $r = mt_rand(1, 100);

        $min = match ($elTipo) {
            1 => $r <= 55 ? mt_rand(3, 12) : ($r <= 85 ? mt_rand(15, 35) : 0),
            2 => $r <= 30 ? mt_rand(10, 25) : ($r <= 85 ? mt_rand(25, 90) : 0),
            default => $r <= 10 ? mt_rand(20, 40) : ($r <= 40 ? mt_rand(60, 180) : 0),
        };

        if ($min <= 0) {
            return null;
        }

        return $desde->copy()->addDays($min)->format('Y-m-d');
    }

    private function registrarMov(
        string $tipo,
        ?int $loteId,
        int $productoId,
        float $cantidad,
        ?float $costoUnitario,
        ?float $costoTotal,
        ?int $origenId,
        ?int $destinoId,
        ?string $documentoTipo,
        ?int $documentoId,
        string $referencia,
        string $fecha
    ): void {
        $this->movBatch[] = [
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
            'creado_por_id' => $this->randomUsuario(),
            'notas' => null,
            'created_at' => $fecha,
        ];
    }

    private function pendStock(): int
    {
        return count($this->loteBatch) + count($this->movBatch);
    }

    private function flushStock(): void
    {
        foreach (array_chunk($this->loteBatch, 2000) as $chunk) {
            DB::table('inv_lotes')->insert($chunk);
        }
        $this->loteBatch = [];

        foreach (array_chunk($this->movBatch, 2000) as $chunk) {
            DB::table('inv_movimientos')->insert($chunk);
        }
        $this->movBatch = [];
    }

    private function flushDocumentos(): void
    {
        foreach (array_chunk($this->tmpBatch, 300) as $chunk) {
            DB::table('restaurante_transformaciones_materia_prima')->insert($chunk);
        }
        $this->tmpBatch = [];

        foreach (array_chunk($this->tmpItemBatch, 1000) as $chunk) {
            DB::table('restaurante_transformacion_materia_prima_items')->insert($chunk);
        }
        $this->tmpItemBatch = [];

        foreach (array_chunk($this->procBatch, 300) as $chunk) {
            DB::table('procesos_cocina')->insert($chunk);
        }
        $this->procBatch = [];

        foreach (array_chunk($this->procItemBatch, 1000) as $chunk) {
            DB::table('proceso_items')->insert($chunk);
        }
        $this->procItemBatch = [];

        foreach (array_chunk($this->pedBatch, 500) as $chunk) {
            DB::table('pedidos')->insert($chunk);
        }
        $this->pedBatch = [];

        foreach (array_chunk($this->pedItemBatch, 1500) as $chunk) {
            DB::table('pedido_items')->insert($chunk);
        }
        $this->pedItemBatch = [];
    }

    private function sincronizarLotes(): void
    {
        $today = now()->format('Y-m-d');
        $ahora = now()->format('Y-m-d H:i:s');

        DB::table('inv_lotes')
            ->where('codigo_lote', 'like', 'KM-%')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', $today)
            ->update([
                'estado' => EstadoLote::Vencido->value,
                'cantidad_disponible' => 0,
                'updated_at' => $ahora,
            ]);

        $porLote = [];

        foreach ($this->stockCocina as $stock) {
            foreach ($stock['lotes'] as $lote) {
                if ($lote['qty'] <= 0.0001) {
                    continue;
                }

                $porLote[(int) $lote['id']] = (float) $lote['qty'];
            }
        }

        foreach (array_chunk(array_keys($porLote), 20000) as $chunkIds) {
            $caseCantidad = '';
            $caseEstado = '';

            foreach ($chunkIds as $id) {
                $caseCantidad .= ' WHEN '.(int) $id.' THEN '.number_format($porLote[$id], 4, '.', '');
                $caseEstado .= ' WHEN '.(int) $id.' THEN '.EstadoLote::Disponible->value;
            }

            DB::statement('UPDATE inv_lotes SET cantidad_disponible = (CASE id'.$caseCantidad.' ELSE cantidad_disponible END), estado = (CASE id'.$caseEstado.' ELSE estado END), updated_at = NOW() WHERE id IN ('.implode(',', $chunkIds).')');
        }

        DB::table('inv_lotes')
            ->where('codigo_lote', 'like', 'KM-%')
            ->where('estado', '!=', EstadoLote::Vencido->value)
            ->where('cantidad_disponible', '<=', 0.001)
            ->update([
                'estado' => EstadoLote::Agotado->value,
                'cantidad_disponible' => 0,
                'updated_at' => $ahora,
            ]);
    }

    private function sincronizarStocksCocina(): void
    {
        $ahora = now()->format('Y-m-d H:i:s');

        DB::table('inv_stock')->where('ubicacion_id', $this->cocinaId)->delete();

        foreach ($this->stockCocina as $varianteId => $stock) {
            $cantidad = max(0.0, round((float) $stock['cantidad'], 4));
            $loteHead = $this->loteFefoHead($stock['lotes']);

            $variante = $this->variantes[$varianteId];

            DB::table('stocks')->updateOrInsert(
                [
                    'stockable_type' => Ubicacion::class,
                    'stockable_id' => $this->cocinaId,
                    'producto_variante_id' => $varianteId,
                ],
                [
                    'producto_id' => $variante['producto_id'],
                    'lote_id' => $loteHead,
                    'cantidad_ideal' => $cantidad,
                    'cantidad_actual' => $cantidad,
                    'estado' => null,
                    'ultima_verificacion' => $ahora,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                    'deleted_at' => null,
                ]
            );

            foreach ($stock['lotes'] as $lote) {
                if ($lote['qty'] <= 0.0001) {
                    continue;
                }

                DB::table('inv_stock')->insert([
                    'producto_id' => $variante['producto_id'],
                    'producto_variante_id' => $varianteId,
                    'lote_id' => (int) $lote['id'],
                    'ubicacion_id' => $this->cocinaId,
                    'ubicacion_detalle_id' => null,
                    'cantidad' => round((float) $lote['qty'], 4),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }
    }

    /** @param list<array{id: int, qty: float, venc: ?string}> $lotes */
    private function loteFefoHead(array $lotes): ?int
    {
        $activos = array_values(array_filter($lotes, fn ($l): bool => $l['qty'] > 0.0001));

        if ($activos === []) {
            return null;
        }

        usort($activos, fn ($a, $b): int => $this->ordenFefo(['venc' => $a['venc'], 'id' => $a['id']], ['venc' => $b['venc'], 'id' => $b['id']]));

        return $activos[0]['id'];
    }

    private function sincronizarSecuencias(): void
    {
        $tablas = [
            'inv_lotes',
            'inv_movimientos',
            'inv_stock',
            'stocks',
            'pedidos',
            'pedido_items',
            'restaurante_transformaciones_materia_prima',
            'restaurante_transformacion_materia_prima_items',
            'procesos_cocina',
            'proceso_items',
        ];

        foreach ($tablas as $tabla) {
            $max = DB::table($tabla)->max('id');
            $valor = is_numeric($max) ? (int) $max : 1;

            try {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$tabla}', 'id'), COALESCE((SELECT MAX(id) FROM {$tabla}), 1))");
            } catch (\Throwable) {
                // SQLite u otras tablas sin secuencia serial
            }
        }
    }

    private function reportar(): void
    {
        $filas = [
            'inv_lotes (KM-%%)' => DB::table('inv_lotes')->where('codigo_lote', 'like', 'KM-%')->count(),
            'inv_movimientos (KM)' => $this->contarMovimientosKm(),
            'pedidos (KM)' => DB::table('pedidos')->where('codigo', 'like', 'KM-PDR-%')->count(),
            'pedido_items' => DB::table('pedido_items')->whereIn('pedido_id', DB::table('pedidos')->where('codigo', 'like', 'KM-PDR-%')->select('id'))->count(),
            'transformaciones (KM)' => DB::table('restaurante_transformaciones_materia_prima')->where('codigo', 'like', 'KM-TRANS-%')->count(),
            'procesos_cocina (KM)' => DB::table('procesos_cocina')->where('codigo', 'like', 'KM-PRC-%')->count(),
        ];

        $this->info('Resultados del histórico de cocina:');
        foreach ($filas as $clave => $fila) {
            $this->info("  - {$clave}: ".number_format($fila, 0, '.', ','));
        }
    }

    private function contarMovimientosKm(): int
    {
        $lotes = DB::table('inv_lotes')->where('codigo_lote', 'like', 'KM-%')->select('id');

        return DB::table('inv_movimientos')
            ->whereIn('lote_id', $lotes)
            ->count();
    }

    /** @return array{id: int, nombre: string, area: string, precio: float, receta: list<array{variante_id: int, cantidad: float}>}|null */
    private function randomPlato(): ?array
    {
        if (empty($this->platos)) {
            return null;
        }

        if (empty($this->platosPorArea)) {
            $this->platosPorArea['cocina'] = $this->platos;
        }

        $area = $this->randomArea();
        $lista = $this->platosPorArea[$area] ?? $this->platos;

        if ($lista === []) {
            $lista = $this->platos;
        }

        return $lista[mt_rand(0, count($lista) - 1)];
    }

    private function randomArea(): string
    {
        $pesos = [
            'cocina' => 40,
            'parrilla' => 20,
            'bar' => 25,
            'postres' => 15,
        ];

        $r = mt_rand(1, 100);
        $acumulado = 0;

        foreach ($pesos as $area => $peso) {
            $acumulado += $peso;
            if ($r <= $acumulado) {
                return $area;
            }
        }

        return 'cocina';
    }

    private function randomVariante(): ?int
    {
        $variantes = array_keys($this->variantes);
        if ($variantes === []) {
            return null;
        }

        return $variantes[mt_rand(0, count($variantes) - 1)];
    }

    private function randomUsuario(): int
    {
        if ($this->usuarioIds === []) {
            return $this->adminId;
        }

        return $this->usuarioIds[mt_rand(0, count($this->usuarioIds) - 1)];
    }

    /** @param array{id: int, nombre: string, area: string, precio: float, receta: list<array{variante_id: int, cantidad: float}>}|null $plato */
    private function productoRecetaId(?array $plato): ?int
    {
        if ($plato === null || ! isset($plato['receta'][0])) {
            return null;
        }

        return $this->variantes[$plato['receta'][0]['variante_id']]['producto_id'] ?? null;
    }

    private function maxId(string $tabla): int
    {
        $valor = DB::table($tabla)->max('id');

        return is_numeric($valor) ? (int) $valor : 0;
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

if (! function_exists('umasort')) {
    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<TKey, TValue>  $array
     * @param  callable(TValue, TValue): int  $cmp
     */
    function umasort(array &$array, callable $cmp): bool
    {
        $arrays = [];
        foreach ($array as $key => $value) {
            $arrays[] = [$key, $value];
        }

        usort($arrays, fn ($a, $b) => $cmp($a[1], $b[1]));

        $ordenado = [];
        foreach ($arrays as [$key, $value]) {
            $ordenado[$key] = $value;
        }

        $array = $ordenado;

        return true;
    }
}
