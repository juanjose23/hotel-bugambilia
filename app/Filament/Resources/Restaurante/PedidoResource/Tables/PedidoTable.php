<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Tables;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Filament\Shared\Actions\Restaurante\PagarPedidoAction;
use App\Interactors\Restaurante\Cocina\AnularTodosItemsActivos;
use App\Interactors\Restaurante\Cocina\MarcarTodosItemsServidos;
use App\Interactors\Restaurante\Cocina\ReenviarItemsPendientesACocina;
use App\Interactors\Restaurante\Pedidos\CancelarPedido;
use App\Interactors\Restaurante\Pedidos\CargarPedidoACuenta;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Interactors\Restaurante\Pedidos\SepararPedido;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

final class PedidoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['items.plato', 'cuenta.detalles', 'mesa', 'mesero.persona']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mesa.nombre')
                    ->label('Mesa')
                    ->placeholder('Sin mesa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cuenta.numero_cuenta')
                    ->label('Nº Cuenta')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cuenta.detalles.concepto')
                    ->label('Concepto Cuenta')
                    ->separator(', ')
                    ->limit(48)
                    ->tooltip(fn (Pedido $record): ?string => $record->cuenta?->detalles?->pluck('concepto')->filter()->implode(', '))
                    ->placeholder('—'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Platos')
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('NIO')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->state(fn (Pedido $record): float => (float) ($record->cuenta->total ?? $record->calcularSubtotal()))
                    ->money('NIO')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EstadoPedido ? $state->getLabel() : (is_string($state) ? EstadoPedido::tryFrom($state)?->getLabel() ?? $state : ''))
                    ->color(fn (mixed $state): string => $state instanceof EstadoPedido ? $state->getColor() : (is_string($state) ? EstadoPedido::tryFrom($state)?->getColor() ?? 'gray' : 'gray')),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoPedido::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->disabled(fn (Pedido $record): bool => ! self::pedidoPuedeEditar($record)),
                    PagarPedidoAction::make(),
                    CobrarCuentaAction::makeFromResolver(
                        resolverCuenta: fn (mixed $record = null): ?Cuenta => $record instanceof Pedido ? $record->cuenta : null,
                    )
                        ->name('abonarCuenta')
                        ->label('Abonar Cuenta')
                        ->icon('heroicon-o-banknotes')
                        ->color('warning')
                        ->visible(fn (Pedido $record): bool => $record->cuenta instanceof Cuenta
                            && $record->cuenta->estaAbierta()
                            && (float) $record->cuenta->saldo > 0),
                    Action::make('conceptosCuenta')
                        ->label('Ver Conceptos')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->modalHeading('Conceptos cargados a la cuenta')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->visible(fn (Pedido $record): bool => $record->cuenta instanceof Cuenta)
                        ->modalContent(function (Pedido $record): HtmlString {
                            $record->loadMissing('cuenta.detalles');
                            $cuenta = $record->cuenta;

                            if (! $cuenta instanceof Cuenta) {
                                return new HtmlString('<p class="text-sm text-gray-500">Este pedido no tiene cuenta asociada.</p>');
                            }

                            $items = $cuenta->detalles
                                ->map(function ($detalle): string {
                                    $concepto = e((string) $detalle->concepto);
                                    $total = number_format((float) $detalle->total, 2);

                                    return "<li class=\"flex items-center justify-between gap-4 py-2 border-b border-gray-100 dark:border-gray-800\"><span>{$concepto}</span><strong>C$ {$total}</strong></li>";
                                })
                                ->implode('');

                            return new HtmlString("<div class=\"space-y-3 text-sm\"><strong>Cuenta #{$cuenta->numero_cuenta}</strong><ul>{$items}</ul></div>");
                        }),
                    Action::make('separarPedido')
                        ->label('Dividir Cuenta')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->visible(fn (Pedido $record): bool => self::pedidoPuedeDividir($record))
                        ->modalHeading('Dividir Cuenta')
                        ->modalDescription('Seleccione los ítems que desea mover a un nuevo pedido para cobrar por separado.')
                        ->schema(fn (Pedido $record): array => [
                            CheckboxList::make('item_ids')
                                ->label('Ítems a mover')
                                ->options(self::opcionesItemsParaDividir($record))
                                ->required()
                                ->columns(1),
                        ])
                        ->action(function (Pedido $record, array $data): void {
                            try {
                                $nuevoPedido = app(SepararPedido::class)->ejecutar(
                                    pedidoOriginal: $record,
                                    itemIds: array_map('intval', $data['item_ids'] ?? []),
                                );

                                Notification::make()
                                    ->title('Cuenta dividida')
                                    ->body("Se creó el pedido {$nuevoPedido->codigo} para cobrar por separado.")
                                    ->success()
                                    ->send();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('No se pudo dividir la cuenta')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('enviarCocina')
                        ->label('Enviar a Cocina')
                        ->icon('heroicon-o-fire')
                        ->color('warning')
                        ->visible(fn (Pedido $record): bool => in_array($record->estado, [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION, EstadoPedido::CARGADO_A_HABITACION], true)
                            && $record->items->contains(fn ($item) => $item->estado === EstadoItemPedido::PENDIENTE))
                        ->requiresConfirmation()
                        ->modalHeading('Enviar a Cocina')
                        ->modalDescription('¿Enviar o reenviar items pendientes a la cocina para preparación?')
                        ->action(function (Pedido $record): void {
                            try {
                                if (in_array($record->estado, [EstadoPedido::EN_PREPARACION, EstadoPedido::CARGADO_A_HABITACION], true)) {
                                    app(ReenviarItemsPendientesACocina::class)->ejecutar($record);
                                    Notification::make()
                                        ->title("Items reenviados a cocina - {$record->codigo}")
                                        ->success()
                                        ->send();
                                } else {
                                    app(EnviarPedidoACocina::class)->ejecutarPorId($record->id);
                                    Notification::make()
                                        ->title("Comanda {$record->codigo} enviada a cocina")
                                        ->success()
                                        ->send();
                                }
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('Error al enviar a cocina')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('imprimirComanda')
                        ->label('Imprimir Comanda')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->url(fn (Pedido $record): string => route('admin.restaurante.comanda', $record))
                        ->openUrlInNewTab(),
                    Action::make('marcarServido')
                        ->label('Marcar como Servido')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Pedido $record): bool => $record->estado !== EstadoPedido::PAGADO
                            && $record->estado !== EstadoPedido::CARGADO_A_HABITACION
                            && $record->estado !== EstadoPedido::CANCELADO
                            && $record->items->contains(fn ($item) => $item->estado === EstadoItemPedido::LISTO))
                        ->requiresConfirmation()
                        ->modalHeading('Marcar como Servido')
                        ->modalDescription('¿Confirmar entrega de todos los platos listos al cliente?')
                        ->action(function (Pedido $record): void {
                            $servidos = app(MarcarTodosItemsServidos::class)->ejecutar($record->id);
                            Notification::make()->title(count($servidos).' platos marcados como servidos')->success()->send();
                        }),
                    Action::make('anularItems')
                        ->label('Anular Items Activos')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn (Pedido $record): bool => $record->estado !== EstadoPedido::PAGADO
                            && $record->estado !== EstadoPedido::CARGADO_A_HABITACION
                            && $record->estado !== EstadoPedido::CANCELADO
                            && $record->items->contains(fn ($item) => ! in_array($item->estado, [EstadoItemPedido::ANULADO, EstadoItemPedido::SERVIDO], true)))
                        ->requiresConfirmation()
                        ->modalHeading('Anular Items')
                        ->modalDescription('¿Anular todos los platos activos de esta comanda?')
                        ->action(function (Pedido $record): void {
                            $anulados = app(AnularTodosItemsActivos::class)->ejecutar($record->id);
                            Notification::make()->title(count($anulados).' items anulados')->warning()->send();
                        }),
                    Action::make('cargarACuenta')
                        ->label('Cargar a Cuenta de Habitación')
                        ->icon('heroicon-o-home')
                        ->color('warning')
                        ->visible(fn (Pedido $record): bool => ! in_array($record->estado, [
                            EstadoPedido::PAGADO,
                            EstadoPedido::LISTO,
                            EstadoPedido::SERVIDO,
                            EstadoPedido::CARGADO_A_HABITACION,
                            EstadoPedido::CANCELADO,
                        ], true))
                        ->schema([
                            Select::make('cuenta_id')
                                ->label('Cuenta Abierta')
                                ->options(fn (Pedido $record): array => Cuenta::query()
                                    ->whereIn('estado', [EstadoCuenta::ABIERTA, EstadoCuenta::SOLICITADA])
                                    ->when($record->cliente_id !== null, fn ($query) => $query->where(function ($cuentas) use ($record): void {
                                        $cuentas->whereNull('cliente_id')
                                            ->orWhere('cliente_id', $record->cliente_id);
                                    }))
                                    ->with('cliente', 'estancia.habitacion')
                                    ->get()
                                    ->mapWithKeys(function (Cuenta $c): array {
                                        $habNombre = $c->estancia?->habitacion !== null ? $c->estancia->habitacion->nombre : 'Sin Habitación';
                                        $cliNombre = $c->cliente !== null ? $c->cliente->nombre_completo : 'Cliente';

                                        return [$c->id => "Cuenta #{$c->numero_cuenta} — {$habNombre} — {$cliNombre}"];
                                    })
                                    ->toArray())
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Pedido $record, array $data): void {
                            $cuenta = app(RestauranteRepositorioInterface::class)->obtenerCuentaPorId((int) $data['cuenta_id']);
                            if (! $cuenta instanceof Cuenta) {
                                Notification::make()->title('Cuenta no encontrada')->danger()->send();

                                return;
                            }
                            try {
                                app(CargarPedidoACuenta::class)->ejecutar($record, $cuenta, auth()->id() !== null ? (int) auth()->id() : null);
                                Notification::make()
                                    ->title("Comanda #{$record->codigo} cargada a la cuenta #{$cuenta->numero_cuenta}")
                                    ->success()
                                    ->send();
                            } catch (\DomainException $e) {
                                Notification::make()->title('Error al cargar a cuenta')->body($e->getMessage())->danger()->send();
                            }
                        }),
                    Action::make('cancelarPedido')
                        ->label('Cancelar Pedido')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Pedido $record): bool => ! in_array($record->estado, [
                            EstadoPedido::PAGADO,
                            EstadoPedido::LISTO,
                            EstadoPedido::SERVIDO,
                            EstadoPedido::CARGADO_A_HABITACION,
                            EstadoPedido::CANCELADO,
                        ], true))
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar Pedido')
                        ->modalDescription('¿Está seguro de cancelar esta comanda? Todos los platos activos serán anulados.')
                        ->action(function (Pedido $record): void {
                            try {
                                app(CancelarPedido::class)->ejecutar($record);
                                Notification::make()->title("Pedido {$record->codigo} cancelado")->warning()->send();
                            } catch (\DomainException $e) {
                                Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                            }
                        }),
                ])->icon(Heroicon::EllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    private static function pedidoPuedeEditar(Pedido $pedido): bool
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

    private static function pedidoPuedeDividir(Pedido $pedido): bool
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

        if ($cuenta instanceof Cuenta && ($cuenta->estado === EstadoCuenta::CERRADA || (float) $cuenta->saldo <= 0)) {
            return false;
        }

        $movibles = $pedido->items
            ->filter(fn ($item): bool => ! in_array($item->estado, [
                EstadoItemPedido::ANULADO,
                EstadoItemPedido::LISTO,
                EstadoItemPedido::SERVIDO,
            ], true))
            ->count();

        $totalNoAnulados = $pedido->items
            ->filter(fn ($item): bool => $item->estado !== EstadoItemPedido::ANULADO)
            ->count();

        return $movibles >= 1 && $totalNoAnulados >= 2;
    }

    /** @return array<int, string> */
    private static function opcionesItemsParaDividir(Pedido $pedido): array
    {
        /** @var array<int, string> $opciones */
        $opciones = $pedido->items
            ->filter(fn ($item): bool => ! in_array($item->estado, [
                EstadoItemPedido::ANULADO,
                EstadoItemPedido::LISTO,
                EstadoItemPedido::SERVIDO,
            ], true))
            ->mapWithKeys(function ($item): array {
                $nombre = $item->plato !== null ? $item->plato->nombre : 'Producto #'.$item->plato_id;
                $label = "{$nombre} x {$item->cantidad} - C$ ".number_format((float) $item->subtotal, 2);

                return [(int) $item->id => (string) $label];
            })
            ->toArray();

        return $opciones;
    }
}
