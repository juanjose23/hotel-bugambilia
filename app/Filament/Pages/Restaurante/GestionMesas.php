<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Auditoria\RegistrarAuditoriaRestaurante;
use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Cuentas\AbrirCuenta;
use App\Interactors\Cuentas\CerrarCuenta;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Interactors\Cuentas\RegistrarPagoCuenta;
use App\Interactors\Restaurante\Cocina\ReimprimirComanda;
use App\Interactors\Restaurante\Cuentas\AplicarDescuentoCuenta;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Interactors\Restaurante\Mesas\MoverCuentaMesa;
use App\Interactors\Restaurante\Mesas\SepararMesas;
use App\Interactors\Restaurante\Mesas\UnirMesas;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Landing\ObtenerReservasRestauranteQuery;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMapaMesasQuery;
use App\Repository\Queries\Restaurante\Pedidos\BuscarClientesRapidoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidoConDetalleQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Exception;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class GestionMesas extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Gestión de Mesas';

    protected static ?string $title = 'Mapa de Mesas e Interfaz de Servicio POS';

    protected static ?string $slug = 'restaurante/mesas';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.restaurante.gestion-mesas';

    // ── Mapa de mesas ──
    /** @var Collection<int, Espacio> */
    public Collection $ambientes;

    /** @var Collection<int, Espacio> */
    public Collection $mesas;

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

    // ── Cobro / Pago ──
    /** @var int[] IDs de los pedidos a cobrar */
    public array $pedidosCobroIds = [];

    public float $totalCobro = 0.0;

    public int $metodoPago = 1;

    public float $montoRecibido = 0.0;

    public ?string $referenciaPago = null;

    public bool $imprimirVoucherTrasPago = false;

    public string $modoCobro = 'total';

    public string $simboloMoneda = 'C$';

    // ── Cliente ──
    public ?int $clienteSeleccionadoId = null;

    public ?string $clienteSeleccionadoNombre = null;

    public ?string $busquedaCliente = '';

    /** @var Collection<int, Persona> */
    public Collection $resultadosClientes;

    // ── DI ──
    private ObtenerMapaMesasQuery $mapaMesas;

    private CambiarEstadoMesa $cambiarEstado;

    private UnirMesas $unirMesasInteractor;

    private SepararMesas $separarMesasInteractor;

    private MoverCuentaMesa $moverCuentaInteractor;

    private AplicarDescuentoCuenta $aplicarDescuentoInteractor;

    private ReimprimirComanda $reimprimirComandaInteractor;

    private RegistrarAuditoriaRestaurante $auditoria;

    private ObtenerReservasRestauranteQuery $reservasQuery;

    private ObtenerPedidoConDetalleQuery $pedidoDetalleQuery;

    private BuscarClientesRapidoQuery $buscarClientesQuery;

    private AbrirCuenta $abrirCuenta;

    private RegistrarDetalleCuenta $registrarDetalle;

    private RegistrarPagoCuenta $registrarPago;

    private CerrarCuenta $cerrarCuenta;

    private RestauranteRepositorioInterface $repositorio;

    public function boot(
        ObtenerMapaMesasQuery $mapaMesas,
        CambiarEstadoMesa $cambiarEstado,
        UnirMesas $unirMesasInteractor,
        SepararMesas $separarMesasInteractor,
        MoverCuentaMesa $moverCuentaInteractor,
        AplicarDescuentoCuenta $aplicarDescuentoInteractor,
        ReimprimirComanda $reimprimirComandaInteractor,
        RegistrarAuditoriaRestaurante $auditoria,
        ObtenerReservasRestauranteQuery $reservasQuery,
        ObtenerPedidoConDetalleQuery $pedidoDetalleQuery,
        BuscarClientesRapidoQuery $buscarClientesQuery,
        AbrirCuenta $abrirCuenta,
        RegistrarDetalleCuenta $registrarDetalle,
        RegistrarPagoCuenta $registrarPago,
        CerrarCuenta $cerrarCuenta,
        RestauranteRepositorioInterface $repositorio,
    ): void {
        $this->mapaMesas = $mapaMesas;
        $this->cambiarEstado = $cambiarEstado;
        $this->unirMesasInteractor = $unirMesasInteractor;
        $this->separarMesasInteractor = $separarMesasInteractor;
        $this->moverCuentaInteractor = $moverCuentaInteractor;
        $this->aplicarDescuentoInteractor = $aplicarDescuentoInteractor;
        $this->reimprimirComandaInteractor = $reimprimirComandaInteractor;
        $this->auditoria = $auditoria;
        $this->reservasQuery = $reservasQuery;
        $this->pedidoDetalleQuery = $pedidoDetalleQuery;
        $this->buscarClientesQuery = $buscarClientesQuery;
        $this->abrirCuenta = $abrirCuenta;
        $this->registrarDetalle = $registrarDetalle;
        $this->registrarPago = $registrarPago;
        $this->cerrarCuenta = $cerrarCuenta;
        $this->repositorio = $repositorio;
    }

    public function mount(): void
    {
        $this->recargarMesas();
        $this->reservasRestaurante = $this->reservasQuery->ejecutar();
        $this->resultadosClientes = collect();
    }

    public function hydrate(): void
    {
        $this->recargarMesas();
    }

    protected function getViewData(): array
    {
        $this->recargarMesas();

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
    }

    // ────────────────────────────────────────────
    // Cambio de estado
    // ────────────────────────────────────────────

    public function cambiarEstadoMesa(int $mesaId, string $nuevoEstado): void
    {
        $estado = EstadoEspacio::tryFrom((int) $nuevoEstado);

        if ($estado === null) {
            Notification::make()->title('Estado de mesa inválido')->danger()->send();

            return;
        }

        $mesa = $this->cambiarEstado->ejecutar($mesaId, $estado);
        $this->recargarMesas();

        $this->auditoria->registrar(
            accion: AccionAuditoriaRestaurante::CambioEstadoMesa,
            mesaId: $mesaId,
            detalles: ['nuevo_estado' => $estado->getLabel()],
            userId: auth()->id() !== null ? (int) auth()->id() : null,
            ipAddress: request()->ip(),
        );

        Notification::make()
            ->title("Mesa {$mesa->nombre} cambiada a {$estado->getLabel()}")
            ->success()
            ->send();
    }

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
            $pedido = $this->reimprimirComandaInteractor->ejecutar(
                $pedidoId,
                $area,
                auth()->id() !== null ? (int) auth()->id() : null,
                request()->ip(),
            );

            $url = route('admin.restaurante.comanda', [
                'pedido' => $pedido->id,
                'tipo' => 'reimpresion',
                'area' => $area,
            ]);

            $this->dispatch('open-window', url: $url);

            Notification::make()
                ->title("Comanda #{$pedido->codigo} enviada a reimpresión")
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

    public function verDetallePedido(int $pedidoId): void
    {
        $this->pedidoDetalle = $this->pedidoDetalleQuery->ejecutar($pedidoId);

        if ($this->pedidoDetalle === null) {
            Notification::make()->title('Pedido no encontrado')->danger()->send();

            return;
        }

        $this->dispatch('open-modal', id: 'modal-detalle-pedido');
    }

    public function cerrarDetallePedido(): void
    {
        $this->pedidoDetalle = null;
        $this->dispatch('close-modal', id: 'modal-detalle-pedido');
    }

    public function irACobrarDesdeDetalle(): void
    {
        if ($this->pedidoDetalle === null) {
            return;
        }

        $pedidoId = $this->pedidoDetalle->id;
        $this->cerrarDetallePedido();
        $this->iniciarCobro([$pedidoId]);
    }

    // ────────────────────────────────────────────
    // Cobro / Pago
    // ────────────────────────────────────────────

    /**
     * @param  int[]  $pedidoIds
     */
    public function iniciarCobro(array $pedidoIds): void
    {
        $this->pedidosCobroIds = $pedidoIds;
        $this->totalCobro = 0.0;

        $pedidos = Pedido::query()
            ->whereIn('id', $pedidoIds)
            ->with(['items.plato', 'mesa'])
            ->get();

        foreach ($pedidos as $pedido) {
            $subtotal = 0.0;
            foreach ($pedido->items as $item) {
                if ($item->estado !== EstadoItemPedido::ANULADO) {
                    $subtotal += (float) $item->subtotal;
                }
            }
            $this->totalCobro += $subtotal;
        }

        $this->totalCobro = round($this->totalCobro, 2);
        $this->montoRecibido = $this->totalCobro;
        $this->metodoPago = MetodoPago::EFECTIVO->value;
        $this->referenciaPago = null;
        $this->imprimirVoucherTrasPago = false;
        $this->modoCobro = count($pedidoIds) > 1 ? 'total' : 'pedido';
        $this->clienteSeleccionadoId = null;
        $this->clienteSeleccionadoNombre = null;
        $this->busquedaCliente = '';
        $this->resultadosClientes = collect();

        /** @var Moneda|null $monedaPredeterminada */
        $monedaPredeterminada = Moneda::query()->where('es_predeterminada', true)->first()
            ?? Moneda::query()->first();
        $this->simboloMoneda = $monedaPredeterminada->simbolo ?? 'C$';

        $this->dispatch('open-modal', id: 'modal-cobro');
    }

    public function cerrarCobro(): void
    {
        $this->pedidosCobroIds = [];
        $this->totalCobro = 0.0;
        $this->montoRecibido = 0.0;
        $this->referenciaPago = null;
        $this->clienteSeleccionadoId = null;
        $this->clienteSeleccionadoNombre = null;
        $this->dispatch('close-modal', id: 'modal-cobro');
    }

    public function buscarClientesAction(): void
    {
        $this->resultadosClientes = $this->buscarClientesQuery->ejecutar($this->busquedaCliente ?? '');
    }

    public function seleccionarCliente(int $personaId): void
    {
        $persona = Persona::query()->find($personaId);

        if ($persona instanceof Persona) {
            $this->clienteSeleccionadoId = $persona->id;
            $this->clienteSeleccionadoNombre = $persona->nombre_completo ?? $persona->primer_nombre;
            $this->busquedaCliente = '';
            $this->resultadosClientes = collect();
        }
    }

    public function limpiarClienteSeleccionado(): void
    {
        $this->clienteSeleccionadoId = null;
        $this->clienteSeleccionadoNombre = null;
    }

    public function confirmarPago(): void
    {
        if ($this->pedidosCobroIds === []) {
            Notification::make()->title('No hay pedidos para cobrar')->danger()->send();

            return;
        }

        $metodoEnum = MetodoPago::tryFrom($this->metodoPago);

        if ($metodoEnum === null) {
            Notification::make()->title('Método de pago inválido')->danger()->send();

            return;
        }

        try {
            $pedidos = Pedido::query()
                ->whereIn('id', $this->pedidosCobroIds)
                ->with(['items.plato', 'mesa'])
                ->get();

            $mesaId = $pedidos->first()?->mesa_id;
            $userId = auth()->id() !== null ? (int) auth()->id() : null;
            /** @var int|null $monedaId */
            $monedaId = Moneda::query()->where('es_predeterminada', true)->value('id')
                ?? Moneda::query()->value('id');

            // 1. Abrir cuenta unificada tipo RESTAURANTE_DIRECTO
            $cuenta = $this->abrirCuenta->ejecutar(
                tipo: TipoCuenta::RESTAURANTE_DIRECTO,
                cliente: $this->clienteSeleccionadoId !== null
                    ? Persona::find($this->clienteSeleccionadoId)
                    : null,
                monedaId: $monedaId,
                usuarioId: $userId,
            );

            // 2. Registrar cada pedido como detalle de la cuenta
            foreach ($pedidos as $pedido) {
                foreach ($pedido->items as $item) {
                    if ($item->estado === EstadoItemPedido::ANULADO) {
                        continue;
                    }

                    $this->registrarDetalle->ejecutar(
                        cuenta: $cuenta,
                        concepto: ($item->plato->nombre ?? 'Platillo').($item->observaciones ? " ({$item->observaciones})" : ''),
                        precioUnitario: (float) $item->precio_unitario,
                        cantidad: (float) $item->cantidad,
                        impuesto: 0.0,
                        descuento: 0.0,
                        origen: $item,
                        espacioId: $mesaId,
                        creadorId: $userId,
                    );
                }

                // Marcar pedido como pagado
                $pedido->estado = EstadoPedido::PAGADO;
                $pedido->cerrado_en = now();
                $pedido->cuenta_id = $cuenta->id;
                if ($this->clienteSeleccionadoId !== null) {
                    $pedido->cliente_id = $this->clienteSeleccionadoId;
                }
                $this->repositorio->guardarPedido($pedido);
            }

            // 3. Registrar pago
            $propina = max(0.0, $this->montoRecibido - $this->totalCobro);

            $this->registrarPago->ejecutar(
                cuenta: $cuenta,
                metodoPago: $metodoEnum,
                monto: $this->totalCobro,
                propina: $propina,
                estado: EstadoPago::APLICADO,
                referenciaTransaccion: $this->referenciaPago,
                monedaId: $monedaId,
                usuarioId: $userId,
            );

            // 4. Cerrar cuenta si saldada
            $cuenta->refresh();
            if ((float) $cuenta->saldo <= 0.0) {
                $this->cerrarCuenta->ejecutar($cuenta, $userId);
            }

            // 5. Auditar
            $this->auditoria->registrar(
                accion: AccionAuditoriaRestaurante::PagoRegistrado,
                mesaId: $mesaId,
                detalles: [
                    'cuenta_numero' => $cuenta->numero_cuenta,
                    'total' => $cuenta->total,
                    'metodo_pago' => $metodoEnum->getLabel(),
                ],
                userId: $userId,
                ipAddress: request()->ip(),
            );

            $pedidoId = $this->pedidosCobroIds[0];

            // 6. Marcar mesa sucia si no quedan pedidos activos
            if ($mesaId !== null) {
                $pedidosRestantes = Pedido::query()
                    ->where('mesa_id', $mesaId)
                    ->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION, EstadoPedido::SERVIDO])
                    ->count();

                if ($pedidosRestantes === 0) {
                    $mesa = Espacio::find($mesaId);
                    if ($mesa instanceof Espacio) {
                        $this->repositorio->actualizarEspacio($mesa, [
                            'estado' => EstadoEspacio::Sucio,
                        ]);
                    }
                }
            }

            $this->cerrarCobro();
            $this->recargarMesas();

            Notification::make()
                ->title('Pago registrado exitosamente')
                ->success()
                ->send();

            if ($this->imprimirVoucherTrasPago) {
                $url = route('admin.restaurante.voucher', [
                    'pedido' => $pedidoId,
                    'tipo' => 'pago',
                    'formato' => 'html',
                    'cuenta_id' => $cuenta->id,
                ]);
                $this->dispatch('open-window', url: $url);
            }
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al procesar pago')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────

    public function getVuelto(): float
    {
        return max(0.0, round($this->montoRecibido - $this->totalCobro, 2));
    }

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
                'borderCard' => 'border-emerald-500/40 dark:border-emerald-500/30 bg-emerald-500/5 dark:bg-emerald-950/20',
                'badgeLabel' => 'Disponible',
            ],
            EstadoEspacio::Mantenimiento => [
                'colorBadge' => 'gray',
                'bgDot' => 'bg-slate-500',
                'borderCircle' => 'border-slate-500 dark:border-slate-400',
                'borderCard' => 'border-slate-500/40 dark:border-slate-500/30 bg-slate-500/5 dark:bg-slate-950/20',
                'badgeLabel' => 'Fuera de Servicio',
            ],
            EstadoEspacio::Limpieza, EstadoEspacio::Sucio => [
                'colorBadge' => 'warning',
                'bgDot' => 'bg-amber-500',
                'borderCircle' => 'border-amber-500 dark:border-amber-400',
                'borderCard' => 'border-amber-500/40 dark:border-amber-500/30 bg-amber-500/5 dark:bg-amber-950/20',
                'badgeLabel' => 'Pendiente Limpieza',
            ],
            EstadoEspacio::Reservado => [
                'colorBadge' => 'info',
                'bgDot' => 'bg-sky-500',
                'borderCircle' => 'border-sky-500 dark:border-sky-400',
                'borderCard' => 'border-sky-500/40 dark:border-sky-500/30 bg-sky-500/5 dark:bg-sky-950/20',
                'badgeLabel' => 'Reservada',
            ],
            EstadoEspacio::Ocupado => [
                'colorBadge' => 'danger',
                'bgDot' => 'bg-rose-500',
                'borderCircle' => 'border-rose-500 dark:border-rose-400',
                'borderCard' => 'border-rose-500/40 dark:border-rose-500/30 bg-rose-500/5 dark:bg-rose-950/20',
                'badgeLabel' => 'Ocupada',
            ],
            EstadoEspacio::Inactivo => [
                'colorBadge' => 'gray',
                'bgDot' => 'bg-slate-500',
                'borderCircle' => 'border-slate-500 dark:border-slate-400',
                'borderCard' => 'border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50',
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
