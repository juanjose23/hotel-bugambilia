<?php

declare(strict_types=1);

namespace Database\Seeders\Activos\Historico;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generador masivo del histórico concordante del módulo Activos.
 *
 * Por cada ítem de recepción HST de producto ActivoFijo (tipo = 3) se crea:
 *   Registro de Individualización → N activos unitarios → asignación física
 *   a Habitación/Espacio/Ubicación → mantenimientos → (5%) bajas definitivas.
 * La entrada de stock y el movimiento MOV_ENTRADA replican el comportamiento
 * del interactor IndividualizarActivos (documento_tipo = 'inv_activos').
 */
final class HistoricoActivosMasivoSeeder extends Seeder
{
    private const CHUNK_SIZE = 2000;

    private const ASIG_HABITACION = 'habitacion';

    private const ASIG_ESPACIO = 'espacio';

    private const ASIG_UBICACION = 'ubicacion';

    /** @var array<int, int> */
    private array $usuarioIds = [];

    /** @var array<int, int> */
    private array $habitacionIds = [];

    /** @var array<int|string, array{type: string, id: int}> */
    private array $espaciosPorProducto = [];

    /** @var array<string, int> */
    private array $ubicacionEspecialPorNombre = [];

    /** @var array<int, array{id: int, nombre: string, categoria: int}> */
    private array $productosTipo3 = [];

    private int $adminId = 1;

    private int $almacenId = 62;

    private int $nextRegistroId = 0;

    private int $nextActivoId = 0;

    private int $nextAsignacionId = 0;

    private int $nextMovimientoId = 0;

    private int $nextStockId = 0;

    private int $nextMantenimientoId = 0;

    private int $nextBajaId = 0;

    private int $seqActivo = 0;

    private int $seqBaja = 0;

    private int $totalActivos = 0;

    /** @var array<int|string, int> */
    private array $stockActivosPorVariante = [];

    /** @var array<int, array<string, mixed>> */
    private array $registroBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $activoBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $asignacionBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $movimientoBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $mantenimientoBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $bajaBatch = [];

    public function run(): void
    {
        mt_srand(2026100103);

        $this->cargarMetadatos();
        $this->inicializarContadores();
        $this->generarDesdeRecepciones();
        $this->generarStockActivos();
        $this->flush();

        $this->info("✓ Activos masivos: {$this->totalActivos} activos individualizados.");
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

        $this->habitacionIds = $this->aEnteros(DB::table('habitaciones')->whereNull('deleted_at')->pluck('id')->all());
        if ($this->habitacionIds === []) {
            $this->habitacionIds = $this->aEnteros(DB::table('habitaciones')->pluck('id')->all());
        }

        $espacios = DB::table('espacios')->get(['id', 'nombre']);
        $porNombre = [
            'gym' => 'Gimnasio Fitness Center',
            'piscina' => 'Piscina Infinity & Pool Lounge',
            'salon' => 'Gran Salón de Eventos Real Bugambilias',
            'restaurante' => 'Restaurante Bugambilias',
            'juntas' => 'Sala de Juntas & Coworking Ejecutivo',
            'bar' => 'Barra de Servicio',
        ];
        foreach ($espacios as $espacio) {
            foreach ($porNombre as $clave => $nombre) {
                if ($espacio->nombre === $nombre) {
                    $this->espaciosPorProducto[$clave] = ['type' => self::ASIG_ESPACIO, 'id' => (int) $espacio->id];
                }
            }
        }

        $marcadores = [
            'lavanderia' => 'Área de Lavado y Secado',
            'maquinas' => 'Cuarto de Máquinas',
            'recepcion' => 'Zona Recepción y Lobby',
            'anden' => 'Andén de Carga y Descarga',
        ];
        foreach ($marcadores as $clave => $nombre) {
            $id = DB::table('ubicaciones')->where('nombre', $nombre)->value('id');
            if (is_numeric($id)) {
                $this->ubicacionEspecialPorNombre[$clave] = (int) $id;
            }
        }

        $productos = DB::table('productos')->where('tipo', 3)->get(['id', 'nombre', 'categoria_id']);
        foreach ($productos as $p) {
            $this->productosTipo3[(int) $p->id] = [
                'id' => (int) $p->id,
                'nombre' => (string) $p->nombre,
                'categoria' => (int) $p->categoria_id,
            ];
        }
    }

    private function inicializarContadores(): void
    {
        $this->nextRegistroId = $this->maxId('inv_registro_individualizacion');
        $this->nextActivoId = $this->maxId('inv_activos');
        $this->nextAsignacionId = $this->maxId('inv_activo_asignaciones');
        $this->nextMovimientoId = $this->maxId('inv_movimientos');
        $this->nextStockId = $this->maxId('inv_stock');
        $this->nextMantenimientoId = $this->maxId('inv_mantenimientos');
        $this->nextBajaId = $this->maxId('inv_activo_bajas');
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

    private function generarDesdeRecepciones(): void
    {
        $ahora = now();

        DB::table('recepciones_compra as rc')
            ->join('recepcion_items as ri', 'ri.recepcion_id', '=', 'rc.id')
            ->join('orden_compra_items as oi', 'oi.id', '=', 'ri.orden_item_id')
            ->join('ordenes_compra as oc', 'oc.id', '=', 'rc.orden_compra_id')
            ->join('cotizaciones as cot', 'cot.id', '=', 'oc.cotizacion_id')
            ->join('productos as p', 'p.id', '=', 'ri.producto_id')
            ->where('rc.codigo', 'like', 'RC-HST-%')
            ->where('p.tipo', 3)
            ->select([
                'ri.id',
                'ri.producto_id',
                'ri.producto_variante_id',
                'ri.cantidad_recibida',
                'rc.fecha_recepcion',
                'oc.proveedor_id',
                'oi.precio_unitario',
                'cot.moneda_id',
            ])
            ->orderBy('ri.id')
            ->chunk(500, function ($filas) use ($ahora): void {
                foreach ($filas as $fila) {
                    $this->procesarRecepcionItem($fila, $ahora);
                }

                $this->flush();
            });
    }

    private function procesarRecepcionItem(object $fila, CarbonInterface $ahora): void
    {
        $productoId = $this->enteroDe($fila, 'producto_id');
        $varianteId = $this->enteroNuloDe($fila, 'producto_variante_id');
        $recepcionItemId = $this->enteroDe($fila, 'id');
        $producto = $this->productosTipo3[$productoId] ?? ['id' => $productoId, 'nombre' => 'Activo Fijo', 'categoria' => 0];
        $cantidadTotal = (int) round($this->decimalDe($fila, 'cantidad_recibida'));
        if ($cantidadTotal < 1) {
            return;
        }

        $fechaAdquisicion = $this->fechaDe($fila, 'fecha_recepcion');
        $fechaAdquisicionStr = $fechaAdquisicion->format('Y-m-d');
        $costoAdquisicion = $this->decimalDe($fila, 'precio_unitario');
        $monedaId = $this->enteroDe($fila, 'moneda_id');
        $proveedorId = $this->enteroDe($fila, 'proveedor_id');
        $fechaGarantiaFin = $fechaAdquisicion->copy()->addMonths(12)->format('Y-m-d');

        $registroId = ++$this->nextRegistroId;

        $this->registroBatch[] = [
            'id' => $registroId,
            'recepcion_item_id' => $recepcionItemId,
            'producto_id' => $producto['id'],
            'producto_variante_id' => $varianteId,
            'cantidad_total' => $cantidadTotal,
            'cantidad_registrada' => $cantidadTotal,
            'estado' => 3,
            'registrado_por_id' => $this->adminId,
            'fecha_completado' => $fechaAdquisicionStr.' 11:00:00',
            'notes' => 'Individualización histórica masiva [HST]',
            'created_at' => $fechaAdquisicionStr.' 11:00:00',
            'updated_at' => $fechaAdquisicionStr.' 11:00:00',
        ];

        $edadMeses = (int) max(0, round($fechaAdquisicion->startOfDay()->diffInMonths($ahora->startOfDay())));
        $tendraBaja = $edadMeses >= 4 && mt_rand(1, 100) <= 5;

        $fechaInicioAsignacion = $fechaAdquisicion->copy()->addDays(mt_rand(0, 4));
        if ($fechaInicioAsignacion->greaterThan($ahora)) {
            $fechaInicioAsignacion = $ahora->copy();
        }

        $edadDias = (int) max(0, $fechaInicioAsignacion->startOfDay()->diffInDays($ahora->startOfDay()));

        for ($u = 1; $u <= $cantidadTotal; $u++) {
            $esBaja = $tendraBaja && $u === 1;

            $manteFuturo = false;
            $nTickets = 0;
            if (! $esBaja) {
                if ($edadDias < 25) {
                    $manteFuturo = mt_rand(1, 100) <= 12;
                } else {
                    $nTickets = mt_rand(1, 100) <= 30 ? mt_rand(1, 2) : 0;
                }
            }

            $activoId = ++$this->nextActivoId;
            $seq = ++$this->seqActivo;
            $this->totalActivos++;

            $codigoInventario = 'HST-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);

            $this->activoBatch[] = [
                'id' => $activoId,
                'codigo_inventario' => $codigoInventario,
                'individualizacion_id' => $registroId,
                'recepcion_item_id' => $recepcionItemId,
                'producto_id' => $producto['id'],
                'producto_variante_id' => $varianteId,
                'nombre_descriptivo' => "{$producto['nombre']} #{$seq}",
                'numero_serie' => $this->numeroSerie($producto),
                'fecha_adquisicion' => $fechaAdquisicionStr,
                'costo_adquisicion' => $costoAdquisicion,
                'moneda_id' => $monedaId,
                'proveedor_id' => $proveedorId,
                'vida_util_meses' => 60,
                'fecha_garantia_fin' => $fechaGarantiaFin,
                'estado' => $esBaja ? 3 : ($manteFuturo ? 2 : 1),
                'notas' => $esBaja ? 'Baja registrada en histórico [HST]' : null,
                'created_at' => $fechaAdquisicionStr.' 11:00:00',
                'updated_at' => $fechaAdquisicionStr.' 11:00:00',
            ];

            $destino = $this->destinoDelProducto($producto['id']);
            $tipoAsignable = match ($destino['type']) {
                self::ASIG_ESPACIO => Espacio::class,
                self::ASIG_UBICACION => Ubicacion::class,
                default => Habitacion::class,
            };

            $fechaBaja = null;
            if ($esBaja) {
                $fechaBaja = $fechaInicioAsignacion->copy()->addMonths(mt_rand(4, 8))->format('Y-m-d');
                if ($fechaBaja > $ahora->format('Y-m-d')) {
                    $fechaBaja = $ahora->format('Y-m-d');
                }
                $this->generarBaja($activoId, $fechaBaja, $costoAdquisicion);
            } else {
                $this->stockActivosPorVariante[$this->claveStock($productoId, $varianteId)] =
                    ($this->stockActivosPorVariante[$this->claveStock($productoId, $varianteId)] ?? 0) + 1;
            }

            $this->asignacionBatch[] = [
                'id' => ++$this->nextAsignacionId,
                'activo_id' => $activoId,
                'asignable_type' => $tipoAsignable,
                'asignable_id' => $destino['id'],
                'fecha_inicio' => $fechaInicioAsignacion->format('Y-m-d'),
                'fecha_fin' => $esBaja ? $fechaBaja : null,
                'motivo' => $this->motivoAsignacion($producto['nombre']),
                'asignado_por_id' => $this->adminId,
                'recibido_por_id' => $this->random($this->usuarioIds),
                'estado' => $esBaja ? 2 : 1,
                'notas' => null,
                'created_at' => $fechaAdquisicionStr.' 11:30:00',
                'updated_at' => $fechaAdquisicionStr.' 11:30:00',
            ];

            $this->movimientoBatch[] = [
                'id' => ++$this->nextMovimientoId,
                'tipo' => 'MOV_ENTRADA',
                'lote_id' => null,
                'producto_id' => $producto['id'],
                'cantidad' => 1.0,
                'costo_unitario' => $costoAdquisicion,
                'costo_total' => $costoAdquisicion,
                'ubicacion_origen_id' => null,
                'ubicacion_destino_id' => $this->almacenId,
                'documento_tipo' => 'inv_activos',
                'documento_id' => $activoId,
                'referencia' => "Individualización de activo: {$codigoInventario}",
                'creado_por_id' => $this->adminId,
                'notas' => null,
                'created_at' => $fechaAdquisicionStr.' 11:00:00',
            ];

            if (! $esBaja && $nTickets > 0) {
                $this->generarMantenimientos($activoId, $fechaInicioAsignacion, $ahora, $nTickets);
            } elseif (! $esBaja && $manteFuturo) {
                $fechaProgramada = $fechaInicioAsignacion->copy()->addDays(mt_rand(5, 20));
                while ($fechaProgramada->lessThanOrEqualTo($ahora)) {
                    $fechaProgramada->addMonth();
                }

                $this->mantenimientoBatch[] = $this->mantenimiento(
                    $activoId,
                    $fechaProgramada,
                    null,
                    null,
                    1,
                    'Correctivo programado por garantía [HST]',
                    $ahora
                );
            }
        }
    }

    private function generarMantenimientos(int $activoId, CarbonInterface $fechaInicio, CarbonInterface $ahora, int $nTickets): void
    {
        $maxDias = (int) max(30, (int) max(0, $fechaInicio->startOfDay()->diffInDays($ahora->startOfDay())) - 10);
        $minDias = min(30, $maxDias);

        for ($i = 0; $i < $nTickets; $i++) {
            $fechaProgramada = $fechaInicio->copy()->addDays(mt_rand($minDias, $maxDias));

            $fechaRealizada = $fechaProgramada->copy()->addDays(mt_rand(1, 3));
            if ($fechaRealizada->greaterThan($ahora)) {
                $fechaRealizada = $ahora->copy();
            }

            $this->mantenimientoBatch[] = $this->mantenimiento(
                $activoId,
                $fechaProgramada,
                $fechaRealizada,
                round(mt_rand(15, 160) + (mt_rand(0, 99) / 100), 2),
                3,
                'Mantenimiento histórico completado [HST]',
                $ahora
            );
        }
    }

    /**
     * @return array<string, scalar|null>
     */
    private function mantenimiento(
        int $activoId,
        CarbonInterface $fechaProgramada,
        ?CarbonInterface $fechaRealizada,
        ?float $costoReal,
        int $estado,
        string $notas,
        CarbonInterface $ahora
    ): array {
        return [
            'id' => ++$this->nextMantenimientoId,
            'plan_id' => null,
            'tipo' => ['preventivo', 'correctivo', 'inspeccion'][mt_rand(0, 2)],
            'activo_id' => $activoId,
            'fecha_programada' => $fechaProgramada->format('Y-m-d'),
            'fecha_realizada' => $fechaRealizada?->format('Y-m-d'),
            'costo_real' => $costoReal,
            'realizado_por_id' => $this->random($this->usuarioIds),
            'estado' => $estado,
            'notas' => $notas,
            'created_at' => $ahora->format('Y-m-d H:i:s'),
            'updated_at' => $ahora->format('Y-m-d H:i:s'),
        ];
    }

    private function generarBaja(int $activoId, string $fechaBaja, float $costoAdquisicion): void
    {
        $motivosTipos = ['obsolescencia', 'daño_irreparable', 'robo', 'perdida', 'donacion', 'venta'];
        $motivoTipo = $motivosTipos[mt_rand(0, count($motivosTipos) - 1)];

        $this->bajaBatch[] = [
            'id' => ++$this->nextBajaId,
            'codigo' => 'BAJA-HST-'.str_pad((string) (++$this->seqBaja), 6, '0', STR_PAD_LEFT),
            'activo_id' => $activoId,
            'fecha_baja' => $fechaBaja,
            'motivo_tipo' => $motivoTipo,
            'motivo_detalle' => $this->motivoBaja($motivoTipo),
            'valor_residual' => round($costoAdquisicion * (mt_rand(5, 30) / 100), 2),
            'aprobado_por_id' => $this->adminId,
            'creado_por_id' => $this->adminId,
            'documento_soporte' => null,
            'created_at' => $fechaBaja.' 10:00:00',
            'updated_at' => $fechaBaja.' 10:00:00',
        ];
    }

    /** @return array{type: string, id: int} */
    private function destinoDelProducto(int $productoId): array
    {
        static $porProducto = null;
        if ($porProducto === null) {
            $porProducto = $this->construirMapaDestinos();
        }

        return $porProducto[$productoId] ?? [
            'type' => self::ASIG_HABITACION,
            'id' => $this->habitacionIds[mt_rand(0, count($this->habitacionIds) - 1)],
        ];
    }

    /** @return array<int, array{type: string, id: int}> */
    private function construirMapaDestinos(): array
    {
        $mapa = [];

        $espacio = fn (string $clave) => ($this->espaciosPorProducto[$clave] ?? null);
        $ubi = fn (string $clave) => (isset($this->ubicacionEspecialPorNombre[$clave])
            ? ['type' => self::ASIG_UBICACION, 'id' => $this->ubicacionEspecialPorNombre[$clave]]
            : null);

        $asignacionesEquipos = [
            200 => $espacio('gym'), 201 => $espacio('gym'),
            202 => $espacio('piscina'),
            195 => $espacio('salon'), 196 => $espacio('salon'), 197 => $espacio('salon'),
            198 => $espacio('salon'), 199 => $espacio('salon'), 234 => $espacio('salon'),
            203 => $espacio('restaurante'), 219 => $espacio('restaurante'), 233 => $espacio('restaurante'),
            204 => $espacio('restaurante'), 205 => $espacio('restaurante'), 206 => $espacio('restaurante'),
            207 => $espacio('restaurante'), 208 => $espacio('restaurante'), 209 => $espacio('restaurante'),
            262 => $espacio('restaurante'), 263 => $espacio('restaurante'), 264 => $espacio('restaurante'),
            265 => $espacio('restaurante'), 271 => $espacio('restaurante'),
            222 => $espacio('juntas'), 236 => $espacio('juntas'),
            220 => $ubi('recepcion'), 221 => $ubi('recepcion'), 259 => $ubi('recepcion'),
            268 => $ubi('recepcion'), 270 => $ubi('recepcion'),
            249 => $ubi('lavanderia'), 250 => $ubi('lavanderia'), 251 => $ubi('lavanderia'),
            246 => $ubi('maquinas'), 247 => $ubi('maquinas'), 248 => $ubi('maquinas'),
            252 => $ubi('maquinas'), 253 => $ubi('maquinas'), 254 => $ubi('maquinas'),
            257 => $ubi('maquinas'), 258 => $ubi('maquinas'), 260 => $ubi('maquinas'),
            267 => $ubi('maquinas'),
            329 => $ubi('maquinas'), 330 => $ubi('anden'), 331 => $ubi('maquinas'),
            332 => $ubi('maquinas'), 333 => $ubi('anden'), 334 => $ubi('maquinas'),
            335 => $ubi('anden'),
        ];

        foreach ($asignacionesEquipos as $id => $destino) {
            if ($destino !== null) {
                $mapa[$id] = $destino;
            }
        }

        return $mapa;
    }

    private function generarStockActivos(): void
    {
        foreach ($this->stockActivosPorVariante as $clave => $cantidad) {
            [$productoId, $varianteId] = explode(':', (string) $clave);

            $this->stockBatch[] = [
                'id' => ++$this->nextStockId,
                'producto_id' => (int) $productoId,
                'producto_variante_id' => ((int) $varianteId) > 0 ? (int) $varianteId : null,
                'lote_id' => null,
                'ubicacion_id' => $this->almacenId,
                'ubicacion_detalle_id' => null,
                'cantidad' => (float) $cantidad,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
    }

    private function claveStock(int $productoId, ?int $varianteId): string
    {
        return $productoId.':'.($varianteId ?? 0);
    }

    /**
     * @param  array{id: int, nombre: string, categoria: int}  $producto
     */
    private function numeroSerie(array $producto): string
    {
        $etiqueta = preg_replace('/[^A-Za-z]/', '', $producto['nombre']);
        $base = is_string($etiqueta) && $etiqueta !== '' ? $etiqueta : 'EQ';

        return 'SN-HST-'.strtoupper(substr($base, 0, 4)).'-'.mt_rand(100000, 999999);
    }

    private function motivoAsignacion(string $productoNombre): string
    {
        return 'Equipamiento fijo asignado tras individualización histórica: '.$productoNombre;
    }

    private function motivoBaja(string $motivoTipo): string
    {
        return match ($motivoTipo) {
            'obsolescencia' => 'Equipo obsoleto fuera de ciclo de vida útil.',
            'daño_irreparable' => 'Daño irreversible constatado por mantenimiento.',
            'robo' => 'Activo reportado como robado y dado de baja.',
            'perdida' => 'Activo perdido sin recuperación posible.',
            'donacion' => 'Equipo donado tras fin de vida útil.',
            default => 'Venta de equipo de segunda mano registrada.',
        };
    }

    /** @var array<int, array<string, mixed>> */
    private array $stockBatch = [];

    private function flush(): void
    {
        if ($this->registroBatch !== []) {
            foreach (array_chunk($this->registroBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_registro_individualizacion')->insert($chunk);
            }
            $this->registroBatch = [];
        }
        if ($this->activoBatch !== []) {
            foreach (array_chunk($this->activoBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_activos')->insert($chunk);
            }
            $this->activoBatch = [];
        }
        if ($this->movimientoBatch !== []) {
            foreach (array_chunk($this->movimientoBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_movimientos')->insert($chunk);
            }
            $this->movimientoBatch = [];
        }
        if ($this->stockBatch !== []) {
            foreach (array_chunk($this->stockBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_stock')->insert($chunk);
            }
            $this->stockBatch = [];
        }
        if ($this->asignacionBatch !== []) {
            foreach (array_chunk($this->asignacionBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_activo_asignaciones')->insert($chunk);
            }
            $this->asignacionBatch = [];
        }
        if ($this->mantenimientoBatch !== []) {
            foreach (array_chunk($this->mantenimientoBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_mantenimientos')->insert($chunk);
            }
            $this->mantenimientoBatch = [];
        }
        if ($this->bajaBatch !== []) {
            foreach (array_chunk($this->bajaBatch, self::CHUNK_SIZE) as $chunk) {
                DB::table('inv_activo_bajas')->insert($chunk);
            }
            $this->bajaBatch = [];
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
