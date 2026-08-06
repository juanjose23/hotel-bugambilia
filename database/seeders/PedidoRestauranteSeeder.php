<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PedidoRestauranteSeeder extends Seeder
{
    private const CODIGO_PREFIX = 'PDR-DEMO-';

    public function run(): void
    {
        $platos = Plato::query()->with('precios')->where('estado', 1)->get();

        if ($platos->isEmpty()) {
            $this->command->error('No hay platos registrados. Ejecute MenuRestauranteSeeder antes de PedidoRestauranteSeeder.');

            return;
        }

        DB::transaction(function () use ($platos): void {
            $this->limpiarPedidosDemo();

            $mesero = Colaborador::query()->first();
            $cliente = $this->clienteDemo();
            $mesas = $this->mesasDemo();

            if ($mesas->count() < 3) {
                $this->command->warn('No hay suficientes mesas demo para crear la secuencia completa de pedidos.');

                return;
            }

            $this->prepararMesas($mesas);

            $this->crearPedidoAbierto($mesas->get('MESA-01'), $mesero, $cliente, $platos);
            $this->crearPedidoEnPreparacion($mesas->get('MESA-02'), $mesero, $cliente, $platos);
            $this->crearPedidoHistoricoPagado($mesas->get('MESA-09') ?? $mesas->get('MESA-03'), $mesero, $cliente, $platos);
        });

        $this->command->info('Restaurante: pedidos demo creados con secuencia lógica abierta, preparación e histórico pagado.');
    }

    private function limpiarPedidosDemo(): void
    {
        $pedidoIds = Pedido::query()
            ->where('codigo', 'like', self::CODIGO_PREFIX.'%')
            ->pluck('id');

        if ($pedidoIds->isEmpty()) {
            return;
        }

        PedidoItem::query()->whereIn('pedido_id', $pedidoIds)->delete();
        Pedido::query()->whereIn('id', $pedidoIds)->forceDelete();
    }

    private function clienteDemo(): Persona
    {
        /** @var Persona|null $cliente */
        $cliente = Persona::query()->where('telefono', '+505 8888 8888')->first();

        if ($cliente instanceof Persona) {
            return $cliente;
        }

        /** @var Persona $persona */
        $persona = Persona::query()->create([
            'primer_nombre' => 'María',
            'segundo_nombre' => 'Sánchez',
            'tipo_persona' => 'natural',
            'telefono' => '+505 8888 8888',
        ]);

        PersonaNatural::query()->create([
            'persona_id' => $persona->id,
            'primer_apellido' => 'Sánchez',
        ]);

        return $persona;
    }

    /**
     * @return Collection<string, Espacio>
     */
    private function mesasDemo()
    {
        return Espacio::query()
            ->whereIn('codigo', ['MESA-01', 'MESA-02', 'MESA-03', 'MESA-09'])
            ->get()
            ->keyBy('codigo');
    }

    /**
     * @param  Collection<string, Espacio>  $mesas
     */
    private function prepararMesas($mesas): void
    {
        foreach ($mesas as $mesa) {
            $tienePedidoNoDemo = $mesa->pedidosActivos()
                ->where('codigo', 'not like', self::CODIGO_PREFIX.'%')
                ->exists();

            if ($tienePedidoNoDemo) {
                continue;
            }

            $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
            unset($meta['mesa_principal_id'], $meta['mesa_principal_nombre'], $meta['mesas_unidas'], $meta['motivo_union']);

            $mesa->forceFill([
                'estado' => EstadoEspacio::Disponible,
                'meta_datos' => $meta,
            ])->save();
        }
    }

    /** @param Collection<int, Plato> $platos */
    private function crearPedidoAbierto(?Espacio $mesa, ?Colaborador $mesero, Persona $cliente, Collection $platos): void
    {
        if (! $mesa instanceof Espacio) {
            return;
        }

        $pedido = app(AbrirPedidoMesa::class)->ejecutar(
            mesa: $mesa,
            meseroId: $mesero?->id,
            clienteId: $cliente->id,
            notas: 'Demo: pedido abierto pendiente de envío a cocina.',
        );

        $pedido->codigo = self::CODIGO_PREFIX.'ABIERTO';
        $pedido->abierto_en = Carbon::now()->subMinutes(12);
        $pedido->save();

        $this->agregarItems($pedido, $platos->take(2)->values()->all());
        $pedido->subtotal = $pedido->calcularSubtotal();
        $pedido->save();
    }

    /** @param Collection<int, Plato> $platos */
    private function crearPedidoEnPreparacion(?Espacio $mesa, ?Colaborador $mesero, Persona $cliente, Collection $platos): void
    {
        if (! $mesa instanceof Espacio) {
            return;
        }

        $pedido = app(AbrirPedidoMesa::class)->ejecutar(
            mesa: $mesa,
            meseroId: $mesero?->id,
            clienteId: $cliente->id,
            notas: 'Demo: pedido enviado a cocina con consumo de ingredientes.',
        );

        $pedido->codigo = self::CODIGO_PREFIX.'COCINA';
        $pedido->abierto_en = Carbon::now()->subMinutes(25);
        $pedido->save();

        $this->agregarItems($pedido, $platos->skip(2)->take(2)->values()->all());
        $pedido->subtotal = $pedido->calcularSubtotal();
        $pedido->save();

        app(EnviarPedidoACocina::class)->ejecutar($pedido->refresh());
    }

    /** @param Collection<int, Plato> $platos */
    private function crearPedidoHistoricoPagado(?Espacio $mesa, ?Colaborador $mesero, Persona $cliente, Collection $platos): void
    {
        $fecha = Carbon::now()->subDays(2)->setTime(19, 30);

        /** @var Pedido $pedido */
        $pedido = Pedido::query()->create([
            'codigo' => self::CODIGO_PREFIX.'PAGADO',
            'mesa_id' => $mesa?->id,
            'mesero_id' => $mesero?->id,
            'cliente_id' => $cliente->id,
            'estado' => EstadoPedido::PAGADO,
            'subtotal' => 0,
            'abierto_en' => $fecha,
            'cerrado_en' => $fecha->copy()->addMinutes(58),
            'created_at' => $fecha,
            'updated_at' => $fecha,
            'notas' => 'Demo: pedido histórico pagado, no ocupa la mesa.',
        ]);

        $this->agregarItems($pedido, $platos->skip(4)->take(3)->values()->all(), EstadoItemPedido::SERVIDO, $fecha);
        $pedido->subtotal = $pedido->calcularSubtotal();
        $pedido->save();
    }

    /**
     * @param  array<int, Plato>  $platos
     */
    private function agregarItems(
        Pedido $pedido,
        array $platos,
        EstadoItemPedido $estado = EstadoItemPedido::PENDIENTE,
        ?Carbon $fecha = null,
    ): void {
        foreach ($platos as $index => $plato) {

            $precioObj = $plato->precios->first();
            $rawPrecio = $precioObj?->getAttribute('precio');
            $precio = is_numeric($rawPrecio) ? (float) $rawPrecio : 150.0;
            $cantidad = $index === 0 ? 2 : 1;

            PedidoItem::query()->create([
                'pedido_id' => $pedido->id,
                'plato_id' => $plato->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($precio * $cantidad, 2),
                'estado' => $estado,
                'created_at' => $fecha ?? now(),
                'updated_at' => $fecha ?? now(),
            ]);
        }
    }
}
