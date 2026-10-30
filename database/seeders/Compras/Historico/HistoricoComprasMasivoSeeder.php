<?php

declare(strict_types=1);

namespace Database\Seeders\Compras\Historico;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generador masivo del histórico concordante del módulo Compras.
 *
 * Arquitectura de flujo P2P que SIEMPRE se respeta:
 *   Solicitud → Cotización (única elegida) → Orden de Compra → Recepción.
 *
 * Estrategia bulk (Patrón HistoricoMedioMillonSeeder):
 *   - Inserciones SQL por tabla en chunks, con IDs explícitos.
 *   - Fechas históricas backdateadas mes a mes (12 meses).
 *   - Últimos 1-2 meses en "pipeline activo" (solicitudes sin adjudicar,
 *     OC en tránsito/emitida) para que el sistema luzca vivo.
 *   - Prefijos idempotentes: SOL-HST-% / OC-HST-% / RC-HST-% / [HST].
 */
final class HistoricoComprasMasivoSeeder extends Seeder
{
    public const MESES = 12;

    public const CHUNK_SIZE = 2000;

    /** @var array<int, int> */
    private array $proveedorIds = [];

    /** @var array<int, int> */
    private array $colaboradorIds = [];

    /** @var array<int, int> */
    private array $usuarioIds = [];

    /** @var array<int, int> */
    private array $departamentoIds = [];

    /** @var array<int, int> */
    private array $condicionPagoIds = [];

    /** @var array<int, array{id: int, tipo: int, um: int|null, nombre: string}> */
    private array $consumibles = [];

    /** @var array<int, array{id: int, tipo: int, um: int|null, nombre: string}> */
    private array $equipos = [];

    /** @var array<int, array<int, int>> */
    private array $variantesPorProducto = [];

    /** @var array<int, array<int, int>> */
    private array $contactosPorProveedor = [];

    private int $adminId = 1;

    private int $nextSolicitudId = 0;

    private int $nextSolicitudItemId = 0;

    private int $nextCotizacionId = 0;

    private int $nextCotizacionItemId = 0;

    private int $nextOrdenId = 0;

    private int $nextOrdenItemId = 0;

    private int $nextRecepcionId = 0;

    private int $nextRecepcionItemId = 0;

    private int $nextHistorialId = 0;

    private int $seqSolicitud = 0;

    private int $seqOrden = 0;

    private int $seqRecepcion = 0;

    /** @var array<int, array<string, mixed>> */
    private array $solBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $solItemBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $cotBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $cotItemBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $ocBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $ocItemBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $rcBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $rcItemBatch = [];

    /** @var array<int, array<string, mixed>> */
    private array $histBatch = [];

    private string $anioActual = '';

    public function run(): void
    {
        mt_srand(2026100101);

        $this->anioActual = (string) now()->year;

        $this->cargarMetadatos();
        $this->inicializarContadores();
        $this->generar();

        $this->info("✓ Compras masivas: {$this->seqSolicitud} solicitudes · {$this->seqOrden} OC · {$this->seqRecepcion} recepciones.");
    }

    private function cargarMetadatos(): void
    {
        $this->proveedorIds = $this->aEnteros(DB::table('proveedores')->pluck('id')->all());
        $this->colaboradorIds = $this->aEnteros(DB::table('colaboradores')->pluck('id')->all());
        $this->departamentoIds = $this->aEnteros(DB::table('catalogos')->where('catalogo_tipo_id', 2)->pluck('id')->all());
        $this->condicionPagoIds = $this->aEnteros(DB::table('catalogos')->where('catalogo_tipo_id', 9)->pluck('id')->all());

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

        $productos = DB::table('productos')->whereNotNull('tipo')->get(['id', 'nombre', 'tipo', 'unidad_medida_id']);
        foreach ($productos as $p) {
            $tipo = (int) $p->tipo;
            $item = [
                'id' => (int) $p->id,
                'tipo' => $tipo,
                'um' => $p->unidad_medida_id !== null ? (int) $p->unidad_medida_id : null,
                'nombre' => (string) $p->nombre,
            ];
            if ($tipo === 3) {
                $this->equipos[] = $item;
            } else {
                $this->consumibles[] = $item;
            }
        }

        $variantes = DB::table('producto_variantes')->get(['id', 'producto_id']);
        foreach ($variantes as $v) {
            $this->variantesPorProducto[(int) $v->producto_id][] = (int) $v->id;
        }

        $contactos = DB::table('proveedor_contactos')->get(['id', 'proveedor_id']);
        foreach ($contactos as $c) {
            $this->contactosPorProveedor[(int) $c->proveedor_id][] = (int) $c->id;
        }
    }

    private function inicializarContadores(): void
    {
        $this->nextSolicitudId = $this->maxId('solicitudes_compra');
        $this->nextSolicitudItemId = $this->maxId('solicitud_items');
        $this->nextCotizacionId = $this->maxId('cotizaciones');
        $this->nextCotizacionItemId = $this->maxId('cotizacion_items');
        $this->nextOrdenId = $this->maxId('ordenes_compra');
        $this->nextOrdenItemId = $this->maxId('orden_compra_items');
        $this->nextRecepcionId = $this->maxId('recepciones_compra');
        $this->nextRecepcionItemId = $this->maxId('recepcion_items');
        $this->nextHistorialId = $this->maxId('compra_historial');
    }

    private function maxId(string $tabla): int
    {
        $valor = DB::table($tabla)->max('id');

        return is_numeric($valor) ? (int) $valor : 0;
    }

    private function generar(): void
    {
        $ahora = now();

        for ($mesIdx = 0; $mesIdx < self::MESES; $mesIdx++) {
            $mes = $ahora->copy()->startOfMonth()->subMonths(self::MESES - 1 - $mesIdx);

            $totalSolicitudes = mt_rand(380, 620);
            $info = "  -> Mes {$mes->format('Y-m')}: {$totalSolicitudes} solicitudes";

            for ($i = 0; $i < $totalSolicitudes; $i++) {
                $this->generarSolicitud($mesIdx, $mes);
            }

            $this->flush();

            $this->info("{$info} ({$this->countFilas()} filas acumuladas)");
        }

        $this->flush(true);
    }

    private function generarSolicitud(int $mesIdx, CarbonInterface $mesBase): void
    {
        $solicitudId = ++$this->nextSolicitudId;
        $seq = ++$this->seqSolicitud;
        $codigo = sprintf('SOL-HST-%s-%04d', $this->anioActual, $seq);

        $fechaSolicitud = $this->fechaEnMes($mesBase);

        $estadoSolicitud = $this->estadoSolicitud($mesIdx);
        $aprobada = $estadoSolicitud === 3;

        $departamentoId = $this->aleatorio($this->departamentoIds);
        $colaboradorId = $this->aleatorio($this->colaboradorIds);

        $this->solBatch[] = [
            'id' => $solicitudId,
            'codigo' => $codigo,
            'colaborador_id' => $colaboradorId,
            'departamento_solicitante_id' => $departamentoId,
            'fecha_solicitud' => $fechaSolicitud,
            'fecha_necesita' => $aprobada ? $this->sumarDias($fechaSolicitud, mt_rand(5, 15)) : null,
            'motivo' => $this->motivoSolicitud($mesIdx),
            'estado' => $estadoSolicitud,
            'notas' => 'Solicitud histórica concordante [HST]',
            'created_at' => $fechaSolicitud.' '.sprintf('%02d:%02d:00', mt_rand(8, 17), mt_rand(0, 59)),
            'updated_at' => $fechaSolicitud.' '.sprintf('%02d:%02d:00', mt_rand(8, 17), mt_rand(0, 59)),
        ];

        $items = [];

        if ($aprobada || $estadoSolicitud === 2 || $estadoSolicitud === 1) {
            $items = $this->generarItemsSolicitud($mesIdx, $solicitudId, $aprobada, $fechaSolicitud);
        }

        $this->histBatch[] = $this->historial(
            'Solicitud',
            $solicitudId,
            null,
            $this->labelSolicitud($estadoSolicitud),
            null,
            $fechaSolicitud
        );

        if ($aprobada) {
            $this->histBatch[] = $this->historial(
                'Solicitud',
                $solicitudId,
                'Pendiente',
                'Aprobada',
                'Aprobada por Compras [HST]',
                $this->sumarDias($fechaSolicitud, 1)
            );

            $conOC = $this->unaOCPorMes($mesIdx);
            if ($conOC) {
                $this->generarAdjudicacionYOrden($mesIdx, $solicitudId, $fechaSolicitud, $items, $departamentoId);
            }
        } elseif ($estadoSolicitud === 4) {
            $this->histBatch[] = $this->historial(
                'Solicitud',
                $solicitudId,
                'Pendiente',
                'Rechazada',
                'Rechazada por Compras [HST]',
                $this->sumarDias($fechaSolicitud, 2)
            );
        } elseif ($estadoSolicitud === 5) {
            $this->histBatch[] = $this->historial(
                'Solicitud',
                $solicitudId,
                'Borrador',
                'Cancelada',
                'Cancelada por solicitante [HST]',
                $this->sumarDias($fechaSolicitud, 1)
            );
        }
    }

    /**
     * @return list<array{producto: array{id:int, tipo:int, um:int|null, nombre:string}, variante:int|null, cantidad:float}>
     */
    private function generarItemsSolicitud(int $mesIdx, int $solicitudId, bool $aprobada, string $fechaSolicitud): array
    {
        $items = [];
        $cantItems = mt_rand(2, 8);

        for ($k = 0; $k < $cantItems; $k++) {
            $producto = $this->elegirProducto();
            $cantidad = $this->cantidadPara($producto['tipo']);
            $items[] = [
                'producto' => $producto,
                'variante' => $this->elegirVariante($producto['id']),
                'cantidad' => $cantidad,
            ];

            $this->solItemBatch[] = [
                'id' => ++$this->nextSolicitudItemId,
                'solicitud_id' => $solicitudId,
                'producto_id' => $producto['id'],
                'producto_variante_id' => $items[count($items) - 1]['variante'],
                'unidad_medida_id' => $producto['um'],
                'cantidad_solicitada' => $cantidad,
                'cantidad_aprobada' => $aprobada ? $cantidad : 0,
                'observaciones' => 'Insumo operativo histórico [HST]',
                'created_at' => $fechaSolicitud.' 09:00:00',
                'updated_at' => $fechaSolicitud.' 09:00:00',
            ];
        }

        return $items;
    }

    /**
     * @param  list<array{producto: array{id:int, tipo:int, um:int|null, nombre:string}, variante:int|null, cantidad:float}>  $items
     */
    private function generarAdjudicacionYOrden(
        int $mesIdx,
        int $solicitudId,
        string $fechaSolicitud,
        array $items,
        int $departamentoId
    ): void {
        $rondas = mt_rand(2, 4);
        $ganador = mt_rand(0, $rondas - 1);
        $totalItems = count($items);

        $cotizacionesDatos = [];

        for ($r = 0; $r < $rondas; $r++) {
            $proveedorId = $this->aleatorio($this->proveedorIds);
            $cotizacionId = ++$this->nextCotizacionId;
            $fechaCotizacion = $this->sumarDias($fechaSolicitud, $r + 1);
            $esElegida = $r === $ganador;
            $diasEntrega = $esElegida ? mt_rand(3, 15) : mt_rand(5, 20);

            $subtotal = 0.0;

            // La elegida cotiza todos los ítems; las demás una parte (comparativa realista).
            $cobertura = $esElegida ? 1.0 : (mt_rand(35, 80) / 100);
            $itemsCotizados = 0;

            foreach ($items as $item) {
                if (! $esElegida && ($itemsCotizados / $totalItems) >= $cobertura) {
                    continue;
                }
                $itemsCotizados++;

                $precioUnitario = round($this->precioBase($item['producto']) * $this->jitter($esElegida ? 1.0 : 1.35), 2);
                $subtotalItem = round($item['cantidad'] * $precioUnitario, 2);
                $subtotal += $subtotalItem;

                $this->cotItemBatch[] = [
                    'id' => ++$this->nextCotizacionItemId,
                    'cotizacion_id' => $cotizacionId,
                    'producto_id' => $item['producto']['id'],
                    'producto_variante_id' => $item['variante'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalItem,
                    'es_elegido' => $esElegida,
                    'created_at' => $fechaCotizacion.' 09:00:00',
                    'updated_at' => $fechaCotizacion.' 09:00:00',
                ];
            }

            $condicionPagoId = $this->aleatorio($this->condicionPagoIds);
            $impuestos = round($subtotal * 0.15, 2);
            $totalOferta = round($subtotal + $impuestos, 2);

            $this->cotBatch[] = [
                'id' => $cotizacionId,
                'solicitud_id' => $solicitudId,
                'proveedor_id' => $proveedorId,
                'fecha_cotizacion' => $fechaCotizacion,
                'fecha_vencimiento' => $this->sumarDias($fechaCotizacion, mt_rand(20, 40)),
                'dias_entrega' => $diasEntrega,
                'condicion_pago_id' => $condicionPagoId,
                'subtotal' => round($subtotal, 2),
                'impuestos' => $impuestos,
                'costo_envio' => 0.0,
                'descuento' => 0.0,
                'total' => $totalOferta,
                'moneda_id' => 2,
                'tasa_cambio' => 1.0000,
                'archivo_pdf' => null,
                'es_elegida' => $esElegida,
                'estado' => $esElegida ? 3 : 1,
                'observaciones' => 'Comparativa de precios [HST]',
                'creada_por' => $this->adminId,
                'elegida_por' => $esElegida ? $this->adminId : null,
                'elegida_en' => $esElegida ? $this->sumarDias($fechaCotizacion, 1).' 10:00:00' : null,
                'created_at' => $fechaCotizacion.' 09:00:00',
                'updated_at' => $fechaCotizacion.' 09:00:00',
            ];

            if ($esElegida) {
                $this->histBatch[] = $this->historial(
                    'Cotizacion',
                    $cotizacionId,
                    'Activa',
                    'Aceptada',
                    'Adjudicada para generar OC [HST]',
                    $this->sumarDias($fechaCotizacion, 1)
                );
                $cotizacionesDatos['ganadora'] = [
                    'id' => $cotizacionId,
                    'proveedor_id' => $proveedorId,
                    'contacto_id' => $this->random($this->contactosPorProveedor[$proveedorId] ?? []),
                    'dias_entrega' => mt_rand(3, 15),
                    'condicion_pago_id' => $condicionPagoId,
                    'fecha' => $fechaCotizacion,
                ];
            }
        }

        if (! isset($cotizacionesDatos['ganadora'])) {
            return;
        }

        $this->generarOrdenYRecepcion($mesIdx, $solicitudId, $cotizacionesDatos['ganadora'], $items, $fechaSolicitud, $departamentoId);
    }

    /**
     * @param  array{id:int, proveedor_id:int, contacto_id:int|null, dias_entrega:int, condicion_pago_id:int, fecha:string}  $ganadora
     * @param  list<array{producto: array{id:int, tipo:int, um:int|null, nombre:string}, variante:int|null, cantidad:float}>  $items
     */
    private function generarOrdenYRecepcion(
        int $mesIdx,
        int $solicitudId,
        array $ganadora,
        array $items,
        string $fechaSolicitud,
        int $departamentoId
    ): void {
        $ordenId = ++$this->nextOrdenId;
        $seq = ++$this->seqOrden;
        $codigo = sprintf('OC-HST-%s-%06d', $this->anioActual, $seq);

        $fechaOrden = $this->sumarDias($fechaSolicitud, mt_rand(3, 8));
        $fechaEntregaEstimada = $this->sumarDias($fechaOrden, $ganadora['dias_entrega']);

        $estadoOrden = $this->estadoOrden($mesIdx);

        $contactoId = $ganadora['contacto_id'];

        $subtotal = 0.0;
        $ordenItemIds = [];

        foreach ($items as $item) {
            $precioUnitario = round($this->precioBase($item['producto']) * 1.0, 2);
            $subtotalItem = round($item['cantidad'] * $precioUnitario, 2);
            $subtotal += $subtotalItem;

            $ordenItemId = ++$this->nextOrdenItemId;
            $ordenItemIds[] = $ordenItemId;

            $this->ocItemBatch[] = [
                'id' => $ordenItemId,
                'orden_compra_id' => $ordenId,
                'producto_id' => $item['producto']['id'],
                'producto_variante_id' => $item['variante'],
                'unidad_medida_id' => $item['producto']['um'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotalItem,
                'created_at' => $fechaOrden.' 09:30:00',
                'updated_at' => $fechaOrden.' 09:30:00',
            ];
        }

        $this->ocBatch[] = [
            'id' => $ordenId,
            'codigo' => $codigo,
            'proveedor_id' => $ganadora['proveedor_id'],
            'proveedor_contacto_id' => $contactoId,
            'solicitud_id' => $solicitudId,
            'cotizacion_id' => $ganadora['id'],
            'fecha_orden' => $fechaOrden,
            'fecha_entrega_estimada' => $fechaEntregaEstimada,
            'condicion_pago_id' => $ganadora['condicion_pago_id'],
            'subtotal' => round($subtotal, 2),
            'impuestos' => round($subtotal * 0.15, 2),
            'total' => round($subtotal * 1.15, 2),
            'tasa_cambio' => 1.0000,
            'estado' => $estadoOrden,
            'notas' => 'Orden histórico concordante [HST]',
            'created_at' => $fechaOrden.' 09:30:00',
            'updated_at' => $fechaOrden.' 09:30:00',
        ];

        $this->histBatch[] = $this->historial(
            'OrdenCompra',
            $ordenId,
            null,
            'Borrador',
            'OC creada desde cotización ganadora [HST]',
            $fechaOrden
        );

        if (in_array($estadoOrden, [2, 3, 4, 6], true)) {
            $this->histBatch[] = $this->historial(
                'OrdenCompra',
                $ordenId,
                'Borrador',
                'Emitida',
                'OC emitida al proveedor [HST]',
                $fechaOrden
            );
        }

        if (in_array($estadoOrden, [4, 6], true)) {
            $tieneRecepcion = $estadoOrden === 4;
            $this->histBatch[] = $this->historial(
                'OrdenCompra',
                $ordenId,
                'Emitida',
                $estadoOrden === 4 ? 'Recibida' : 'Parcial',
                'Mercancía recibida en Almacén General [HST]',
                $this->sumarDias($fechaOrden, $ganadora['dias_entrega'])
            );

            $this->generarRecepcion($mesIdx, $ordenId, $ordenItemIds, $items, $fechaOrden, $ganadora, $estadoOrden === 6);
        }
    }

    /**
     * @param  list<int>  $ordenItemIds
     * @param  list<array{producto: array{id:int, tipo:int, um:int|null, nombre:string}, variante:int|null, cantidad:float}>  $items
     * @param  array{id:int, proveedor_id:int, contacto_id:int|null, dias_entrega:int, condicion_pago_id:int, fecha:string}  $ganadora
     */
    private function generarRecepcion(
        int $mesIdx,
        int $ordenId,
        array $ordenItemIds,
        array $items,
        string $fechaOrden,
        array $ganadora,
        bool $parcial
    ): void {
        $recepcionId = ++$this->nextRecepcionId;
        $seq = ++$this->seqRecepcion;
        $codigo = sprintf('RC-HST-%s-%06d', $this->anioActual, $seq);

        $fechaRecepcion = $this->sumarDias($fechaOrden, $ganadora['dias_entrega'] + mt_rand(0, 2));

        $this->rcBatch[] = [
            'id' => $recepcionId,
            'codigo' => $codigo,
            'orden_compra_id' => $ordenId,
            'fecha_recepcion' => $fechaRecepcion,
            'guia_remision' => 'GUIA-HST-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT),
            'recibido_por_id' => $this->random($this->usuarioIds) ?? $this->adminId,
            'ubicacion_id' => $this->ubicacionAlmacenId(),
            'estado' => $parcial ? 2 : 1,
            'notas' => $parcial ? 'Recepción parcial, pendiente saldo de la OC [HST]' : 'Entrega completa según contrato [HST]',
            'created_at' => $fechaRecepcion.' '.sprintf('%02d:%02d:00', mt_rand(8, 16), mt_rand(0, 59)),
            'updated_at' => $fechaRecepcion.' '.sprintf('%02d:%02d:00', mt_rand(8, 16), mt_rand(0, 59)),
        ];

        $this->histBatch[] = $this->historial(
            'RecepcionCompra',
            $recepcionId,
            null,
            $parcial ? 'Parcial' : 'Completa',
            'Recepción física registrada [HST]',
            $fechaRecepcion
        );

        foreach ($items as $idx => $item) {
            $cantidadRecibida = $parcial ? round($item['cantidad'] * (mt_rand(50, 90) / 100), 0) : (float) $item['cantidad'];
            $cantidadRecibida = max(1.0, $cantidadRecibida);

            $esPerecedero = $item['producto']['tipo'] === 1;

            $this->rcItemBatch[] = [
                'id' => ++$this->nextRecepcionItemId,
                'recepcion_id' => $recepcionId,
                'orden_item_id' => $ordenItemIds[$idx],
                'producto_id' => $item['producto']['id'],
                'producto_variante_id' => $item['variante'],
                'unidad_medida_id' => $item['producto']['um'],
                'cantidad_recibida' => $cantidadRecibida,
                'cantidad_rechazada' => 0.0,
                'motivo_rechazo' => null,
                'nota' => null,
                'lote_proveedor' => $esPerecedero ? 'HST-LP-'.strtoupper(substr(md5((string) $recepcionId.$idx), 0, 8)) : null,
                'fecha_vencimiento' => $esPerecedero ? $this->sumarDias($fechaRecepcion, mt_rand(30, 180)) : null,
                'ubicacion_id' => null,
                'created_at' => $fechaRecepcion.' 09:45:00',
                'updated_at' => $fechaRecepcion.' 09:45:00',
            ];
        }
    }

    /** @return array{id:int, tipo:int, um:int|null, nombre:string} */
    private function elegirProducto(): array
    {
        $esEquipo = mt_rand(1, 100) <= 22;
        $pool = $esEquipo && $this->equipos !== [] ? $this->equipos : $this->consumibles;

        return $pool[mt_rand(0, count($pool) - 1)];
    }

    private function cantidadPara(int $tipo): float
    {
        return match ($tipo) {
            1 => (float) mt_rand(20, 200),
            3 => (float) mt_rand(1, 12),
            default => (float) mt_rand(10, 150),
        };
    }

    private function elegirVariante(int $productoId): ?int
    {
        $variantes = $this->variantesPorProducto[$productoId] ?? [];

        return $variantes === [] || mt_rand(1, 100) <= 62 ? null : $variantes[mt_rand(0, count($variantes) - 1)];
    }

    /**
     * @param  array{id:int, tipo:int, um:int|null, nombre:string}  $producto
     */
    private function precioBase(array $producto): float
    {
        return match ($producto['tipo']) {
            1 => round(1.5 + ($producto['id'] % 24) * 0.9, 2),
            3 => round(90.0 + ($producto['id'] % 55) * 23.0, 2),
            default => round(4.0 + ($producto['id'] % 37) * 2.3, 2),
        };
    }

    private function jitter(float $max): float
    {
        return 0.85 + (mt_rand(0, 1000) / 1000) * ($max - 0.85);
    }

    private function estadoSolicitud(int $mesIdx): int
    {
        $rand = mt_rand(1, 100);
        if ($mesIdx >= 10) {
            return match (true) {
                $rand <= 60 => 3,
                $rand <= 88 => 2,
                $rand <= 96 => 1,
                default => 5,
            };
        }
        if ($mesIdx === 9) {
            return match (true) {
                $rand <= 84 => 3,
                $rand <= 95 => 2,
                default => 4,
            };
        }

        return match (true) {
            $rand <= 96 => 3,
            $rand <= 98 => 2,
            $rand <= 99 => 4,
            default => 5,
        };
    }

    private function estadoOrden(int $mesIdx): int
    {
        $rand = mt_rand(1, 100);
        if ($mesIdx >= 10) {
            return match (true) {
                $rand <= 5 => 4,
                $rand <= 25 => 2,
                $rand <= 70 => 3,
                $rand <= 90 => 1,
                default => 5,
            };
        }
        if ($mesIdx === 9) {
            return match (true) {
                $rand <= 45 => 4,
                $rand <= 60 => 6,
                $rand <= 85 => 3,
                $rand <= 95 => 2,
                default => 5,
            };
        }

        return match (true) {
            $rand <= 92 => 4,
            $rand <= 97 => 6,
            default => 5,
        };
    }

    private function unaOCPorMes(int $mesIdx): bool
    {
        $rand = mt_rand(1, 100);
        if ($mesIdx >= 10) {
            return $rand <= 45;
        }
        if ($mesIdx === 9) {
            return $rand <= 75;
        }

        return true;
    }

    private function motivoSolicitud(int $mesIdx): string
    {
        $motivos = [
            'Reposición de insumos para operación diaria.',
            'Reabastecimiento de inventario para alto flujo de huéspedes.',
            'Reposición de material de limpieza y dotación de habitaciones.',
            'Compra preventiva para mantenimiento preventivo y correctivo.',
            'Requerimiento de cocina y alimentos para servicio de restaurante.',
            'Insumos de limpieza lavandería y lencería.',
            'Reposición de consumibles para áreas comunes y eventos.',
            'Abastecimiento para temporada alta.',
        ];

        return $motivos[mt_rand(0, count($motivos) - 1)];
    }

    private function labelSolicitud(int $estado): string
    {
        return match ($estado) {
            1 => 'Borrador',
            2 => 'Pendiente',
            4 => 'Rechazada',
            5 => 'Cancelada',
            default => 'Aprobada',
        };
    }

    /**
     * @return array<string, scalar|null>
     */
    private function historial(
        string $modelType,
        int $modelId,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $comentario,
        string $fecha
    ): array {
        return [
            'id' => ++$this->nextHistorialId,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'usuario_id' => $this->adminId,
            'comentario' => ($comentario ?? 'Cambio de estado [HST]').' [HST]',
            'created_at' => $fecha.' 10:00:00',
            'updated_at' => $fecha.' 10:00:00',
        ];
    }

    private function flush(bool $forzar = false): void
    {
        if (! $forzar && $this->countFilas() < 4000) {
            return;
        }

        if ($this->solBatch !== []) {
            $this->insertar('solicitudes_compra', $this->solBatch);
            $this->solBatch = [];
        }
        if ($this->solItemBatch !== []) {
            $this->insertar('solicitud_items', $this->solItemBatch);
            $this->solItemBatch = [];
        }
        if ($this->cotBatch !== []) {
            $this->insertar('cotizaciones', $this->cotBatch);
            $this->cotBatch = [];
        }
        if ($this->cotItemBatch !== []) {
            $this->insertar('cotizacion_items', $this->cotItemBatch);
            $this->cotItemBatch = [];
        }
        if ($this->ocBatch !== []) {
            $this->insertar('ordenes_compra', $this->ocBatch);
            $this->ocBatch = [];
        }
        if ($this->ocItemBatch !== []) {
            $this->insertar('orden_compra_items', $this->ocItemBatch);
            $this->ocItemBatch = [];
        }
        if ($this->rcBatch !== []) {
            $this->insertar('recepciones_compra', $this->rcBatch);
            $this->rcBatch = [];
        }
        if ($this->rcItemBatch !== []) {
            $this->insertar('recepcion_items', $this->rcItemBatch);
            $this->rcItemBatch = [];
        }
        if ($this->histBatch !== []) {
            $this->insertar('compra_historial', $this->histBatch);
            $this->histBatch = [];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function insertar(string $tabla, array $filas): void
    {
        foreach (array_chunk($filas, self::CHUNK_SIZE) as $chunk) {
            DB::table($tabla)->insert($chunk);
        }
    }

    private function countFilas(): int
    {
        return count($this->solBatch)
            + count($this->solItemBatch)
            + count($this->cotBatch)
            + count($this->cotItemBatch)
            + count($this->ocBatch)
            + count($this->ocItemBatch)
            + count($this->rcBatch)
            + count($this->rcItemBatch)
            + count($this->histBatch);
    }

    private function fechaEnMes(CarbonInterface $mesBase): string
    {
        $dia = mt_rand(1, (int) $mesBase->daysInMonth);

        return $mesBase->copy()->day($dia)->format('Y-m-d');
    }

    private function sumarDias(string $fecha, int $dias): string
    {
        return Carbon::createFromFormat('Y-m-d', $fecha)?->addDays($dias)->format('Y-m-d') ?? $fecha;
    }

    private function ubicacionAlmacenId(): int
    {
        static $almacenId = null;
        if ($almacenId === null) {
            $ubicacion = DB::table('ubicaciones')->where('tipo', 'almacen')->value('id');
            $almacenId = is_numeric($ubicacion) ? (int) $ubicacion : 62;
        }

        return $almacenId;
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
     * @param  array<int, int>  $items
     */
    private function aleatorio(array $items): int
    {
        if ($items === []) {
            throw new \RuntimeException('Lista vacía para selección aleatoria en histórico de compras.');
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
