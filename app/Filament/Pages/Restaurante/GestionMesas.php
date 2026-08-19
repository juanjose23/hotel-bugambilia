<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Interactors\Limpieza\Ejecucion\RegistrarSolicitudLimpieza;
use App\Interactors\Restaurante\Cocina\ReimprimirComanda;
use App\Interactors\Restaurante\Cuentas\AbrirCuentaYConsumoRestaurante;
use App\Interactors\Restaurante\Cuentas\AplicarDescuentoCuenta;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Interactors\Restaurante\Mesas\CancelarReservaMesa;
use App\Interactors\Restaurante\Mesas\ConfirmarLlegadaReservaMesa;
use App\Interactors\Restaurante\Mesas\MoverCuentaMesa;
use App\Interactors\Restaurante\Mesas\SepararMesas;
use App\Interactors\Restaurante\Mesas\UnirMesas;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Landing\ObtenerReservasRestauranteQuery;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMapaMesasQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerComandasMesaConDetalleQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Throwable;
use UnitEnum;

final class GestionMesas extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'hugeicons-restaurant-table';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Gestión de Mesas';

    protected static ?string $title = 'Mapa de Mesas';

    protected static ?string $slug = 'restaurante/mesas';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.resources.restaurante.gestion-mesas';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crearReserva')
                ->label('Reservar Mesa')
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->url(fn (): string => ReservaResource::getUrl('create', ['tipo_reserva' => TipoReserva::RESTAURANTE->value])),

            Action::make('pantallaTurnos')
                ->label('Pantalla Turnos')
                ->icon('heroicon-o-tv')
                ->color('info')
                ->url(fn (): string => PantallaPedidos::getUrl())
                ->openUrlInNewTab(),
        ];
    }

    // ── Mapa de mesas ──
    /** @var Collection<int, Espacio> */
    public Collection $ambientes;

    /** @var Collection<int, Espacio> */
    public Collection $mesas;

    /** @var Collection<int, Espacio> */
    public Collection $mesasFiltradas;

    /** @var array<int, EstadoEspacio> */
    public array $estadosMesa = [];

    public string $filtroMesa = '';

    public ?string $filtroEstado = null;

    public string $ordenarPor = 'nombre';

    // ── Unión de mesas ──
    public ?int $mesaSeleccionadaId = null;

    public ?int $mesaDestinoId = null;

    /** @var int[] */
    public array $mesasParaUnir = [];

    public string $motivoUnion = 'uso_inmediato';

    public ?int $reservaIdParaUnion = null;

    /** @var Collection<int, Reserva> */
    public Collection $reservasRestaurante;

    // ── Descuento ──
    public ?int $pedidoDescuentoId = null;

    public float $descuentoPorcentaje = 0.0;

    public float $descuentoMonto = 0.0;

    public ?string $motivoDescuento = null;

    // ── Detalle del pedido ──
    public ?Pedido $pedidoDetalle = null;

    /** @var Collection<int, Pedido> */
    public Collection $comandasDetalle;

    public string $simboloMoneda = 'C$';

    // ── DI ──
    private ObtenerMapaMesasQuery $mapaMesas;

    private CambiarEstadoMesa $cambiarEstado;

    private UnirMesas $unirMesasInteractor;

    private SepararMesas $separarMesasInteractor;

    private MoverCuentaMesa $moverCuentaInteractor;

    private AplicarDescuentoCuenta $aplicarDescuentoInteractor;

    private ReimprimirComanda $reimprimirComandaInteractor;

    private ObtenerReservasRestauranteQuery $reservasQuery;

    private ObtenerComandasMesaConDetalleQuery $comandasMesaDetalleQuery;

    private RestauranteRepositorioInterface $repositorio;

    public function boot(
        ObtenerMapaMesasQuery $mapaMesas,
        CambiarEstadoMesa $cambiarEstado,
        UnirMesas $unirMesasInteractor,
        SepararMesas $separarMesasInteractor,
        MoverCuentaMesa $moverCuentaInteractor,
        AplicarDescuentoCuenta $aplicarDescuentoInteractor,
        ReimprimirComanda $reimprimirComandaInteractor,
        ObtenerReservasRestauranteQuery $reservasQuery,
        ObtenerComandasMesaConDetalleQuery $comandasMesaDetalleQuery,
        RestauranteRepositorioInterface $repositorio,
    ): void {
        $this->mapaMesas = $mapaMesas;
        $this->cambiarEstado = $cambiarEstado;
        $this->unirMesasInteractor = $unirMesasInteractor;
        $this->separarMesasInteractor = $separarMesasInteractor;
        $this->moverCuentaInteractor = $moverCuentaInteractor;
        $this->aplicarDescuentoInteractor = $aplicarDescuentoInteractor;
        $this->reimprimirComandaInteractor = $reimprimirComandaInteractor;
        $this->reservasQuery = $reservasQuery;
        $this->comandasMesaDetalleQuery = $comandasMesaDetalleQuery;
        $this->repositorio = $repositorio;
    }

    public function mount(): void
    {
        $this->comandasDetalle = collect();
        $this->recargarMesas();
        $this->reservasRestaurante = $this->reservasQuery->ejecutar();
        $this->estadosMesa = EstadoEspacio::cases();
        $this->mesasFiltradas = $this->mesas;
    }

    public function hydrate(): void
    {
        $this->recargarMesas();
    }

    protected function getViewData(): array
    {
        return [
            'ambientes' => $this->ambientes,
            'mesas' => $this->mesas,
        ];
    }

    public function recargarMesas(): void
    {
        $mapa = $this->mapaMesas->ejecutar();
        $this->ambientes = $mapa['ambientes'];
        $this->mesas = $mapa['mesas'];
        $this->aplicarFiltroMesa();
    }

    public function updatedFiltroMesa(): void
    {
        $this->aplicarFiltroMesa();
    }

    public function updatedFiltroEstado(): void
    {
        $this->aplicarFiltroMesa();
    }

    public function updatedOrdenarPor(): void
    {
        $this->aplicarFiltroMesa();
    }

    private function aplicarFiltroMesa(): void
    {
        $this->mesasFiltradas = $this->mesas;

        if ($this->filtroMesa !== '') {
            $filtro = mb_strtolower(trim($this->filtroMesa));
            $this->mesasFiltradas = $this->mesasFiltradas->filter(
                static fn (Espacio $mesa): bool => str_contains(mb_strtolower((string) $mesa->nombre), $filtro)
                    || str_contains((string) $mesa->id, $filtro)
            );
        }

        if ($this->filtroEstado !== null && $this->filtroEstado !== '') {
            $estadoFilter = EstadoEspacio::tryFrom((int) $this->filtroEstado);

            if ($estadoFilter !== null) {
                $this->mesasFiltradas = $this->mesasFiltradas->filter(
                    static fn (Espacio $mesa): bool => $mesa->estado === $estadoFilter
                );
            }
        }

        $this->mesasFiltradas = match ($this->ordenarPor) {
            'estado' => $this->mesasFiltradas->sortBy(
                static fn (Espacio $mesa): int => $mesa->estado->value
            ),
            'capacidad' => $this->mesasFiltradas->sortBy(
                static fn (Espacio $mesa): int => (int) ($mesa->meta_datos['capacidad_personas'] ?? 0)
            ),
            default => $this->mesasFiltradas->sortBy('nombre'),
        };
    }

    // ────────────────────────────────────────────
    // Cambio de estado
    // ────────────────────────────────────────────

    public function cambiarEstadoMesa(int $mesaId, EstadoEspacio|int|string $nuevoEstado): void
    {
        $estado = $nuevoEstado instanceof EstadoEspacio
            ? $nuevoEstado
            : EstadoEspacio::tryFrom(is_numeric($nuevoEstado) ? (int) $nuevoEstado : 0);

        if ($estado === null) {
            Notification::make()->title('Estado de mesa inválido')->danger()->send();

            return;
        }

        try {
            $mesa = $this->cambiarEstado->ejecutar($mesaId, $estado);
        } catch (Exception $e) {
            Notification::make()->title('No se pudo cambiar el estado')->body($e->getMessage())->danger()->send();

            return;
        }

        $this->recargarMesas();

        Notification::make()
            ->title("Estado cambiado a {$mesa->estado->getLabel()}")
            ->success()
            ->send();
    }

    public function confirmarLlegadaReserva(int $mesaId): void
    {
        try {
            $authId = auth()->id();
            $meseroId = is_numeric($authId) ? (int) $authId : null;
            $pedido = app(ConfirmarLlegadaReservaMesa::class)->ejecutar($mesaId, $meseroId);
            $this->recargarMesas();

            Notification::make()
                ->title('Cliente atendido - Mesa Ocupada')
                ->body("Comanda #$pedido->codigo abierta y pre-orden enviada a cocina.")
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al confirmar llegada')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelarReservaMesa(int $mesaId): void
    {
        try {
            app(CancelarReservaMesa::class)->ejecutar($mesaId);
            $this->recargarMesas();

            Notification::make()
                ->title('Reservación cancelada')
                ->body('La mesa ha sido desvinculada y liberada.')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al cancelar reservación')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function solicitarLimpiezaMesa(int $mesaId): void
    {
        $mesa = $this->repositorio->obtenerEspacioPorId($mesaId);

        $this->cambiarEstadoMesa($mesaId, EstadoEspacio::Sucio);

        if ($mesa !== null) {
            app(RegistrarSolicitudLimpieza::class)->execute(
                limpiable: $mesa,
                prioridad: 'normal',
                notas: "Limpieza de la mesa '{$mesa->nombre}' solicitada desde la Gestión de Mesas",
            );
        }
    }

    public function marcarMesaLimpia(int $mesaId): void
    {
        $mesa = $this->repositorio->obtenerEspacioPorId($mesaId);

        if ($mesa !== null) {
            $solicitudActiva = SolicitudLimpieza::query()
                ->where('limpiable_type', $mesa->getMorphClass())
                ->where('limpiable_id', $mesa->id)
                ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
                ->first();

            if ($solicitudActiva !== null) {
                Notification::make()
                    ->title('Solicitud de limpieza activa')
                    ->body("La mesa '{$mesa->nombre}' tiene una solicitud de limpieza activa. Debe ser completada y cerrada desde el Tablero de Limpieza.")
                    ->warning()
                    ->send();

                return;
            }
        }

        $this->cambiarEstadoMesa($mesaId, EstadoEspacio::Disponible);
    }

    public function reservarMesa(int $mesaId): void
    {
        $this->cambiarEstadoMesa($mesaId, EstadoEspacio::Reservado);
    }

    // ────────────────────────────────────────────
    // Unión y Separación de Mesas
    // ────────────────────────────────────────────

    // ────────────────────────────────────────────
    // Mover cuenta
    // ────────────────────────────────────────────

    public function moverCuentaMesa(): void
    {
        if ($this->mesaSeleccionadaId === null || $this->mesaDestinoId === null) {
            Notification::make()
                ->title('Selección incompleta')
                ->body('Debe seleccionar la mesa origen y la mesa destino.')
                ->warning()
                ->send();

            return;
        }

        try {
            $this->moverCuentaInteractor->ejecutar(
                mesaOrigenId: $this->mesaSeleccionadaId,
                mesaDestinoId: $this->mesaDestinoId
            );

            $this->mesaSeleccionadaId = null;
            $this->mesaDestinoId = null;
            $this->recargarMesas();

            $this->dispatch('close-modal', id: 'modal-mover-cuenta');

            Notification::make()
                ->title('Cuenta trasladada exitosamente')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al mover cuenta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Error al mover cuenta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ────────────────────────────────────────────
    // Descuento
    // ────────────────────────────────────────────

    public function aplicarDescuento(): void
    {
        if ($this->pedidoDescuentoId === null) {
            Notification::make()->title('Pedido no seleccionado')->danger()->send();

            return;
        }

        try {
            $this->aplicarDescuentoInteractor->ejecutar(
                pedidoId: $this->pedidoDescuentoId,
                descuentoPorcentaje: $this->descuentoPorcentaje,
                descuentoMonto: $this->descuentoMonto,
                motivo: $this->motivoDescuento
            );

            $this->pedidoDescuentoId = null;
            $this->descuentoPorcentaje = 0.0;
            $this->descuentoMonto = 0.0;
            $this->motivoDescuento = null;
            $this->recargarMesas();

            $this->dispatch('close-modal', id: 'modal-descuento');

            Notification::make()
                ->title('Descuento aplicado correctamente')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al aplicar descuento')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ────────────────────────────────────────────
    // Reimprimir comanda
    // ────────────────────────────────────────────

    public function reimprimir(int $pedidoId, ?string $area = null): void
    {
        try {
            $pedido = $this->reimprimirComandaInteractor->ejecutar($pedidoId);

            $url = route('admin.restaurante.comanda', [
                'pedido' => $pedido->id,
                'tipo' => 'reimpresion',
                'area' => $area,
            ]);

            $this->dispatch('open-window', url: $url);

            Notification::make()
                ->title("Comanda #$pedido->codigo enviada a reimpresión")
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al reimprimir comanda')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ────────────────────────────────────────────
    // Unir / Separar mesas
    // ────────────────────────────────────────────

    public function unirMesas(): void
    {
        if ($this->mesaSeleccionadaId === null || empty($this->mesasParaUnir)) {
            Notification::make()
                ->title('Selección incompleta')
                ->body('Debe seleccionar una mesa principal y al menos una mesa secundaria para unir.')
                ->warning()
                ->send();

            return;
        }

        try {
            $this->unirMesasInteractor->ejecutar(
                mesaPrincipalId: $this->mesaSeleccionadaId,
                mesasSecundariasIds: $this->mesasParaUnir,
                reservaId: $this->reservaIdParaUnion,
                motivo: $this->motivoUnion
            );

            $this->mesaSeleccionadaId = null;
            $this->mesasParaUnir = [];
            $this->reservaIdParaUnion = null;
            $this->recargarMesas();

            $this->dispatch('close-modal', id: 'modal-unir-mesas');

            Notification::make()
                ->title('Mesas unidas exitosamente')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al unir mesas')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function separarMesas(int $mesaId): void
    {
        try {
            $this->separarMesasInteractor->ejecutar($mesaId);
            $this->recargarMesas();

            Notification::make()
                ->title('Mesa desvinculada y liberada')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al separar mesa')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ────────────────────────────────────────────
    // Detalle del pedido
    // ────────────────────────────────────────────

    public function verComandasMesa(int $mesaId): void
    {
        $this->comandasDetalle = $this->comandasMesaDetalleQuery->ejecutar($mesaId);
        $this->pedidoDetalle = $this->comandasDetalle->first();

        if ($this->comandasDetalle->isEmpty()) {
            Notification::make()->title('No hay comandas activas en esta mesa')->warning()->send();

            return;
        }

        $this->dispatch('open-modal', id: 'modal-detalle-pedido');
    }

    public function cerrarDetallePedido(): void
    {
        $this->pedidoDetalle = null;
        $this->comandasDetalle = collect();
        $this->dispatch('close-modal', id: 'modal-detalle-pedido');
    }

    public ?int $pedidoCobroId = null;

    public function irACobrarDesdeDetalle(): void
    {
        if ($this->pedidoDetalle === null) {
            return;
        }

        $this->pedidoCobroId = $this->pedidoDetalle->id;
        $this->cerrarDetallePedido();
        $this->mountAction('cobrarCuenta');
    }

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
                $this->recargarMesas();
            }
        )->name('cobrarCuenta');
    }

    public function iniciarCobroMesa(int $mesaId): void
    {
        $mesa = $this->repositorio->obtenerEspacioPorId($mesaId);

        if ($mesa === null) {
            Notification::make()->title('Mesa no encontrada')->danger()->send();

            return;
        }

        $pedidosActivos = $mesa->pedidosActivos;
        $primerPedido = $pedidosActivos->first();

        if ($primerPedido === null) {
            Notification::make()->title('No hay pedidos activos en esta mesa')->warning()->send();

            return;
        }

        $this->pedidoCobroId = $primerPedido->id;
        $this->mountAction('cobrarCuenta');
    }

    // ────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────

    /**
     * @return array{colorBadge: string, bgDot: string, borderCircle: string, borderCard: string, badgeLabel: string}
     */
    public function obtenerConfiguracionEstiloMesa(EstadoEspacio $estado): array
    {
        return match ($estado) {
            EstadoEspacio::Disponible => [
                'colorBadge' => 'success',
                'bgDot' => 'bg-emerald-500',
                'borderCircle' => 'border-emerald-500 dark:border-emerald-400',
                'borderCard' => 'border-emerald-300 dark:border-emerald-800/60 bg-emerald-50/40 dark:bg-emerald-950/20',
                'badgeLabel' => 'Disponible',
            ],
            EstadoEspacio::Mantenimiento => [
                'colorBadge' => 'gray',
                'bgDot' => 'bg-slate-500',
                'borderCircle' => 'border-slate-500 dark:border-slate-400',
                'borderCard' => 'border-slate-300 dark:border-slate-800/60 bg-slate-50/40 dark:bg-slate-950/20',
                'badgeLabel' => 'Fuera de Servicio',
            ],
            EstadoEspacio::Limpieza, EstadoEspacio::Sucio => [
                'colorBadge' => 'warning',
                'bgDot' => 'bg-amber-500',
                'borderCircle' => 'border-amber-500 dark:border-amber-400',
                'borderCard' => 'border-amber-300 dark:border-amber-800/60 bg-amber-50/40 dark:bg-amber-950/20',
                'badgeLabel' => 'Pendiente Limpieza',
            ],
            EstadoEspacio::Reservado => [
                'colorBadge' => 'info',
                'bgDot' => 'bg-sky-500',
                'borderCircle' => 'border-sky-500 dark:border-sky-400',
                'borderCard' => 'border-sky-300 dark:border-sky-800/60 bg-sky-50/40 dark:bg-sky-950/20',
                'badgeLabel' => 'Reservada',
            ],
            EstadoEspacio::Ocupado => [
                'colorBadge' => 'danger',
                'bgDot' => 'bg-rose-500',
                'borderCircle' => 'border-rose-500 dark:border-rose-400',
                'borderCard' => 'border-rose-300 dark:border-rose-800/60 bg-rose-50/40 dark:bg-rose-950/20',
                'badgeLabel' => 'Ocupada',
            ],
            EstadoEspacio::Inactivo => [
                'colorBadge' => 'gray',
                'bgDot' => 'bg-slate-500',
                'borderCircle' => 'border-slate-500 dark:border-slate-400',
                'borderCard' => 'border-stone-200 dark:border-stone-800 bg-stone-50/40 dark:bg-stone-900/50',
                'badgeLabel' => 'Inactiva',
            ],
        };
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_GestionMesas') ?? false;
    }
}
