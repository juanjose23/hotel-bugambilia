<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Cocina\MarcarItemPedidoListo;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosCocinaQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\CarbonInterface;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class CocinaPedidos extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-fire';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Cocina KDS';

    protected static ?string $title = 'Cocina - Panel KDS de Preparación';

    protected static ?string $slug = 'restaurante/cocina';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.restaurante.cocina-pedidos';

    /** @var Collection<int, Pedido> */
    public Collection $pedidos;

    public ?string $areaSeleccionada = null;

    public int $ultimoTotalPedidos = 0;

    private ObtenerPedidosCocinaQuery $pedidosQuery;

    private MarcarItemPedidoListo $marcarItemListo;

    private EnviarPedidoACocina $enviarACocina;

    public function boot(
        ObtenerPedidosCocinaQuery $pedidosQuery,
        MarcarItemPedidoListo $marcarItemListo,
        EnviarPedidoACocina $enviarACocina,
    ): void {
        $this->pedidosQuery = $pedidosQuery;
        $this->marcarItemListo = $marcarItemListo;
        $this->enviarACocina = $enviarACocina;
    }

    public function mount(): void
    {
        $this->cargarPedidos();
    }

    public function hydrate(): void
    {
        $this->cargarPedidos();
    }

    protected function getViewData(): array
    {
        $this->cargarPedidos();

        return [
            'pedidos' => $this->pedidos,
        ];
    }

    public function cargarPedidos(): void
    {
        $todos = $this->pedidosQuery->ejecutar();

        if ($this->areaSeleccionada !== null && $this->areaSeleccionada !== '') {
            $this->pedidos = $todos->map(function (Pedido $pedido) {
                $cloned = clone $pedido;
                $cloned->setRelation('items', $pedido->items->filter(function ($item) {
                    $itemArea = $item->area_cocina?->value;
                    $platoArea = $item->plato?->area_cocina?->value;

                    return $itemArea === $this->areaSeleccionada || $platoArea === $this->areaSeleccionada;
                }));

                return $cloned;
            })->filter(fn (Pedido $pedido) => $pedido->items->isNotEmpty())->values();
        } else {
            $this->pedidos = $todos;
        }

        $nuevoTotal = $this->pedidos->count();
        if ($nuevoTotal > $this->ultimoTotalPedidos && $this->ultimoTotalPedidos > 0) {
            $this->dispatch('nuevo-pedido-audio');
        }
        $this->ultimoTotalPedidos = $nuevoTotal;
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
            Notification::make()->title("Platillo listo: {$item->plato?->nombre}")->success()->send();
        }

        $this->cargarPedidos();
    }

    public function prepararAlimento(int $pedidoId): void
    {
        /** @var Pedido|null $pedido */
        $pedido = Pedido::query()->find($pedidoId);

        if ($pedido === null || $pedido->estado !== EstadoPedido::ABIERTO) {
            Notification::make()->title('Pedido no disponible')->body('Este pedido ya fue enviado a preparación.')->warning()->send();

            return;
        }

        try {
            $this->enviarACocina->ejecutar($pedido);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo iniciar preparación')->body($exception->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title('Preparación iniciada')->body("Pedido {$pedido->codigo} enviado a cocina.")->success()->send();

        $this->cargarPedidos();
    }

    public function tiempoTranscurrido(Pedido $pedido): string
    {
        return $pedido->created_at?->diffForHumans(null, CarbonInterface::DIFF_ABSOLUTE) ?? '';
    }

    protected function getPollingInterval(): string
    {
        return '6s';
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_CocinaPedidos') ?? false;
    }
}
