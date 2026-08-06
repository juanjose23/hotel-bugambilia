<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Pages\Restaurante\PantallaPedidos;
use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Filament\Shared\Actions\Restaurante\PagarPedidoAction;
use App\Filament\Shared\Actions\Restaurante\RegistrarClienteRapidoAction;
use App\Interactors\Cuentas\Gestion\TransferirPedidoACuenta;
use App\Interactors\Restaurante\Cocina\ReenviarItemsPendientesACocina;
use App\Interactors\Restaurante\Cuentas\AbrirCuentaYConsumoRestaurante;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Interactors\Restaurante\Pedidos\RecalcularTotalesPedido;
use App\Interactors\Restaurante\Pedidos\ResolverFaltanteStockPedido;
use App\Interactors\Restaurante\Pedidos\SepararPedido;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;

final class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Pedido $record */
        $record = $this->getRecord();
        $record->loadMissing('cuenta');

        return [
            RegistrarClienteRapidoAction::make(
                onClienteRegistrado: function ($persona): void {
                    $this->form->fill([
                        ...(array) $this->form->getRawState(),
                        'cliente_id' => $persona->id,
                    ]);
                }
            ),

            Action::make('abrirCuentaRestaurante')
                ->label('Abrir Cuenta y Cargar Consumo')
                ->icon('heroicon-o-folder-plus')
                ->color('primary')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    $tieneItemsPendientes = $pedido->items()->where('estado', EstadoItemPedido::PENDIENTE)->exists();

                    if (in_array($pedido->estado, [EstadoPedido::PAGADO, EstadoPedido::LISTO, EstadoPedido::SERVIDO, EstadoPedido::CANCELADO], true) && ! $tieneItemsPendientes) {
                        return false;
                    }

                    if ($pedido->cuenta !== null && ($pedido->cuenta->estado === EstadoCuenta::CERRADA || (float) $pedido->cuenta->saldo <= 0) && ! $tieneItemsPendientes) {
                        return false;
                    }

                    return $pedido->cuenta_id === null || $pedido->cuenta === null;
                })
                ->action(function (): void {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();
                    $userId = auth()->id() !== null ? (int) auth()->id() : null;

                    try {
                        $resultado = app(AbrirCuentaYConsumoRestaurante::class)->ejecutar($pedido, $userId);
                        $cuenta = $resultado['cuenta'];
                        $detalles = $resultado['detalles'];

                        $this->form->fill([
                            ...(array) $this->form->getRawState(),
                            'cuenta_id' => $cuenta->id,
                        ]);

                        Notification::make()
                            ->title('Cuenta abierta y consumo registrado')
                            ->body('Cuenta #'.$cuenta->numero_cuenta.' — '.count($detalles).' items cargados.')
                            ->success()
                            ->send();
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('Error al abrir cuenta')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            CobrarCuentaAction::makeFromResolver(
                resolverCuenta: function (): ?Cuenta {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return $pedido->cuenta;
                },
                onSuccess: function (): void {
                    $this->refreshFormData(['cuenta_id', 'subtotal']);
                }
            )
                ->name('abonarCuenta')
                ->label('Abonar Cuenta')
                ->color('warning')
                ->icon('heroicon-o-banknotes')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return $pedido->cuenta_id !== null && $pedido->cuenta !== null && $pedido->cuenta->estaAbierta() && (float) $pedido->cuenta->saldo > 0;
                }),

            Action::make('conceptosCuenta')
                ->label('Conceptos de Cuenta')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->modalHeading('Conceptos cargados a la cuenta')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    return $record instanceof Pedido && $record->cuenta_id !== null;
                })
                ->modalContent(function (): HtmlString {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();
                    $pedido->loadMissing('cuenta.detalles');

                    $cuenta = $pedido->cuenta;
                    if (! $cuenta instanceof Cuenta) {
                        return new HtmlString('<p class="text-sm text-gray-500">Este pedido no tiene cuenta asociada.</p>');
                    }

                    $items = $cuenta->detalles
                        ->map(function ($detalle): string {
                            $concepto = e((string) $detalle->concepto);
                            $descripcion = filled($detalle->descripcion) ? '<span class="text-gray-500"> - '.e((string) $detalle->descripcion).'</span>' : '';
                            $total = number_format((float) $detalle->total, 2);

                            return "<li class=\"flex items-start justify-between gap-4 py-2 border-b border-gray-100 dark:border-gray-800\"><span>{$concepto}{$descripcion}</span><strong>C$ {$total}</strong></li>";
                        })
                        ->implode('');

                    $saldo = number_format((float) $cuenta->saldo, 2);
                    $total = number_format((float) $cuenta->total, 2);

                    return new HtmlString(
                        "<div class=\"space-y-3 text-sm\"><div><strong>Cuenta #{$cuenta->numero_cuenta}</strong><br><span class=\"text-gray-500\">Total C$ {$total} · Saldo C$ {$saldo}</span></div><ul>{$items}</ul></div>"
                    );
                }),

            PagarPedidoAction::make(
                resolverPedido: function (): Pedido {
                    /** @var Pedido $record */
                    $record = $this->getRecord();

                    return $record;
                },
                onSuccess: function (string $voucherUrl): void {
                    $this->js("window.open('{$voucherUrl}', '_blank')");
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->getRecord()]));
                },
            )->visible(function (): bool {
                /** @var Pedido $pedido */
                $pedido = $this->getRecord();

                if (in_array($pedido->estado, [EstadoPedido::PAGADO, EstadoPedido::CANCELADO], true)) {
                    return false;
                }

                if ($pedido->cuenta !== null && ($pedido->cuenta->estado === EstadoCuenta::CERRADA || (float) $pedido->cuenta->saldo <= 0)) {
                    return false;
                }

                return $pedido->cuenta_id === null || $pedido->cuenta === null;
            }),

            Action::make('enviarCocina')
                ->label('Enviar a Cocina')
                ->icon('heroicon-o-fire')
                ->color('warning')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return in_array($pedido->estado, [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION, EstadoPedido::CARGADO_A_HABITACION], true)
                        && $pedido->items()->where('estado', EstadoItemPedido::PENDIENTE)->exists();
                })
                ->requiresConfirmation()
                ->modalHeading('Enviar Comanda a Cocina')
                ->modalDescription('¿Confirmar envío de esta comanda a la cocina para preparación?')
                ->action(function (): void {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();
                    try {
                        if (in_array($pedido->estado, [EstadoPedido::EN_PREPARACION, EstadoPedido::CARGADO_A_HABITACION], true)) {
                            app(ReenviarItemsPendientesACocina::class)->ejecutar($pedido->refresh());
                            Notification::make()
                                ->title('Items reenviados a cocina')
                                ->body("Los nuevos items del pedido {$pedido->codigo} están en preparación.")
                                ->success()
                                ->send();
                        } else {
                            app(EnviarPedidoACocina::class)->ejecutar($pedido->refresh());
                            Notification::make()
                                ->title('Comanda enviada a cocina')
                                ->body("La comanda {$pedido->codigo} está ahora en preparación.")
                                ->success()
                                ->send();
                        }
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('Error al enviar a cocina')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('resolverFaltantesStock')
                ->label('Resolver Faltantes')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return $pedido->items()->where('estado', EstadoItemPedido::BLOQUEADO_STOCK->value)->exists();
                })
                ->modalHeading('Resolver faltantes de stock')
                ->modalDescription('Registre la decisión confirmada con el cliente antes de reenviar el pedido a cocina.')
                ->modalWidth('2xl')
                ->schema([
                    Select::make('item_id')
                        ->label('Item bloqueado')
                        ->options(function (): array {
                            /** @var Pedido $pedido */
                            $pedido = $this->getRecord();

                            return $pedido->items()
                                ->where('estado', EstadoItemPedido::BLOQUEADO_STOCK->value)
                                ->with('plato')
                                ->get()
                                ->mapWithKeys(fn ($item): array => [
                                    $item->id => ($item->plato->nombre ?? 'Platillo').' x '.number_format((float) $item->cantidad, 2),
                                ])
                                ->toArray();
                        })
                        ->required()
                        ->live()
                        ->native(false),

                    Select::make('accion')
                        ->label('Decisión del cliente')
                        ->options([
                            'sustituir_ingrediente' => 'Usar ingrediente sustituto',
                            'cambiar_plato' => 'Cambiar platillo',
                            'anular_item' => 'Quitar item',
                            'cancelar_pedido' => 'Cancelar pedido completo',
                        ])
                        ->required()
                        ->live()
                        ->native(false),

                    Repeater::make('sustituciones')
                        ->label('Sustituciones autorizadas')
                        ->visible(fn (Get $get): bool => $get('accion') === 'sustituir_ingrediente')
                        ->schema([
                            Select::make('variante_original_id')
                                ->label('Ingrediente faltante')
                                ->options(fn (Get $get): array => $this->opcionesIngredientesFaltantes(
                                    is_numeric($get('../../item_id')) ? (int) $get('../../item_id') : 0
                                ))
                                ->required()
                                ->live()
                                ->native(false),
                            Select::make('variante_sustituta_id')
                                ->label('Ingrediente sustituto')
                                ->options(fn (): array => $this->opcionesVariantesProducto())
                                ->required()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            TextInput::make('cantidad_usada')
                                ->label('Cantidad a usar')
                                ->numeric()
                                ->minValue(0.0001)
                                ->step(0.0001)
                                ->required(),
                        ])
                        ->columns(3)
                        ->defaultItems(1),

                    Select::make('plato_id')
                        ->label('Nuevo platillo')
                        ->visible(fn (Get $get): bool => $get('accion') === 'cambiar_plato')
                        ->options(fn (): array => Plato::query()
                            ->activos()
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    try {
                        /** @var array<int, array{variante_original_id?: mixed, variante_sustituta_id?: mixed, cantidad_usada?: mixed}> $sustituciones */
                        $sustituciones = is_array($data['sustituciones'] ?? null) ? $data['sustituciones'] : [];

                        app(ResolverFaltanteStockPedido::class)->ejecutar(
                            pedido: $pedido->refresh(),
                            accion: (string) $data['accion'],
                            itemId: isset($data['item_id']) && is_numeric($data['item_id']) ? (int) $data['item_id'] : null,
                            platoId: isset($data['plato_id']) && is_numeric($data['plato_id']) ? (int) $data['plato_id'] : null,
                            sustituciones: $sustituciones,
                        );

                        Notification::make()
                            ->title('Faltante resuelto')
                            ->body('El pedido quedó listo para reenviarse a cocina.')
                            ->success()
                            ->send();
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('No se pudo resolver el faltante')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('separarPedido')
                ->label('Separar Pedido')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    if (in_array($pedido->estado, [
                        EstadoPedido::PAGADO,
                        EstadoPedido::LISTO,
                        EstadoPedido::SERVIDO,
                        EstadoPedido::CANCELADO,
                    ], true)) {
                        return false;
                    }

                    $pedido->loadMissing('cuenta');
                    if ($pedido->cuenta instanceof Cuenta && ($pedido->cuenta->estado === EstadoCuenta::CERRADA || (float) $pedido->cuenta->saldo <= 0)) {
                        return false;
                    }

                    $movibles = $pedido->items()
                        ->whereNotIn('estado', [
                            EstadoItemPedido::ANULADO,
                            EstadoItemPedido::LISTO,
                            EstadoItemPedido::SERVIDO,
                        ])
                        ->count();

                    $totalNoAnulados = $pedido->items()
                        ->where('estado', '!=', EstadoItemPedido::ANULADO)
                        ->count();

                    return $movibles >= 1 && $totalNoAnulados >= 2;
                })
                ->modalHeading('Separar Pedido')
                ->modalDescription('Seleccione los ítems que desea mover a un nuevo pedido.')
                ->modalWidth('2xl')
                ->schema(function (): array {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    $items = $pedido->items()
                        ->whereNotIn('estado', [
                            EstadoItemPedido::ANULADO,
                            EstadoItemPedido::LISTO,
                            EstadoItemPedido::SERVIDO,
                        ])
                        ->with('plato')
                        ->get();

                    /** @var array<int|string, string> $opciones */
                    $opciones = $items->mapWithKeys(function ($item) {
                        $nombre = $item->plato !== null ? $item->plato->nombre : 'Producto #'.$item->plato_id;
                        $label = "{$nombre}  × {$item->cantidad}  —  C$ ".
                            number_format((float) $item->subtotal, 2);

                        return [$item->id => $label];
                    })->toArray();

                    return [
                        CheckboxList::make('item_ids')
                            ->label('Ítems a mover al nuevo pedido')
                            ->options($opciones)
                            ->required()
                            ->columns(1),
                    ];
                })
                ->action(function (array $data): void {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();
                    $itemIds = array_map('intval', $data['item_ids'] ?? []);
                    $userId = auth()->id() !== null ? (int) auth()->id() : null;

                    try {
                        $nuevoPedido = app(SepararPedido::class)->ejecutar(
                            pedidoOriginal: $pedido,
                            itemIds: $itemIds,
                        );

                        Notification::make()
                            ->title('Pedido Separado')
                            ->body("Se creó el pedido {$nuevoPedido->codigo} con los ítems seleccionados.")
                            ->success()
                            ->send();

                        $this->redirect(PedidoResource::getUrl('edit', ['record' => $nuevoPedido]));
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('Error al separar pedido')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('imprimirComanda')
                ->label('Imprimir Comanda')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(function (): string {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return route('admin.restaurante.comanda', ['pedido' => $pedido->getKey()]);
                })
                ->openUrlInNewTab(),

            Action::make('pantallaTurnos')
                ->label('Pantalla Turnos')
                ->icon('heroicon-o-tv')
                ->color('gray')
                ->url(fn (): string => PantallaPedidos::getUrl())
                ->openUrlInNewTab(),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Pedido $record */
        $record = $this->getRecord();
        $record->loadMissing('cuenta');
        app(RecalcularTotalesPedido::class)->ejecutar($record->refresh());

        $tienePendientes = $record->items()->where('estado', EstadoItemPedido::PENDIENTE)->exists();

        if ($tienePendientes) {
            try {
                if (in_array($record->estado, [EstadoPedido::EN_PREPARACION, EstadoPedido::CARGADO_A_HABITACION], true)) {
                    app(ReenviarItemsPendientesACocina::class)->ejecutar($record->refresh());
                    Notification::make()
                        ->title('Items reenviados a cocina')
                        ->body("Los nuevos items del pedido {$record->codigo} están en preparación.")
                        ->success()
                        ->send();
                } elseif ($record->estado === EstadoPedido::ABIERTO) {
                    app(EnviarPedidoACocina::class)->ejecutar($record->refresh());
                }
            } catch (DomainException $e) {
                Notification::make()
                    ->title('No se pudo enviar a cocina')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }

        $record->refresh()->loadMissing('cuenta');
        $cuenta = $record->cuenta;

        if ($record->estado === EstadoPedido::CARGADO_A_HABITACION && $cuenta instanceof Cuenta && $cuenta->estaAbierta()) {
            try {
                app(TransferirPedidoACuenta::class)->ejecutar(
                    pedido: $record,
                    cuenta: $cuenta,
                    usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                );
            } catch (DomainException $e) {
                Notification::make()
                    ->title('No se pudo actualizar la cuenta')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }

        $this->refreshFormData([
            'cuenta_id',
            'cliente_id',
            'estado',
            'subtotal',
        ]);
    }

    protected function beforeSave(): void
    {
        /** @var Pedido $record */
        $record = $this->getRecord();
        $record->loadMissing('cuenta');

        if (! $this->pedidoPuedeEditar($record)) {
            Notification::make()
                ->title('Pedido bloqueado')
                ->body('No se puede editar un pedido listo, servido, pagado o con cuenta saldada.')
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->disabled(function (): bool {
                $record = $this->getRecord();

                return ! ($record instanceof Pedido && $this->pedidoPuedeEditar($record));
            });
    }

    /** @return array<int, string> */
    private function opcionesIngredientesFaltantes(int $itemId): array
    {
        if ($itemId <= 0) {
            return [];
        }

        /** @var Pedido $pedido */
        $pedido = $this->getRecord();
        $item = $pedido->items()
            ->whereKey($itemId)
            ->where('estado', EstadoItemPedido::BLOQUEADO_STOCK->value)
            ->first();

        if ($item === null) {
            return [];
        }

        /** @var array<int, string> $opciones */
        $opciones = collect($item->bloqueo_stock_detalle ?? [])
            ->mapWithKeys(function (array $detalle): array {
                $id = is_numeric($detalle['variante_original_id'] ?? null) ? (int) $detalle['variante_original_id'] : 0;
                $label = is_string($detalle['ingrediente'] ?? null) ? $detalle['ingrediente'] : 'Ingrediente';

                return [$id => $label];
            })
            ->filter(fn (string $label, int $id): bool => $id > 0 && $label !== '')
            ->toArray();

        return $opciones;
    }

    /** @return array<int, string> */
    private function opcionesVariantesProducto(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = ProductoVariante::query()
            ->with('producto')
            ->where('estado', 1)
            ->orderBy('nombre_variante')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $producto = $variante->producto->nombre ?? null;
                $label = trim(($producto !== null ? $producto.' - ' : '').($variante->nombre_variante ?: 'Variante #'.$variante->id));

                return [(int) $variante->id => $label];
            })
            ->toArray();

        return $opciones;
    }

    private function pedidoPuedeEditar(Pedido $pedido): bool
    {
        if (in_array($pedido->estado, [
            EstadoPedido::LISTO,
            EstadoPedido::SERVIDO,
            EstadoPedido::PAGADO,
            EstadoPedido::CANCELADO,
        ], true)) {
            return false;
        }

        $cuenta = $pedido->cuenta;

        return ! ($cuenta instanceof Cuenta && ($cuenta->estado === EstadoCuenta::CERRADA || (float) $cuenta->saldo <= 0));
    }
}
