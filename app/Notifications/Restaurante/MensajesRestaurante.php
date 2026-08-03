<?php

declare(strict_types=1);

namespace App\Notifications\Restaurante;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Restaurante\Pedido;
use Filament\Actions\Action;

final class MensajesRestaurante
{
    public function __construct(
        private readonly UrlNotificador $url,
    ) {}

    public function pedidoEnviadoACocina(Pedido $pedido): DatosNotificacion
    {
        $pedido->loadMissing(['items', 'mesa']);

        $mesa = $pedido->mesa instanceof Espacio ? $pedido->mesa->nombre : 'Sin mesa';
        $items = $pedido->items->count();

        return new DatosNotificacion(
            title: 'Comanda Enviada a Cocina',
            body: "Comanda {$pedido->codigo} (Mesa {$mesa}) con {$items} platillo(s) enviada a preparación.",
            type: TipoNotificacion::Info,
            actions: [
                Action::make('verPedido')
                    ->label('Ver Comanda')
                    ->url($this->url->pedido($pedido))
                    ->button(),
                Action::make('verCocina')
                    ->label('Ir a Cocina KDS')
                    ->url($this->url->cocinaPedidos())
                    ->button(),
            ],
        );
    }

    public function pedidoCancelado(Pedido $pedido): DatosNotificacion
    {
        $pedido->loadMissing('mesa');

        $mesa = $pedido->mesa instanceof Espacio ? $pedido->mesa->nombre : 'Sin mesa';

        return new DatosNotificacion(
            title: 'Comanda Cancelada',
            body: "La comanda {$pedido->codigo} (Mesa {$mesa}) ha sido cancelada.",
            type: TipoNotificacion::Error,
            actions: [
                Action::make('verPedido')
                    ->label('Ver Comanda')
                    ->url($this->url->pedido($pedido))
                    ->button(),
            ],
        );
    }

    public function pedidoCargadoACuenta(Pedido $pedido, Cuenta $cuenta): DatosNotificacion
    {
        $pedido->loadMissing('mesa');
        $cuenta->loadMissing('estancia.habitacion');

        $mesa = $pedido->mesa instanceof Espacio ? $pedido->mesa->nombre : 'Sin mesa';
        $hab = $cuenta->estancia instanceof Estancia ? ($cuenta->estancia->habitacion !== null ? $cuenta->estancia->habitacion->nombre : 'Habitación') : 'Habitación';

        return new DatosNotificacion(
            title: 'Comanda Cargada a Habitación',
            body: "Comanda {$pedido->codigo} (Mesa {$mesa}) cargada a Cuenta #{$cuenta->numero_cuenta} ({$hab}).",
            type: TipoNotificacion::Success,
            actions: [
                Action::make('verPedido')
                    ->label('Ver Comanda')
                    ->url($this->url->pedido($pedido))
                    ->button(),
            ],
        );
    }

    public function stockMinimoRestaurante(string $producto, float $cantidad, string $ubicacion): DatosNotificacion
    {
        return new DatosNotificacion(
            title: 'Stock Mínimo - Restaurante',
            body: "El producto \"{$producto}\" tiene solo {$cantidad} unidades en \"{$ubicacion}\".",
            type: TipoNotificacion::StockLow,
            actions: [
                Action::make('verInventario')
                    ->label('Ver Inventario')
                    ->url('/admin/inventario')
                    ->button(),
            ],
        );
    }

    public function mesaUnida(string $mesaPrincipal, string $mesasSecundarias): DatosNotificacion
    {
        return new DatosNotificacion(
            title: 'Mesas Unidas',
            body: "Mesa principal \"{$mesaPrincipal}\" unida con: {$mesasSecundarias}.",
            type: TipoNotificacion::Info,
            actions: [
                Action::make('verMesas')
                    ->label('Ver Gestión de Mesas')
                    ->url($this->url->gestionMesas())
                    ->button(),
            ],
        );
    }

    public function mesaSeparada(string $mesa): DatosNotificacion
    {
        return new DatosNotificacion(
            title: 'Mesa Separada',
            body: "La mesa \"{$mesa}\" ha sido desvinculada de su grupo.",
            type: TipoNotificacion::Info,
            actions: [
                Action::make('verMesas')
                    ->label('Ver Gestión de Mesas')
                    ->url($this->url->gestionMesas())
                    ->button(),
            ],
        );
    }
}
