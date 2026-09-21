<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Cuentas\AplicarDescuentoCuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Support\MonedaHelper;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Throwable;

final class AplicarDescuentoPedidoAction
{
    /**
     * @param  (Closure(): void)|null  $onSuccess
     */
    public static function make(?Closure $onSuccess = null): Action
    {
        return Action::make('aplicarDescuento')
            ->label('Aplicar Descuento')
            ->icon(Heroicon::CurrencyDollar)
            ->color('danger')
            ->modalHeading('Aplicar Descuento a Pedido / Comanda')
            ->modalWidth('lg')
            ->extraAttributes(['dusk' => 'aplicar-descuento'])
            ->modalSubmitActionLabel('Aplicar Descuento')
            ->fillForm(fn (array $arguments) => [
                'pedido_id' => ! empty($arguments['pedido_id']) ? (int) $arguments['pedido_id'] : null,
            ])
            ->schema([
                Select::make('pedido_id')
                    ->label('Pedido / Comanda')
                    ->placeholder('Seleccionar pedido...')
                    ->options(function (): array {
                        return Pedido::query()
                            ->with('mesa')
                            ->whereNotIn('estado', [
                                EstadoPedido::PAGADO,
                                EstadoPedido::CARGADO_A_HABITACION,
                                EstadoPedido::CANCELADO,
                            ])
                            ->latest('id')
                            ->take(50)
                            ->get()
                            ->mapWithKeys(function (Pedido $p): array {
                                $mesaNombre = $p->mesa ? 'Mesa '.$p->mesa->nombre : 'Sin mesa';
                                $subtotal = MonedaHelper::formatear((float) $p->subtotal, $p->cuenta?->moneda);

                                return [$p->id => "{$p->codigo} — {$mesaNombre} ({$subtotal})"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->extraAttributes(['dusk' => 'descuento-pedido']),

                Grid::make(2)
                    ->schema([
                        TextInput::make('descuento_porcentaje')
                            ->label('Descuento (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->placeholder('Ej. 10')
                            ->extraAttributes(['dusk' => 'descuento-porcentaje']),

                        TextInput::make('descuento_monto')
                            ->label('Descuento (Monto '.MonedaHelper::simbolo().')')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->placeholder('Ej. 50.00')
                            ->extraAttributes(['dusk' => 'descuento-monto']),
                    ]),

                TextInput::make('motivo')
                    ->label('Motivo del Descuento')
                    ->placeholder('Ej. Promoción / Cortesía / Queja del cliente...')
                    ->maxLength(255)
                    ->extraAttributes(['dusk' => 'descuento-motivo']),
            ])
            ->action(function (array $data) use ($onSuccess): void {
                try {
                    $pedidoId = (int) ($data['pedido_id'] ?? 0);
                    $porcentaje = is_numeric($data['descuento_porcentaje'] ?? null) ? (float) $data['descuento_porcentaje'] : 0.0;
                    $monto = is_numeric($data['descuento_monto'] ?? null) ? (float) $data['descuento_monto'] : 0.0;
                    $motivo = ! empty($data['motivo']) ? (string) $data['motivo'] : null;
                    $userId = auth()->id() !== null ? (int) auth()->id() : null;

                    app(AplicarDescuentoCuenta::class)->ejecutar(
                        pedidoId: $pedidoId,
                        descuentoPorcentaje: $porcentaje,
                        descuentoMonto: $monto,
                        motivo: $motivo,
                        usuarioId: $userId
                    );

                    Notification::make()
                        ->title('Descuento aplicado correctamente')
                        ->success()
                        ->send();

                    if ($onSuccess !== null) {
                        $onSuccess();
                    }
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Error al aplicar descuento')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
