<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Interactors\Restaurante\MarcarItemPedidoListo;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\ObtenerPedidosCocinaQuery;
use BackedEnum;
use Carbon\CarbonInterface;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class CocinaPedidos extends Page
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

    private ObtenerPedidosCocinaQuery $pedidosQuery;

    private MarcarItemPedidoListo $marcarItemListo;

    public function boot(ObtenerPedidosCocinaQuery $pedidosQuery, MarcarItemPedidoListo $marcarItemListo): void
    {
        $this->pedidosQuery = $pedidosQuery;
        $this->marcarItemListo = $marcarItemListo;
    }

    public function mount(): void
    {
        $this->cargarPedidos();
    }

    public function cargarPedidos(): void
    {
        $this->pedidos = $this->pedidosQuery->ejecutar();
    }

    public function marcarItemListo(int $itemId): void
    {
        try {
            $item = $this->marcarItemListo->ejecutar($itemId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo completar el plato')->body($exception->getMessage())->danger()->send();

            return;
        }

        if ($item !== null) {
            Notification::make()->title("Item listo: {$item->plato?->nombre}")->success()->send();
        }

        $this->cargarPedidos();
    }

    public function tiempoTranscurrido(Pedido $pedido): string
    {
        return $pedido->created_at?->diffForHumans(null, CarbonInterface::DIFF_ABSOLUTE) ?? '';
    }

    protected function getPollingInterval(): string
    {
        return '10s';
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_CocinaPedidos') ?? false;
    }
}
