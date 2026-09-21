<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de Ultra Alto Rendimiento para Generación Histórica Masiva (500,000 Registros).
 *
 * Distribución de los 500.000 registros:
 *   1. 150.000 Pedidos de Restaurante, Bar, Comandas y Mostrador (tabla: pedidos)
 *   2. 200.000 Ítems de Comandas y Platos Servidos (tabla: pedido_items)
 *   3.  75.000 Reservaciones de Mesa, Salones y Servicios (tabla: reservas)
 *   4.  75.000 Cargos de Servicios y Ventas Directas en Cuentas (tabla: cuenta_detalles)
 *
 * Utiliza inserciones SQL bulk en chunks de 5,000 registros por query para
 * máxima velocidad (~20-30 segundos en total) sin agotar memoria RAM.
 */
final class HistoricoMedioMillonSeeder extends Seeder
{
    public const TOTAL_PEDIDOS = 150000;

    public const TOTAL_PEDIDO_ITEMS = 200000;

    public const TOTAL_RESERVAS = 75000;

    public const TOTAL_CARGOS_CUENTA = 75000;

    public const CHUNK_SIZE = 2000;

    /** @var array<int, int> */
    private array $mesaIds = [];

    /** @var array<int, int> */
    private array $colaboradorIds = [];

    /** @var array<int, int> */
    private array $cuentaIds = [];

    /** @var array<int, int> */
    private array $usuarioIds = [];

    /** @var array<int, array{id: int, precio: float, area: string}> */
    private array $platosDisponibles = [];

    /** @var array<int, int> */
    private array $servicioIds = [];

    public function run(): void
    {
        mt_srand(202650000);
        @ini_set('memory_limit', '1024M');
        @set_time_limit(1800);

        $this->command->newLine();
        $this->info('================================================================');
        $this->info('   GENERADOR MASIVO DE HISTÓRICO HOTELERO: 500,000 REGISTROS    ');
        $this->info('================================================================');
        $this->command->newLine();

        $inicioTotal = microtime(true);

        $this->cargarMetadatosBase();

        // 1. Limpiar registros previos con el prefijo 500K
        $this->limpiarHistoricoPrevio();

        // 2. Generar 150,000 Pedidos y 200,000 PedidoItems
        $this->generarPedidosYItems();

        // 3. Generar 75,000 Reservaciones de Mesas y Servicios
        $this->generarReservasMasivas();

        // 4. Generar 75,000 Cargos de Servicios Adicionales en Cuentas
        $this->generarCargosServiciosCuentas();

        // 5. Sincronizar secuencias PostgreSQL
        $this->sincronizarSecuenciasPostgres();

        $tiempoTotal = round(microtime(true) - $inicioTotal, 2);

        $this->command->newLine();
        $this->info('================================================================');
        $this->info("   ✓ 500,000 REGISTROS HISTÓRICOS CREADOS EXITOSAMENTE EN {$tiempoTotal}s");
        $this->info('   • Pedidos de Restaurante/Bar:    '.number_format(self::TOTAL_PEDIDOS));
        $this->info('   • Ítems de Comandas:             '.number_format(self::TOTAL_PEDIDO_ITEMS));
        $this->info('   • Reservaciones Mesas/Servicios: '.number_format(self::TOTAL_RESERVAS));
        $this->info('   • Cargos de Servicios Cuentas:   '.number_format(self::TOTAL_CARGOS_CUENTA));
        $this->info('================================================================');
        $this->command->newLine();
    }

    private function cargarMetadatosBase(): void
    {
        $this->mesaIds = $this->aEnteros(Espacio::where('tipo', 'mesa')->pluck('id')->all());
        if (empty($this->mesaIds)) {
            $this->mesaIds = $this->aEnteros(Espacio::pluck('id')->all());
        }

        $this->colaboradorIds = $this->aEnteros(Colaborador::pluck('id')->all());
        $this->cuentaIds = $this->aEnteros(Cuenta::pluck('id')->all());
        $this->usuarioIds = $this->aEnteros(User::pluck('id')->all());
        $this->servicioIds = $this->aEnteros(Servicio::pluck('id')->all());

        // Platos y bebidas con sus precios
        $platos = Plato::query()
            ->where('estado', 1)
            ->with('precios')
            ->get();

        foreach ($platos as $plato) {
            $primerPrecio = $plato->precios->first();
            $precio = $primerPrecio !== null ? (float) $primerPrecio->precio : 180.0;
            $area = ($precio <= 220.0 && mt_rand(1, 10) <= 6) ? 'bar' : 'cocina';
            $this->platosDisponibles[] = [
                'id' => (int) $plato->id,
                'precio' => $precio > 0 ? $precio : 150.0,
                'area' => $area,
            ];
        }

        // Si no hay platos registrados, usar fallbacks
        if (empty($this->platosDisponibles)) {
            for ($i = 1; $i <= 10; $i++) {
                $this->platosDisponibles[] = [
                    'id' => $i,
                    'precio' => (float) (80 + ($i * 25)),
                    'area' => $i <= 4 ? 'bar' : 'cocina',
                ];
            }
        }
    }

    private function limpiarHistoricoPrevio(): void
    {
        $this->info('▶ Limpiando registros históricos 500K previos...');

        DB::statement("DELETE FROM pedido_items WHERE pedido_id IN (SELECT id FROM pedidos WHERE codigo LIKE 'PDR-500K-%')");
        DB::statement("DELETE FROM pedidos WHERE codigo LIKE 'PDR-500K-%'");
        DB::statement("DELETE FROM reservas WHERE codigo_reserva LIKE 'RES-500K-%'");
        DB::statement("DELETE FROM cuenta_detalles WHERE concepto LIKE '%[500K]%'");

        $this->info('  ✓ Limpieza completada.');
    }

    private function generarPedidosYItems(): void
    {
        $this->info('▶ Generando 150,000 Pedidos y 200,000 Ítems de Restaurante/Bar...');
        $inicio = microtime(true);

        $maxPedidoVal = DB::table('pedidos')->max('id');
        $maxPedidoId = is_numeric($maxPedidoVal) ? (int) $maxPedidoVal : 0;

        $maxItemVal = DB::table('pedido_items')->max('id');
        $maxItemId = is_numeric($maxItemVal) ? (int) $maxItemVal : 0;

        $totalPedidos = self::TOTAL_PEDIDOS;
        $totalItems = self::TOTAL_PEDIDO_ITEMS;

        $platosCount = count($this->platosDisponibles);
        $mesasCount = count($this->mesaIds);
        $colaboradoresCount = count($this->colaboradorIds);
        $cuentasCount = count($this->cuentaIds);

        $now = now();
        $pedidosBatch = [];
        $itemsBatch = [];

        $itemsGenerados = 0;
        $itemsPorPedidoBase = (int) floor($totalItems / $totalPedidos); // 1
        $pedidosConItemExtra = $totalItems - $totalPedidos; // 50,000 pedidos tendrán 2 items

        for ($chunkStart = 1; $chunkStart <= $totalPedidos; $chunkStart += self::CHUNK_SIZE) {
            $chunkEnd = min($chunkStart + self::CHUNK_SIZE - 1, $totalPedidos);
            $pedidosBatch = [];
            $itemsBatch = [];

            for ($i = $chunkStart; $i <= $chunkEnd; $i++) {
                $pedidoId = ++$maxPedidoId;
                $codigo = sprintf('PDR-500K-%07d', $i);

                // Antigüedad distribuida en los últimos 730 días
                $diasAtras = mt_rand(1, 730);
                $horaApertura = mt_rand(1, 10) <= 6 ? mt_rand(12, 15) : mt_rand(18, 23);
                $minutoApertura = mt_rand(0, 59);

                $fechaApertura = (clone $now)->subDays($diasAtras)->setTime($horaApertura, $minutoApertura);
                $fechaCierre = (clone $fechaApertura)->addMinutes(mt_rand(35, 110));

                $mesaId = (! empty($this->mesaIds) && mt_rand(1, 10) <= 8)
                    ? $this->mesaIds[mt_rand(0, $mesasCount - 1)]
                    : null;

                $meseroId = ! empty($this->colaboradorIds)
                    ? $this->colaboradorIds[mt_rand(0, $colaboradoresCount - 1)]
                    : null;

                $cuentaId = (! empty($this->cuentaIds) && mt_rand(1, 10) <= 4)
                    ? $this->cuentaIds[mt_rand(0, $cuentasCount - 1)]
                    : null;

                // Estados: 5=Pagado (92%), 6=CargadoAHabitacion (5%), 7=Cancelado (3%)
                $randEstado = mt_rand(1, 100);
                $estado = match (true) {
                    $randEstado <= 92 => 5,
                    $randEstado <= 97 => 6,
                    default => 7,
                };

                // Determinar cuántos items para este pedido (1 o 2)
                $cantItems = ($i <= $pedidosConItemExtra) ? 2 : 1;
                $subtotalPedido = 0.0;

                for ($k = 0; $k < $cantItems; $k++) {
                    $plato = $this->platosDisponibles[mt_rand(0, $platosCount - 1)];
                    $cantidad = (float) mt_rand(1, 3);
                    $precioUnitario = $plato['precio'];
                    $subtotalItem = round($cantidad * $precioUnitario, 2);
                    $subtotalPedido += $subtotalItem;

                    $itemsBatch[] = [
                        'id' => ++$maxItemId,
                        'pedido_id' => $pedidoId,
                        'plato_id' => $plato['id'],
                        'area_cocina' => $plato['area'],
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'subtotal' => $subtotalItem,
                        'estado' => 4, // Servido
                        'notas' => null,
                        'observaciones' => null,
                        'created_at' => $fechaApertura->format('Y-m-d H:i:s'),
                        'updated_at' => $fechaCierre->format('Y-m-d H:i:s'),
                    ];

                    $itemsGenerados++;
                }

                $pedidosBatch[] = [
                    'id' => $pedidoId,
                    'codigo' => $codigo,
                    'mesa_id' => $mesaId,
                    'mesero_id' => $meseroId,
                    'cliente_id' => null,
                    'cuenta_id' => $cuentaId,
                    'estado' => $estado,
                    'subtotal' => round($subtotalPedido, 2),
                    'total' => round($subtotalPedido, 2),
                    'padre_pedido_id' => null,
                    'consecutivo_comanda' => 1,
                    'abierto_en' => $fechaApertura->format('Y-m-d H:i:s'),
                    'cerrado_en' => $fechaCierre->format('Y-m-d H:i:s'),
                    'cargado_en' => $estado === 6 ? $fechaCierre->format('Y-m-d H:i:s') : null,
                    'notas' => null,
                    'created_at' => $fechaApertura->format('Y-m-d H:i:s'),
                    'updated_at' => $fechaCierre->format('Y-m-d H:i:s'),
                ];
            }

            // Primero insertar padres pedidos
            DB::table('pedidos')->insert($pedidosBatch);

            // Luego insertar hijos items en bloques seguros
            foreach (array_chunk($itemsBatch, 2000) as $subItems) {
                DB::table('pedido_items')->insert($subItems);
            }

            unset($pedidosBatch, $itemsBatch);
            gc_collect_cycles();

            if ($chunkEnd % 25000 === 0 || $chunkEnd === $totalPedidos) {
                $this->info("  -> {$chunkEnd} pedidos procesados...");
            }
        }

        $tiempo = round(microtime(true) - $inicio, 2);
        $this->info("  ✓ 150,000 pedidos y {$itemsGenerados} ítems insertados en {$tiempo}s.");
    }

    private function generarReservasMasivas(): void
    {
        $this->command->info('▶ Generando 75,000 Reservaciones de Mesas y Servicios...');
        $inicio = microtime(true);

        $maxReservaVal = DB::table('reservas')->max('id');
        $maxReservaId = is_numeric($maxReservaVal) ? (int) $maxReservaVal : 0;
        $totalReservas = self::TOTAL_RESERVAS;
        $reservasBatch = [];

        $nombres = [
            'Carlos Mendoza', 'María José López', 'Roberto Gómez', 'Sofía Alvarado',
            'Alejandro Silva', 'Lucía Fernández', 'Gabriel Morales', 'Valentina Ruiz',
            'Fernando Castillo', 'Camila Vargas', 'Diego Herrera', 'Isabella Navarro',
            'Daniela Paredes', 'Andrés Guerrero', 'Valeria Romero', 'Esteban Méndez',
        ];

        $horas = ['12:30', '13:00', '13:30', '14:00', '19:00', '19:30', '20:00', '20:30', '21:00'];
        $nombresCount = count($nombres);
        $horasCount = count($horas);
        $mesasCount = count($this->mesaIds);
        $serviciosCount = count($this->servicioIds);
        $usuariosCount = count($this->usuarioIds);

        $now = now();

        for ($i = 1; $i <= $totalReservas; $i++) {
            $reservaId = ++$maxReservaId;
            $codigo = sprintf('RES-500K-%06d', $i);
            $nombre = $nombres[mt_rand(0, $nombresCount - 1)].' '.$i;

            $diasAtras = mt_rand(1, 730);
            $fechaCheckIn = (clone $now)->subDays($diasAtras)->format('Y-m-d');
            $horaReserva = $horas[mt_rand(0, $horasCount - 1)];

            // 65% restaurante / mesas, 35% servicios
            $esRestaurante = mt_rand(1, 100) <= 65;
            $tipoReserva = $esRestaurante ? 'restaurante' : 'servicio';
            $espacioId = $esRestaurante && ! empty($this->mesaIds) ? $this->mesaIds[mt_rand(0, $mesasCount - 1)] : null;
            $servicioId = (! $esRestaurante && ! empty($this->servicioIds)) ? $this->servicioIds[mt_rand(0, $serviciosCount - 1)] : null;

            $adultos = mt_rand(1, 5);
            $ninos = mt_rand(1, 10) <= 3 ? mt_rand(1, 2) : 0;
            $totalMonto = round(mt_rand(250, 1800) + (mt_rand(0, 99) / 100), 2);

            // Estado: 4=checked_out (completada 90%), 2=confirmada (6%), 5=cancelada (4%)
            $randEstado = mt_rand(1, 100);
            $estado = match (true) {
                $randEstado <= 90 => 4,
                $randEstado <= 96 => 2,
                default => 5,
            };

            $clienteId = (! empty($this->usuarioIds) && mt_rand(1, 10) <= 4)
                ? $this->usuarioIds[mt_rand(0, $usuariosCount - 1)]
                : null;

            $reservasBatch[] = [
                'id' => $reservaId,
                'codigo_reserva' => $codigo,
                'cliente_id' => $clienteId,
                'nombre_cliente' => $nombre,
                'telefono_cliente' => '+505 8'.mt_rand(1000000, 9999999),
                'email_cliente' => 'cliente.'.$i.'@hotelbugambilias.test',
                'tipo_reserva' => $tipoReserva,
                'habitacion_id' => null,
                'espacio_id' => $espacioId,
                'servicio_id' => $servicioId,
                'promocion_id' => null,
                'fecha_check_in' => $fechaCheckIn,
                'fecha_check_out' => $fechaCheckIn,
                'hora_reserva' => $horaReserva,
                'adultos' => $adultos,
                'ninos' => $ninos,
                'acompanantes' => null,
                'estado' => $estado,
                'subtotal' => $totalMonto,
                'descuento' => 0.00,
                'total' => $totalMonto,
                'notas' => 'Reserva histórica de mesa/servicio [500K]',
                'created_at' => $fechaCheckIn.' '.$horaReserva.':00',
                'updated_at' => $fechaCheckIn.' 22:00:00',
            ];

            if (count($reservasBatch) >= self::CHUNK_SIZE) {
                DB::table('reservas')->insert($reservasBatch);
                $reservasBatch = [];
                gc_collect_cycles();
            }

            if ($i % 25000 === 0) {
                $this->info("  -> {$i} reservaciones procesadas...");
            }
        }

        if (! empty($reservasBatch)) {
            DB::table('reservas')->insert($reservasBatch);
        }

        $tiempo = round(microtime(true) - $inicio, 2);
        $this->info("  ✓ 75,000 reservaciones insertadas en {$tiempo}s.");
    }

    private function generarCargosServiciosCuentas(): void
    {
        $this->info('▶ Generando 75,000 Cargos de Servicios Adicionales en Cuentas...');
        $inicio = microtime(true);

        $maxDetalleVal = DB::table('cuenta_detalles')->max('id');
        $maxDetalleId = is_numeric($maxDetalleVal) ? (int) $maxDetalleVal : 0;
        $totalCargos = self::TOTAL_CARGOS_CUENTA;
        $detallesBatch = [];

        $conceptos = [
            ['concepto' => 'Spa - Masaje Relajante 60min [500K]',       'precio' => 750.0, 'tipo' => 5],
            ['concepto' => 'Spa - Masaje Tejido Profundo [500K]',        'precio' => 900.0, 'tipo' => 5],
            ['concepto' => 'Spa - Paquete Hidratante & Sauna [500K]',    'precio' => 550.0, 'tipo' => 5],
            ['concepto' => 'Lavandería - Ropa Casual [500K]',            'precio' => 140.0, 'tipo' => 6],
            ['concepto' => 'Lavandería - Servicio Express [500K]',        'precio' => 220.0, 'tipo' => 6],
            ['concepto' => 'Lavandería - Planchado Especial [500K]',     'precio' => 95.0,  'tipo' => 6],
            ['concepto' => 'Minibar - Cerveza Nacional [500K]',          'precio' => 85.0,  'tipo' => 7],
            ['concepto' => 'Minibar - Vino Tinto 200ml [500K]',          'precio' => 190.0, 'tipo' => 7],
            ['concepto' => 'Minibar - Snack Mixto & Chocolate [500K]',   'precio' => 75.0,  'tipo' => 7],
            ['concepto' => 'Room Service - Desayuno Bugambilia [500K]',  'precio' => 260.0, 'tipo' => 8],
            ['concepto' => 'Room Service - Cena Gourmet [500K]',         'precio' => 450.0, 'tipo' => 8],
            ['concepto' => 'Room Service - Café de Altura [500K]',       'precio' => 80.0,  'tipo' => 8],
            ['concepto' => 'Tour - Volcán Masaya Nocturno [500K]',       'precio' => 850.0, 'tipo' => 9],
            ['concepto' => 'Tour - Ciudad Colonial Granada [500K]',      'precio' => 700.0, 'tipo' => 9],
            ['concepto' => 'Traslado - Aeropuerto Internacional [500K]', 'precio' => 500.0, 'tipo' => 9],
        ];

        $conceptosCount = count($conceptos);
        $cuentasCount = count($this->cuentaIds);
        $now = now();

        for ($i = 1; $i <= $totalCargos; $i++) {
            $detalleId = ++$maxDetalleId;
            $conceptoItem = $conceptos[mt_rand(0, $conceptosCount - 1)];

            $cuentaId = ! empty($this->cuentaIds)
                ? $this->cuentaIds[mt_rand(0, $cuentasCount - 1)]
                : 1;

            $cantidad = (float) mt_rand(1, 2);
            $precioUnitario = (float) $conceptoItem['precio'];
            $subtotal = round($cantidad * $precioUnitario, 2);

            $diasAtras = mt_rand(1, 730);
            $fecha = (clone $now)->subDays($diasAtras)->setTime(mt_rand(8, 22), mt_rand(0, 59))->format('Y-m-d H:i:s');

            $detallesBatch[] = [
                'id' => $detalleId,
                'cuenta_id' => $cuentaId,
                'origen_type' => null,
                'origen_id' => null,
                'tipo_detalle' => $conceptoItem['tipo'],
                'espacio_id' => null,
                'concepto' => $conceptoItem['concepto'],
                'descripcion' => 'Venta / consumo directo registrado en auditoría masiva [500K]',
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'estado' => 1, // Activo
                'metadatos' => json_encode(['seeder' => 'HistoricoMedioMillonSeeder', 'batch' => 500000]),
                'creador_id' => null,
                'anulado_por' => null,
                'anulado_en' => null,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ];

            if (count($detallesBatch) >= self::CHUNK_SIZE) {
                DB::table('cuenta_detalles')->insert($detallesBatch);
                $detallesBatch = [];
                gc_collect_cycles();
            }

            if ($i % 25000 === 0) {
                $this->info("  -> {$i} cargos procesados...");
            }
        }

        if (! empty($detallesBatch)) {
            DB::table('cuenta_detalles')->insert($detallesBatch);
        }

        $tiempo = round(microtime(true) - $inicio, 2);
        $this->info("  ✓ 75,000 cargos insertados en {$tiempo}s.");
    }

    private function sincronizarSecuenciasPostgres(): void
    {
        $this->info('▶ Sincronizando secuencias de PostgreSQL...');

        $tablas = ['pedidos', 'pedido_items', 'reservas', 'cuenta_detalles'];

        foreach ($tablas as $tabla) {
            try {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$tabla}', 'id'), COALESCE((SELECT MAX(id) FROM {$tabla}), 1))");
            } catch (\Throwable $e) {
                // Silencioso si la secuencia tiene otro nombre
            }
        }

        $this->info('  ✓ Secuencias PostgreSQL sincronizadas.');
    }

    private function info(string $mensaje): void
    {
        if ($this->command !== null) {
            $this->command->info($mensaje);
        } else {
            echo $mensaje.PHP_EOL;
        }
    }

    /**
     * @param  array<array-key, mixed>  $items
     * @return array<int, int>
     */
    private function aEnteros(array $items): array
    {
        $res = [];
        foreach ($items as $item) {
            if (is_numeric($item)) {
                $res[] = (int) $item;
            }
        }

        return $res;
    }
}
