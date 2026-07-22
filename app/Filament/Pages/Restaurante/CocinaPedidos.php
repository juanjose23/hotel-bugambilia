<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\ConsumirIngredientesPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use BackedEnum;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class CocinaPedidos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-fire';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Cocina KDS';

    protected static ?string $title = 'Cocina - Panel de Preparacion';

    protected static ?string $slug = 'restaurante/cocina';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.cocina-pedidos';

    /** @var Collection<int, Pedido> */
    public Collection $pedidos;

    public function mount(): void
    {
        $this->cargarPedidos();
    }

    public function cargarPedidos(): void
    {
        $this->pedidos = Pedido::with(['items.plato', 'mesa'])
            ->whereIn('estado', ['abierto', 'preparacion'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function marcarItemListo(int $itemId): void
    {
        $item = PedidoItem::find($itemId);
        if (! $item instanceof PedidoItem) {
            return;
        }

        $item->update(['estado' => 'listo']);

        app(ConsumirIngredientesPedido::class)->ejecutar($item);

        $pedido = $item->pedido;
        if ($pedido instanceof Pedido) {
            $pendientes = $pedido->items()->where('estado', '!=', 'listo')->count();
            if ($pendientes === 0) {
                $pedido->update(['estado' => EstadoPedido::Listo->value]);
            } elseif ($pedido->estado === 'abierto') {
                $pedido->update(['estado' => EstadoPedido::Preparacion->value]);
            }
        }

        Notification::make()
            ->title("Item listo: {$item->plato?->nombre}")
            ->success()
            ->send();

        $this->cargarPedidos();
    }

    public function tiempoTranscurrido(Pedido $pedido): string
    {
        return $pedido->created_at?->diffForHumans(null, CarbonInterface::DIFF_ABSOLUTE) ?? '';
    }

    protected function getPollingInterval(): ?string
    {
        return '10s';
    }
}
