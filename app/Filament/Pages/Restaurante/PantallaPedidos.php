<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosPantallaQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class PantallaPedidos extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tv';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Pantalla Turnos';

    protected static ?string $title = 'Pantalla de Pedidos - Comedor y Despacho';

    protected static ?string $slug = 'restaurante/pantalla-pedidos';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.resources.restaurante.pantalla-pedidos';

    /** @var Collection<int, Pedido> */
    public Collection $pedidosEnPreparacion;

    /** @var Collection<int, Pedido> */
    public Collection $pedidosListos;

    /** @var Collection<int, Pedido> */
    public Collection $pedidos;

    public int $ultimoTotalListos = 0;

    private ObtenerPedidosPantallaQuery $pantallaQuery;

    public function boot(ObtenerPedidosPantallaQuery $pantallaQuery): void
    {
        $this->pantallaQuery = $pantallaQuery;
    }

    public function mount(): void
    {
        $this->cargarPedidos();
    }

    public function cargarPedidos(): void
    {
        $datos = $this->pantallaQuery->ejecutar();

        $this->pedidosEnPreparacion = $datos['enPreparacion'];
        $this->pedidosListos = $datos['listos'];
        $this->pedidos = $this->pedidosEnPreparacion->merge($this->pedidosListos);

        $nuevoTotalListos = $this->pedidosListos->count();

        if ($nuevoTotalListos > $this->ultimoTotalListos && $this->ultimoTotalListos > 0) {
            $this->dispatch('pedido-listo-audio');
        }

        $this->ultimoTotalListos = $nuevoTotalListos;
    }

    public function tiempoTranscurrido(Pedido $pedido): string
    {
        return $pedido->created_at?->diffForHumans(null, CarbonInterface::DIFF_ABSOLUTE) ?? '0m';
    }

    protected function getPollingInterval(): string
    {
        return '4s';
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
