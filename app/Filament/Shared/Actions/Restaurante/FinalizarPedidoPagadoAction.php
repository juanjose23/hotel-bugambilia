<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Pedidos\CerrarPedidoMesa;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;

final readonly class FinalizarPedidoPagadoAction
{
    /** @param (\Closure(mixed): ?Pedido)|null $resolverPedido @param (\Closure(): void)|null $alFinalizar */
    public static function make(?\Closure $resolverPedido = null, ?\Closure $alFinalizar = null): Action
    {
        $resolver = fn (mixed $record = null): ?Pedido => $resolverPedido !== null ? $resolverPedido($record) : ($record instanceof Pedido ? $record : null);

        return Action::make('finalizarPedidoPagado')
            ->label('Finalizar pedido pagado')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->authorize(fn (mixed $record = null): bool => ($pedido = $resolver($record)) !== null && Gate::allows('update', $pedido))
            ->visible(fn (mixed $record = null): bool => ($pedido = $resolver($record)) !== null
                && $pedido->estado === EstadoPedido::SERVIDO && $pedido->cuenta !== null && (float) $pedido->cuenta->saldo <= 0)
            ->requiresConfirmation()
            ->modalDescription('Finaliza la atención y genera la venta de la cuenta si aún no existe. No se registra otro cobro.')
            ->action(function (mixed $record = null) use ($resolver, $alFinalizar): void {
                $pedido = $resolver($record);
                if ($pedido === null) {
                    return;
                }
                try {
                    app(CerrarPedidoMesa::class)->ejecutar($pedido, usuarioId: auth()->id() !== null ? (int) auth()->id() : null);
                    Notification::make()->title('Pedido finalizado sin otro cobro')->success()->send();
                    if ($alFinalizar !== null) {
                        $alFinalizar();
                    }
                } catch (DomainException $exception) {
                    Notification::make()->title('No se pudo finalizar')->body($exception->getMessage())->danger()->send();
                }
            });
    }
}
