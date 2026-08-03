<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Pages\Restaurante\PantallaPedidos;
use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Filament\Shared\Actions\Restaurante\PagarPedidoAction;
use App\Filament\Shared\Actions\Restaurante\RegistrarClienteRapidoAction;
use App\Interactors\Restaurante\Cocina\ReenviarItemsPendientesACocina;
use App\Interactors\Restaurante\Cuentas\AbrirCuentaYConsumoRestaurante;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Interactors\Restaurante\Pedidos\SepararPedido;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

final class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
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

                    return $pedido->cuenta_id === null || $pedido->cuenta === null || ! $pedido->cuenta->estaAbierta();
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
                ->label('Cobrar Cuenta')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return $pedido->cuenta_id !== null && $pedido->cuenta !== null && $pedido->cuenta->estaAbierta();
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

                return $pedido->cuenta_id === null || $pedido->cuenta === null || ! $pedido->cuenta->estaAbierta();
            }),

            Action::make('enviarCocina')
                ->label('Enviar a Cocina')
                ->icon('heroicon-o-fire')
                ->color('warning')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    return in_array($pedido->estado, [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION, EstadoPedido::SERVIDO], true)
                        && $pedido->items()->where('estado', EstadoItemPedido::PENDIENTE)->exists();
                })
                ->requiresConfirmation()
                ->modalHeading('Enviar Comanda a Cocina')
                ->modalDescription('¿Confirmar envío de esta comanda a la cocina para preparación?')
                ->action(function (): void {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();
                    try {
                        if (in_array($pedido->estado, [EstadoPedido::SERVIDO, EstadoPedido::EN_PREPARACION], true)) {
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

            Action::make('separarPedido')
                ->label('Separar Pedido')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->visible(function (): bool {
                    /** @var Pedido $pedido */
                    $pedido = $this->getRecord();

                    if (in_array($pedido->estado, [
                        EstadoPedido::PAGADO,
                        EstadoPedido::CARGADO_A_HABITACION,
                        EstadoPedido::CANCELADO,
                    ], true)) {
                        return false;
                    }

                    $movibles = $pedido->items()
                        ->whereNotIn('estado', [
                            EstadoItemPedido::ANULADO,
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

        $tienePendientes = $record->items()->where('estado', EstadoItemPedido::PENDIENTE)->exists();

        if (! $tienePendientes) {
            return;
        }

        try {
            if (in_array($record->estado, [EstadoPedido::SERVIDO, EstadoPedido::EN_PREPARACION], true)) {
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
}
