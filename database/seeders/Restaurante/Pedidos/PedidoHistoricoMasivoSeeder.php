<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Genera 3.000 pedidos históricos de restaurante distribuidos en los últimos 12 meses.
 *
 * Distribución:
 *   - 2.200 pedidos de mesas (almuerzo / cena)
 *   - 500  pedidos de bar (bebidas y cócteles)
 *   - 300  pedidos de eventos / banquetes (catering con 5-15 ítems)
 *
 * Inserta directamente via DB para máxima velocidad. No consume ingredientes
 * (ya existe el stock demo). Para auditoría use ReservaHistoricaSeeder.
 */
final class PedidoHistoricoMasivoSeeder extends Seeder
{
    private const TOTAL_MESAS = 2200;

    private const TOTAL_BAR = 500;

    private const TOTAL_EVENTOS = 300;

    private const CHUNK_SIZE = 200;

    /** @var array<int, array{id:int,precio:float}> */
    private array $platosCocina = [];

    /** @var array<int, array{id:int,precio:float}> */
    private array $platosBar = [];

    /** @var array<int, int> */
    private array $mesaIds = [];

    private ?int $meseroId = null;

    public function run(): void
    {
        mt_srand(20261201);

        $this->cargarDatos();

        if (empty($this->platosCocina) && empty($this->platosBar)) {
            $this->command->error('No hay platos registrados. Ejecute MenuRestauranteSeeder primero.');

            return;
        }

        // Limpiar ejecuciones previas para idempotencia
        $pedidosPreviosIds = DB::table('pedidos')->where('codigo', 'like', 'PDR-HST-%')->pluck('id');
        if ($pedidosPreviosIds->isNotEmpty()) {
            DB::table('pedido_items')->whereIn('pedido_id', $pedidosPreviosIds)->delete();
            DB::table('pedidos')->whereIn('id', $pedidosPreviosIds)->delete();
        }

        DB::transaction(function (): void {
            $this->command->info('Generando pedidos de mesa...');
            $this->generarPedidosMesa();

            $this->command->info('Generando pedidos de bar...');
            $this->generarPedidosBar();

            $this->command->info('Generando pedidos de eventos/banquetes...');
            $this->generarPedidosEventos();
        });

        $total = DB::table('pedidos')->where('codigo', 'like', 'PDR-HST-%')->count();
        $this->command->info("✓ Histórico de restaurante: {$total} pedidos creados exitosamente.");
    }

    private function cargarDatos(): void
    {
        // Platos de cocina (todos excepto los de bar)
        $this->platosCocina = Plato::query()
            ->where('estado', 1)
            ->whereDoesntHave('precios', fn ($q) => $q->where('precio', 0))
            ->with('precios')
            ->get()
            ->filter(fn (Plato $p) => $p->precios->first() !== null)
            ->map(fn (Plato $p) => [
                'id' => (int) $p->id,
                'precio' => (float) ($p->precios->first()->precio ?? 150.0),
            ])
            ->values()
            ->all();

        // Platos de bar (filtramos por precio bajo como proxy de bebidas)
        $this->platosBar = array_filter($this->platosCocina, fn ($p) => $p['precio'] <= 300.0);
        $this->platosBar = array_values($this->platosBar);

        // Si no hay distinción, usar todos como platos de bar también
        if (empty($this->platosBar)) {
            $this->platosBar = $this->platosCocina;
        }

        // Mesas del restaurante (MESA-01..MESA-12)
        $this->mesaIds = Espacio::query()
            ->where('codigo', 'like', 'MESA-%')
            ->pluck('id')
            ->map(fn ($id) => is_numeric($id) ? (int) $id : 0)
            ->all();

        $rawMeseroId = Colaborador::query()->value('id');
        $this->meseroId = is_numeric($rawMeseroId) ? (int) $rawMeseroId : null;
    }

    private function generarPedidosMesa(): void
    {
        $platos = $this->platosCocina;
        if (empty($platos)) {
            return;
        }

        $pedidosInsert = [];
        $itemsInsert = [];
        $consecutivo = $this->siguienteConsecutivo();

        for ($i = 0; $i < self::TOTAL_MESAS; $i++) {
            $fecha = $this->fechaAleatoria(365);
            $duracion = mt_rand(30, 90); // minutos
            $mesaId = empty($this->mesaIds) ? null : $this->mesaIds[mt_rand(0, count($this->mesaIds) - 1)];
            $codigo = sprintf('PDR-HST-MSA-%06d', $consecutivo++);

            $numItems = mt_rand(2, 5);
            $subtotal = 0.0;

            $pedidoId = $this->nextId();
            $pedidosInsert[] = [
                'id' => $pedidoId,
                'codigo' => $codigo,
                'mesa_id' => $mesaId,
                'mesero_id' => $this->meseroId,
                'cliente_id' => null,
                'cuenta_id' => null,
                'estado' => EstadoPedido::PAGADO->value,
                'subtotal' => 0, // se actualiza luego
                'total' => 0,
                'consecutivo_comanda' => $consecutivo,
                'abierto_en' => $fecha->format('Y-m-d H:i:s'),
                'cerrado_en' => $fecha->copy()->addMinutes($duracion)->format('Y-m-d H:i:s'),
                'cargado_en' => null,
                'notas' => null,
                'padre_pedido_id' => null,
                'created_at' => $fecha->format('Y-m-d H:i:s'),
                'updated_at' => $fecha->copy()->addMinutes($duracion)->format('Y-m-d H:i:s'),
            ];

            for ($j = 0; $j < $numItems; $j++) {
                $plato = $platos[mt_rand(0, count($platos) - 1)];
                $cantidad = mt_rand(1, 3);
                $precio = $plato['precio'];
                $sub = round($precio * $cantidad, 2);
                $subtotal += $sub;

                $itemsInsert[] = [
                    'pedido_id' => $pedidoId,
                    'plato_id' => $plato['id'],
                    'area_cocina' => 'cocina',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $sub,
                    'estado' => EstadoItemPedido::SERVIDO->value,
                    'notas' => null,
                    'created_at' => $fecha->format('Y-m-d H:i:s'),
                    'updated_at' => $fecha->format('Y-m-d H:i:s'),
                ];
            }

            // Actualizar subtotal y total al último elemento del pedido
            $pedidosInsert[count($pedidosInsert) - 1]['subtotal'] = $subtotal;
            $pedidosInsert[count($pedidosInsert) - 1]['total'] = $subtotal;

            if (count($pedidosInsert) >= self::CHUNK_SIZE) {
                $this->insertarChunk($pedidosInsert, $itemsInsert);
                $pedidosInsert = [];
                $itemsInsert = [];
            }
        }

        if (! empty($pedidosInsert)) {
            $this->insertarChunk($pedidosInsert, $itemsInsert);
        }
    }

    private function generarPedidosBar(): void
    {
        $platos = $this->platosBar;
        if (empty($platos)) {
            $platos = $this->platosCocina;
        }
        if (empty($platos)) {
            return;
        }

        $pedidosInsert = [];
        $itemsInsert = [];
        $consecutivo = $this->siguienteConsecutivo();

        for ($i = 0; $i < self::TOTAL_BAR; $i++) {
            // Bar tiene mayor afluencia nocturna
            $hora = mt_rand(0, 10) < 7 ? mt_rand(18, 23) : mt_rand(12, 17);
            $fecha = $this->fechaAleatoria(365)->setHour($hora)->setMinute(mt_rand(0, 59));

            $codigo = sprintf('PDR-HST-BAR-%06d', $consecutivo++);
            $numItems = mt_rand(1, 4);
            $subtotal = 0.0;
            $pedidoId = $this->nextId();

            $pedidosInsert[] = [
                'id' => $pedidoId,
                'codigo' => $codigo,
                'mesa_id' => empty($this->mesaIds) ? null : $this->mesaIds[mt_rand(0, count($this->mesaIds) - 1)],
                'mesero_id' => $this->meseroId,
                'cliente_id' => null,
                'cuenta_id' => null,
                'estado' => EstadoPedido::PAGADO->value,
                'subtotal' => 0,
                'total' => 0,
                'consecutivo_comanda' => $consecutivo,
                'abierto_en' => $fecha->format('Y-m-d H:i:s'),
                'cerrado_en' => $fecha->copy()->addMinutes(mt_rand(10, 35))->format('Y-m-d H:i:s'),
                'cargado_en' => null,
                'notas' => null,
                'padre_pedido_id' => null,
                'created_at' => $fecha->format('Y-m-d H:i:s'),
                'updated_at' => $fecha->format('Y-m-d H:i:s'),
            ];

            for ($j = 0; $j < $numItems; $j++) {
                $plato = $platos[mt_rand(0, count($platos) - 1)];
                $cantidad = mt_rand(1, 2);
                $precio = $plato['precio'];
                $sub = round($precio * $cantidad, 2);
                $subtotal += $sub;

                $itemsInsert[] = [
                    'pedido_id' => $pedidoId,
                    'plato_id' => $plato['id'],
                    'area_cocina' => 'bar',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $sub,
                    'estado' => EstadoItemPedido::SERVIDO->value,
                    'notas' => null,
                    'created_at' => $fecha->format('Y-m-d H:i:s'),
                    'updated_at' => $fecha->format('Y-m-d H:i:s'),
                ];
            }

            $pedidosInsert[count($pedidosInsert) - 1]['subtotal'] = $subtotal;
            $pedidosInsert[count($pedidosInsert) - 1]['total'] = $subtotal;

            if (count($pedidosInsert) >= self::CHUNK_SIZE) {
                $this->insertarChunk($pedidosInsert, $itemsInsert);
                $pedidosInsert = [];
                $itemsInsert = [];
            }
        }

        if (! empty($pedidosInsert)) {
            $this->insertarChunk($pedidosInsert, $itemsInsert);
        }
    }

    private function generarPedidosEventos(): void
    {
        $platos = $this->platosCocina;
        if (empty($platos)) {
            return;
        }

        $pedidosInsert = [];
        $itemsInsert = [];
        $consecutivo = $this->siguienteConsecutivo();

        for ($i = 0; $i < self::TOTAL_EVENTOS; $i++) {
            // Eventos suelen ser fines de semana y al mediodía
            $fecha = $this->fechaAleatoria(365, preferirFinDeSemana: true)->setHour(mt_rand(12, 21));
            $duracion = mt_rand(90, 180);
            $codigo = sprintf('PDR-HST-EVT-%06d', $consecutivo++);
            $numItems = mt_rand(5, 15);
            $subtotal = 0.0;
            $pedidoId = $this->nextId();

            $pedidosInsert[] = [
                'id' => $pedidoId,
                'codigo' => $codigo,
                'mesa_id' => null, // Eventos no tienen mesa fija
                'mesero_id' => $this->meseroId,
                'cliente_id' => null,
                'cuenta_id' => null,
                'estado' => EstadoPedido::PAGADO->value,
                'subtotal' => 0,
                'total' => 0,
                'consecutivo_comanda' => $consecutivo,
                'abierto_en' => $fecha->format('Y-m-d H:i:s'),
                'cerrado_en' => $fecha->copy()->addMinutes($duracion)->format('Y-m-d H:i:s'),
                'cargado_en' => null,
                'notas' => 'Pedido de catering / evento corporativo.',
                'padre_pedido_id' => null,
                'created_at' => $fecha->format('Y-m-d H:i:s'),
                'updated_at' => $fecha->copy()->addMinutes($duracion)->format('Y-m-d H:i:s'),
            ];

            for ($j = 0; $j < $numItems; $j++) {
                $plato = $platos[mt_rand(0, count($platos) - 1)];
                $cantidad = mt_rand(2, 10); // mayor cantidad en eventos
                $precio = $plato['precio'];
                $sub = round($precio * $cantidad, 2);
                $subtotal += $sub;

                $itemsInsert[] = [
                    'pedido_id' => $pedidoId,
                    'plato_id' => $plato['id'],
                    'area_cocina' => 'cocina',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $sub,
                    'estado' => EstadoItemPedido::SERVIDO->value,
                    'notas' => null,
                    'created_at' => $fecha->format('Y-m-d H:i:s'),
                    'updated_at' => $fecha->format('Y-m-d H:i:s'),
                ];
            }

            $pedidosInsert[count($pedidosInsert) - 1]['subtotal'] = $subtotal;
            $pedidosInsert[count($pedidosInsert) - 1]['total'] = $subtotal;

            if (count($pedidosInsert) >= self::CHUNK_SIZE) {
                $this->insertarChunk($pedidosInsert, $itemsInsert);
                $pedidosInsert = [];
                $itemsInsert = [];
            }
        }

        if (! empty($pedidosInsert)) {
            $this->insertarChunk($pedidosInsert, $itemsInsert);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $pedidos
     * @param  array<int, array<string, mixed>>  $items
     */
    private function insertarChunk(array $pedidos, array $items): void
    {
        DB::table('pedidos')->insert($pedidos);
        if (! empty($items)) {
            DB::table('pedido_items')->insert($items);
        }
    }

    /**
     * Genera una fecha aleatoria dentro de los últimos N días.
     * Si preferirFinDeSemana es true, el 60% de las fechas caerán en sáb/dom.
     */
    private function fechaAleatoria(int $diasAtras, bool $preferirFinDeSemana = false): Carbon
    {
        if ($preferirFinDeSemana && mt_rand(0, 9) < 6) {
            // Generar fecha en fin de semana más cercano
            $dias = mt_rand(0, (int) ($diasAtras / 7)) * 7;
            $base = Carbon::now()->subDays($dias);
            $diasFds = $base->dayOfWeek === Carbon::SATURDAY ? 0 : ($base->dayOfWeek === Carbon::SUNDAY ? 1 : (7 - $base->dayOfWeek));

            return $base->addDays($diasFds % 7)->setHour(mt_rand(8, 22))->setMinute(mt_rand(0, 59));
        }

        return Carbon::now()
            ->subDays(mt_rand(1, $diasAtras))
            ->setHour(mt_rand(7, 22))
            ->setMinute(mt_rand(0, 59))
            ->setSecond(0);
    }

    /**
     * Obtiene el siguiente ID disponible para la tabla pedidos.
     * Se usa una secuencia manual para poder pre-asignar IDs en batch inserts.
     */
    private static int $currentId = 0;

    private function nextId(): int
    {
        if (self::$currentId === 0) {
            $max = DB::table('pedidos')->max('id');
            self::$currentId = (is_numeric($max) ? (int) $max : 0) + 1;
        }

        return self::$currentId++;
    }

    private function siguienteConsecutivo(): int
    {
        $max = DB::table('pedidos')->max('consecutivo_comanda');

        return (is_numeric($max) ? (int) $max : 0) + 1;
    }
}
