<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\BusinessLogic\Restaurante\Cuentas\CalcularTotalesCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Pedidos\CerrarPedidoMesa;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Cuentas\ObtenerCuentasAbiertasQuery;
use App\Repository\Queries\Monedas\ObtenerMonedaPorIdQuery;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Monedas\ObtenerMonedasQuery;
use App\Services\Shared\TasaCambioService;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

final class PagarPedidoAction
{
    /**
     * Crea una acción reutilizable de "Pagar Pedido" con multi-moneda y registro rápido de cliente.
     *
     * @param  \Closure(): Pedido  $resolverPedido
     * @param  \Closure(string $voucherUrl): void  $onSuccess
     */
    public static function make(\Closure $resolverPedido, ?\Closure $onSuccess = null): Action
    {
        return Action::make('pagarPedido')
            ->label('Pagar / Cerrar Comanda')
            ->icon('heroicon-o-currency-dollar')
            ->color('warning')
            ->modalWidth('lg')
            ->visible(function () use ($resolverPedido): bool {
                $pedido = $resolverPedido();
                $estado = $pedido->estado;

                return $estado !== EstadoPedido::PAGADO
                    && $estado !== EstadoPedido::CARGADO_A_HABITACION
                    && $estado !== EstadoPedido::CANCELADO;
            })
            ->schema(function () use ($resolverPedido): array {
                $pedido = $resolverPedido();
                $pedido->loadMissing('items');

                $totales = app(CalcularTotalesCuenta::class)->calcular($pedido);
                $subtotal = $totales['subtotal'];

                $cuentasAbiertas = app(ObtenerCuentasAbiertasQuery::class)->ejecutar()
                    ->mapWithKeys(function (Cuenta $c): array {
                        $titular = 'Sin titular';
                        if ($c->cliente !== null && $c->cliente->nombre_completo !== null) {
                            $titular = $c->cliente->nombre_completo;
                        } elseif ($c->estancia?->habitacion !== null) {
                            $titular = $c->estancia->habitacion->nombre;
                        }

                        return [$c->id => $c->numero_cuenta.' — '.$titular];
                    });

                return [
                    TextEntry::make('resumen_totales')
                        ->hiddenLabel()
                        ->state(function (Get $get) use ($subtotal): HtmlString {
                            $simbolo = e(self::simboloMoneda($get));
                            $totalConvertido = self::totalEnMoneda($get, $subtotal);
                            $formatted = number_format($totalConvertido, 2);

                            return new HtmlString(
                                "<div class='p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg text-sm space-y-1'>".
                                "<div class='font-bold text-amber-900 dark:text-amber-200'>SUBTOTAL COMANDA: {$simbolo} {$formatted}</div>".
                                "<div class='text-xs text-amber-700 dark:text-amber-400'>Los impuestos, descuentos y cargos finales se aplican a nivel de la cuenta general.</div>".
                                '</div>'
                            );
                        }),

                    Toggle::make('cargar_a_habitacion')
                        ->label('Cargo a Habitación')
                        ->live()
                        ->default(false)
                        ->columnSpanFull(),

                    Select::make('cuenta_id')
                        ->label('Cuenta')
                        ->options($cuentasAbiertas)
                        ->searchable()
                        ->visible(fn (Get $get): bool => (bool) $get('cargar_a_habitacion'))
                        ->required(fn (Get $get): bool => (bool) $get('cargar_a_habitacion')),

                    Grid::make(2)->schema([
                        Select::make('metodo_pago')
                            ->label('Método de Pago')
                            ->options(fn () => collect(MetodoPago::cases())
                                ->filter(fn (MetodoPago $m) => $m !== MetodoPago::CARGO_HABITACION)
                                ->mapWithKeys(fn (MetodoPago $m) => [$m->value => $m->getLabel()]))
                            ->visible(fn (Get $get): bool => ! (bool) $get('cargar_a_habitacion'))
                            ->required(fn (Get $get): bool => ! (bool) $get('cargar_a_habitacion'))
                            ->native(false),

                        Select::make('moneda_id')
                            ->label('Moneda')
                            ->options(fn () => app(ObtenerMonedasQuery::class)->ejecutar()
                                ->mapWithKeys(fn ($m) => [$m->id => $m->codigo]))
                            ->visible(fn (Get $get): bool => ! (bool) $get('cargar_a_habitacion'))
                            ->live()
                            ->native(false),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('monto_recibido')
                            ->label('Monto Recibido')
                            ->numeric()
                            ->prefix(fn (Get $get): string => self::simboloMoneda($get))
                            ->visible(fn (Get $get): bool => ! (bool) $get('cargar_a_habitacion'))
                            ->helperText(function (Get $get) use ($subtotal): string {
                                $min = self::totalEnMoneda($get, $subtotal);
                                $simbolo = self::simboloMoneda($get);

                                return 'Min: '.$simbolo.' '.number_format($min, 2);
                            })
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get, $subtotal): void {
                                    $min = self::totalEnMoneda($get, $subtotal);
                                    if (is_numeric($value) && (float) $value > 0 && (float) $value < $min) {
                                        $fail('Mínimo: '.self::simboloMoneda($get).' '.number_format($min, 2));
                                    }
                                },
                            ]),

                        TextInput::make('referencia_pago')
                            ->label('Referencia')
                            ->maxLength(100)
                            ->visible(fn (Get $get): bool => ! (bool) $get('cargar_a_habitacion')),
                    ]),
                ];
            })
            ->action(function (array $data) use ($resolverPedido, $onSuccess): void {
                $pedido = $resolverPedido();
                $userId = auth()->id() !== null ? (int) auth()->id() : null;

                $cargarAHabitacion = (bool) ($data['cargar_a_habitacion'] ?? false);

                try {
                    $cuenta = null;
                    if ($cargarAHabitacion && isset($data['cuenta_id']) && is_numeric($data['cuenta_id'])) {
                        $cuenta = app(RestauranteRepositorioInterface::class)->obtenerCuentaPorId((int) $data['cuenta_id']);
                    }

                    $metodoPago = null;
                    if (! $cargarAHabitacion && isset($data['metodo_pago']) && is_numeric($data['metodo_pago'])) {
                        $metodoPago = MetodoPago::tryFrom((int) $data['metodo_pago']);
                    }

                    $montoRecibido = isset($data['monto_recibido']) && is_numeric($data['monto_recibido'])
                        ? (float) $data['monto_recibido']
                        : null;

                    $referencia = isset($data['referencia_pago']) && is_string($data['referencia_pago']) && $data['referencia_pago'] !== ''
                        ? $data['referencia_pago']
                        : null;

                    $monedaId = isset($data['moneda_id']) && is_numeric($data['moneda_id'])
                        ? (int) $data['moneda_id']
                        : null;

                    app(CerrarPedidoMesa::class)->ejecutar(
                        pedido: $pedido,
                        cargarAHabitacion: $cargarAHabitacion,
                        cuentaEstancia: $cuenta,
                        usuarioId: $userId,
                        metodoPago: $metodoPago,
                        montoRecibido: $montoRecibido,
                        referenciaPago: $referencia,
                        clienteId: $pedido->cliente_id,
                        monedaId: $monedaId,
                    );

                    Notification::make()
                        ->title('Comanda cerrada exitosamente')
                        ->body('Pedido #'.$pedido->codigo.' - Subtotal: C$ '.number_format((float) $pedido->subtotal, 2))
                        ->success()
                        ->send();

                    $voucherUrl = route('admin.restaurante.voucher', [
                        'pedido' => $pedido->id,
                        'tipo' => 'pago',
                        'formato' => 'html',
                    ]);
                    if ($onSuccess !== null) {
                        $onSuccess($voucherUrl);
                    }
                } catch (DomainException $e) {
                    Notification::make()
                        ->title('No se pudo cerrar la comanda')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    // ─── Helpers de moneda ───────────────────────────────────

    public static function simboloMoneda(Get $get): string
    {
        $monedaId = $get('moneda_id');
        if (! is_numeric($monedaId)) {
            return self::simboloMonedaDefault();
        }

        $moneda = app(ObtenerMonedaPorIdQuery::class)->ejecutar((int) $monedaId);

        return $moneda !== null
            ? ($moneda->simbolo ?? 'C$')
            : self::simboloMonedaDefault();
    }

    public static function simboloMonedaDefault(): string
    {
        static $simbolo = null;

        if ($simbolo === null) {
            $moneda = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar();
            $simbolo = $moneda !== null ? $moneda->simbolo : 'C$';
        }

        return $simbolo;
    }

    public static function totalEnMoneda(Get $get, float $subtotalNIO): float
    {
        $monedaId = $get('moneda_id');
        if (! is_numeric($monedaId)) {
            return $subtotalNIO;
        }

        $moneda = app(ObtenerMonedaPorIdQuery::class)->ejecutar((int) $monedaId);
        $codigo = $moneda !== null ? $moneda->codigo : 'NIO';

        if ($codigo === 'NIO') {
            return $subtotalNIO;
        }

        $tasa = app(TasaCambioService::class)->obtenerTasa(now()->toDateString(), $codigo, 'NIO');

        return $tasa > 0 ? round($subtotalNIO / $tasa, 2) : $subtotalNIO;
    }
}
