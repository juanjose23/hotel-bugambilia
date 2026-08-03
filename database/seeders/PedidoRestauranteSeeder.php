<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PedidoRestauranteSeeder extends Seeder
{
    public function run(): void
    {
        $mesas = Espacio::where('tipo', 'mesa')->get();
        $platos = Plato::with('precios')->get();
        $mesero = Colaborador::first() ?? Colaborador::create(['persona_id' => 1, 'codigo' => 'COL-001', 'estado' => 1]);

        $cliente = Persona::first();
        if (! $cliente) {
            $cliente = Persona::create([
                'primer_nombre' => 'María',
                'segundo_nombre' => 'Sánchez',
                'tipo_persona' => 'natural',
                'telefono' => '+505 8888 8888',
            ]);
            PersonaNatural::create([
                'persona_id' => $cliente->id,
                'primer_nombre' => 'María',
                'primer_apellido' => 'Sánchez',
            ]);
        }

        if ($platos->isEmpty()) {
            $this->command->error('No hay platos registrados. Por favor ejecute MenuRestauranteSeeder primero.');

            return;
        }

        // Generar 20 pedidos distribuidos en los últimos 15 días
        for ($i = 1; $i <= 20; $i++) {
            $fecha = Carbon::now()->subDays(rand(0, 15))->subHours(rand(1, 12));
            $codigo = 'PED-'.$fecha->format('Ymd').'-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $estado = $i <= 3 ? EstadoPedido::ABIERTO : ($i <= 5 ? EstadoPedido::EN_PREPARACION : EstadoPedido::PAGADO);

            $mesa = $mesas->random();

            $pedido = Pedido::create([
                'codigo' => $codigo,
                'mesa_id' => $mesa->id,
                'mesero_id' => $mesero->id,
                'cliente_id' => $cliente->id,
                'estado' => $estado,
                'subtotal' => 0.00,
                'abierto_en' => $fecha,
                'cerrado_en' => $estado === EstadoPedido::PAGADO ? $fecha->addMinutes(rand(30, 90)) : null,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            $numItems = rand(1, 4);
            $totalPedido = 0;

            for ($j = 0; $j < $numItems; $j++) {
                $plato = $platos->random();
                $precioObj = $plato->precios->first();
                $rawPrecio = $precioObj?->getAttribute('precio');
                $precio = is_numeric($rawPrecio) ? (float) $rawPrecio : (float) rand(120, 380);
                $cantidad = rand(1, 3);
                $subtotal = round($precio * $cantidad, 2);
                $totalPedido += $subtotal;

                $itemEstado = match ($estado) {
                    EstadoPedido::PAGADO => EstadoItemPedido::SERVIDO,
                    EstadoPedido::EN_PREPARACION => EstadoItemPedido::EN_PREPARACION,
                    default => EstadoItemPedido::PENDIENTE,
                };

                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'plato_id' => $plato->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                    'estado' => $itemEstado,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);
            }

            $pedido->update(['subtotal' => $totalPedido]);
        }

        $this->command->info('Módulo de Restaurante: 20 pedidos demo y sus detalles han sido creados correctamente.');
    }
}
