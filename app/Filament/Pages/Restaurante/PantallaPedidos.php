<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Interactors\Restaurante\Cocina\AnularItemPedido;
use App\Interactors\Restaurante\Cocina\MarcarItemPedidoListo;
use App\Interactors\Restaurante\Cocina\MarcarItemServido;
use App\Interactors\Restaurante\Cuentas\AbrirCuentaYConsumoRestaurante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosPantallaQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\CarbonInterface;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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

    /** @var array<int, int> */
    public array $pedidoListosNotificados = [];

    public bool $pantallaInicializada = false;

    private ObtenerPedidosPantallaQuery $pantallaQuery;

    private MarcarItemServido $marcarItemServido;

    private MarcarItemPedidoListo $marcarItemListo;

    private AnularItemPedido $anularItemPedido;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function boot(
        ObtenerPedidosPantallaQuery $pantallaQuery,
        MarcarItemServido $marcarItemServido,
        MarcarItemPedidoListo $marcarItemListo,
        AnularItemPedido $anularItemPedido,
    ): void {
        $this->pantallaQuery = $pantallaQuery;
        $this->marcarItemServido = $marcarItemServido;
        $this->marcarItemListo = $marcarItemListo;
        $this->anularItemPedido = $anularItemPedido;
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

        $idsListos = $this->pedidosListos
            ->pluck('id')
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->values()
            ->all();

        if ($this->pantallaInicializada && $idsListos !== []) {
            $mesas = $this->pedidosListos
                ->map(fn (Pedido $pedido): string => is_string($pedido->mesa?->nombre) && trim($pedido->mesa->nombre) !== ''
                    ? trim($pedido->mesa->nombre)
                    : 'sin mesa')
                ->unique()
                ->values()
                ->implode(', ');

            $this->dispatch('pedido-listo-audio', mesa: $mesas !== '' ? $mesas : 'sin mesa');
        }

        $this->pedidoListosNotificados = $idsListos;
        $this->ultimoTotalListos = count($idsListos);
        $this->pantallaInicializada = true;
    }

    public function tiempoTranscurrido(Pedido $pedido): string
    {
        return $pedido->created_at?->diffForHumans(null, CarbonInterface::DIFF_ABSOLUTE) ?? '0m';
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

    public function marcarItemServido(int $itemId): void
    {
        try {
            $item = $this->marcarItemServido->ejecutar($itemId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo marcar como servido')->body($exception->getMessage())->danger()->send();

            return;
        }

        if ($item !== null) {
            Notification::make()->title("Platillo servido: {$item->plato?->nombre}")->success()->send();
        }

        $this->cargarPedidos();
    }

    public function anularItemPedido(int $itemId): void
    {
        try {
            $item = $this->anularItemPedido->ejecutar($itemId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo anular el plato')->body($exception->getMessage())->danger()->send();

            return;
        }

        if ($item !== null) {
            Notification::make()->title("Platillo anulado: {$item->plato?->nombre}")->warning()->send();
        }

        $this->cargarPedidos();
    }

    public ?int $pedidoCobroId = null;

    // ────────────────────────────────────────────
    // Cobro / Pago — Acción Unificada CobrarCuentaAction
    // ────────────────────────────────────────────

    public function cobrarCuentaAction(): Action
    {
        return CobrarCuentaAction::makeFromResolver(
            resolverCuenta: function (): ?Cuenta {
                if (! is_numeric($this->pedidoCobroId)) {
                    return null;
                }

                $pedido = Pedido::find((int) $this->pedidoCobroId);

                if ($pedido === null) {
                    return null;
                }

                if ($pedido->cuenta_id !== null && $pedido->cuenta !== null && $pedido->cuenta->estaAbierta()) {
                    return $pedido->cuenta;
                }

                $userId = auth()->id() !== null ? (int) auth()->id() : null;
                $resultado = app(AbrirCuentaYConsumoRestaurante::class)->ejecutar($pedido, $userId);

                return $resultado['cuenta'];
            },
            onSuccess: function (): void {
                $this->cargarPedidos();
            }
        )->name('cobrarCuenta');
    }

    public function iniciarCobroPedido(int $pedidoId): void
    {
        $this->pedidoCobroId = $pedidoId;
        $this->mountAction('cobrarCuenta');
    }

    protected function getPollingInterval(): string
    {
        return '4s';
    }

    public static function canAccess(): bool
    {
        return app(VerificarRestauranteActivo::class)->estaActivo();
    }
}
