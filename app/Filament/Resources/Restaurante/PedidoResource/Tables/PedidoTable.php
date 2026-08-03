<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Tables;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Cocina\AnularTodosItemsActivos;
use App\Interactors\Restaurante\Cocina\MarcarTodosItemsServidos;
use App\Interactors\Restaurante\Cocina\ReenviarItemsPendientesACocina;
use App\Interactors\Restaurante\Pedidos\CancelarPedido;
use App\Interactors\Restaurante\Pedidos\CargarPedidoACuenta;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PedidoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('items'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('mesa.nombre')->label('Mesa')->searchable()->sortable(),
                TextColumn::make('mesero.persona.nombre_completo')->label('Mesero')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estado')->label('Estado')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EstadoPedido ? $state->getLabel() : (is_string($state) ? EstadoPedido::tryFrom($state)?->getLabel() ?? $state : ''))
                    ->color(fn (mixed $state): string => $state instanceof EstadoPedido ? $state->getColor() : (is_string($state) ? EstadoPedido::tryFrom($state)?->getColor() ?? 'gray' : 'gray')),
                TextColumn::make('subtotal')->label('Subtotal')->money('NIO')->sortable(),
                TextColumn::make('created_at')->label('Creado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoPedido::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('enviarCocina')
                        ->label('Enviar a Cocina')
                        ->icon('heroicon-o-fire')
                        ->color('warning')
                        ->visible(fn (Pedido $record): bool => in_array($record->estado, [EstadoPedido::ABIERTO, EstadoPedido::SERVIDO], true)
                            && $record->items->contains(fn ($item) => $item->estado === EstadoItemPedido::PENDIENTE))
                        ->requiresConfirmation()
                        ->modalHeading('Enviar a Cocina')
                        ->modalDescription('¿Enviar o reenviar items pendientes a la cocina para preparación?')
                        ->action(function (Pedido $record): void {
                            try {
                                if ($record->estado === EstadoPedido::SERVIDO) {
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
                            EstadoPedido::CARGADO_A_HABITACION,
                            EstadoPedido::CANCELADO,
                        ], true))
                        ->schema([
                            Select::make('cuenta_id')
                                ->label('Cuenta Abierta')
                                ->options(fn (): array => Cuenta::query()
                                    ->whereIn('estado', [EstadoCuenta::ABIERTA, EstadoCuenta::SOLICITADA])
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
}
