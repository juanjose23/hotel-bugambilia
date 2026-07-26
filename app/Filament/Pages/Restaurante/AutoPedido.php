<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Interactors\Restaurante\Pedidos\ConfirmarPedidoKiosko;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use BackedEnum;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class AutoPedido extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Kiosko Auto-Pedido';

    protected static ?string $title = 'Menú Interactivo & Auto-Pedido';

    protected static ?string $slug = 'restaurante/auto-pedido';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.restaurante.auto-pedido';

    public ?int $categoriaSeleccionadaId = null;

    public ?int $mesaId = null;

    public string $notas = '';

    /**
     * @var array<int, array{
     *     plato_id: int,
     *     nombre: string,
     *     precio: float,
     *     cantidad: int,
     *     observaciones: string,
     *     imagen_url: string|null
     * }>
     */
    public array $carrito = [];

    private RestauranteRepositorioInterface $repositorio;

    private ConfirmarPedidoKiosko $confirmarPedido;

    public function boot(
        RestauranteRepositorioInterface $repositorio,
        ConfirmarPedidoKiosko $confirmarPedido,
    ): void {
        $this->repositorio = $repositorio;
        $this->confirmarPedido = $confirmarPedido;
    }

    /**
     * @return Collection<int, Plato>
     */
    public function getPlatosProperty(): Collection
    {
        return $this->repositorio->obtenerPlatosActivos($this->categoriaSeleccionadaId);
    }

    /**
     * @return Collection<int, Espacio>
     */
    public function getMesasProperty(): Collection
    {
        return $this->repositorio->obtenerMesasDisponibles();
    }

    public function agregarAlCarrito(int $platoId): void
    {
        $plato = $this->repositorio->obtenerPlatoConPrecios($platoId);

        if (! $plato instanceof Plato) {
            return;
        }

        $precioObj = $plato->precios()->latest()->first();
        $precioUnitario = $precioObj !== null ? (float) $precioObj->precio : 0.0;
        $imagenUrl = $plato->imagenes()->first()?->url;

        if (isset($this->carrito[$platoId])) {
            $this->carrito[$platoId]['cantidad']++;
        } else {
            $this->carrito[$platoId] = [
                'plato_id' => $plato->id,
                'nombre' => $plato->nombre,
                'precio' => $precioUnitario,
                'cantidad' => 1,
                'observaciones' => '',
                'imagen_url' => $imagenUrl,
            ];
        }

        Notification::make()
            ->title("Añadido: {$plato->nombre}")
            ->success()
            ->send();
    }

    public function cambiarCantidad(int $platoId, int $delta): void
    {
        if (! isset($this->carrito[$platoId])) {
            return;
        }

        $nuevaCantidad = $this->carrito[$platoId]['cantidad'] + $delta;

        if ($nuevaCantidad <= 0) {
            unset($this->carrito[$platoId]);
        } else {
            $this->carrito[$platoId]['cantidad'] = $nuevaCantidad;
        }
    }

    public function eliminarDelCarrito(int $platoId): void
    {
        unset($this->carrito[$platoId]);
    }

    public function vaciarCarrito(): void
    {
        $this->carrito = [];
    }

    public function calcularSubtotal(): float
    {
        $subtotal = 0.0;
        foreach ($this->carrito as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }

        return round($subtotal, 2);
    }

    public function confirmarYEnviarPedido(): void
    {
        if (empty($this->carrito)) {
            Notification::make()->title('El carrito está vacío')->warning()->send();

            return;
        }

        $mesa = is_int($this->mesaId) ? $this->repositorio->obtenerMesaPorId($this->mesaId) : null;

        try {
            $this->confirmarPedido->ejecutar(
                items: array_values($this->carrito),
                mesa: $mesa,
                meseroId: auth()->id() !== null ? (int) auth()->id() : null,
                notas: $this->notas !== '' ? $this->notas : null,
            );

            $this->carrito = [];
            $this->notas = '';

            $this->dispatch('pedido-confirmado-audio');

            Notification::make()
                ->title('¡Pedido creado exitosamente!')
                ->body('Su orden ha sido registrada en la cocina.')
                ->success()
                ->send();

        } catch (DomainException $e) {
            Notification::make()->title('Error al procesar auto-pedido')->body($e->getMessage())->danger()->send();
        }
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_CocinaPedidos') || $user?->can('page_GestionMesas') || ($user?->hasRole('super_admin') ?? false);
    }
}
