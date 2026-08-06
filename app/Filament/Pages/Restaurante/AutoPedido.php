<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Cobro\CalcularSubtotalCarrito;
use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Interactors\Restaurante\Pedidos\ConfirmarPedidoKiosko;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Platos\ObtenerCategoriasMenuQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class AutoPedido extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Toma Rápida';

    protected static ?string $title = 'Toma Rápida de Pedido';

    protected static ?string $slug = 'restaurante/auto-pedido';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.resources.restaurante.auto-pedido';

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

    /**
     * @return Collection<int, Plato>
     */
    public function getPlatosProperty(RestauranteRepositorioInterface $repositorio): Collection
    {
        return $repositorio->obtenerPlatosActivos($this->categoriaSeleccionadaId);
    }

    /**
     * @return Collection<int, Espacio>
     */
    public function getMesasProperty(RestauranteRepositorioInterface $repositorio): Collection
    {
        return $repositorio->obtenerMesasDisponibles();
    }

    public function agregarAlCarrito(int $platoId, RestauranteRepositorioInterface $repositorio): void
    {
        $plato = $repositorio->obtenerPlatoConPrecios($platoId);

        if (! $plato instanceof Plato) {
            return;
        }

        $precioObj = $plato->precios->sortByDesc('created_at')->first();
        $precioUnitario = $precioObj !== null ? (float) $precioObj->precio : 0.0;
        $imagenUrl = $plato->imagenes->first()?->url;

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
            ->title("Añadido: $plato->nombre")
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
        return app(CalcularSubtotalCarrito::class)->calcular($this->carrito);
    }

    public function confirmarYEnviarPedido(
        RestauranteRepositorioInterface $repositorio,
        ConfirmarPedidoKiosko $confirmarPedido
    ): void {
        if (empty($this->carrito)) {
            Notification::make()->title('El carrito está vacío')->warning()->send();

            return;
        }

        $mesa = is_int($this->mesaId) ? $repositorio->obtenerMesaPorId($this->mesaId) : null;

        try {
            $confirmarPedido->ejecutar(
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
                ->body('La orden fue registrada para preparación.')
                ->success()
                ->send();

        } catch (DomainException $e) {
            Notification::make()->title('Error al tomar pedido')->body($e->getMessage())->danger()->send();
        }
    }

    /**
     * @return Collection<int, string>
     */
    public function getCategoriasProperty(ObtenerCategoriasMenuQuery $categoriasQuery): Collection
    {
        return $categoriasQuery->ejecutar();
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_AutoPedido') || $user?->can('page_CocinaPedidos') || $user?->can('page_GestionMesas') || ($user?->hasRole('super_admin') ?? false);
    }
}
