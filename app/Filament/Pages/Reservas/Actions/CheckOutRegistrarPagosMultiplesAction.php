<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Actions;

use App\Enums\Cuentas\MetodoPago;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Cuentas\Cobros\RegistrarPagosMultiplesCuenta;
use App\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class CheckOutRegistrarPagosMultiplesAction
{
    public static function make(CheckOutPage $page): Action
    {
        return Action::make('registrar_pagos_multiples')
            ->label('Pago Dividido / Múltiple')
            ->icon('heroicon-o-arrows-pointing-out')
            ->color('warning')
            ->visible(fn (): bool => $page->reserva !== null && $page->saldoCuenta() > 0)
            ->schema([
                Repeater::make('pagos')
                    ->label('Desglose de Métodos de Pago')
                    ->schema([
                        Select::make('forma_pago')
                            ->label('Método')
                            ->options(MetodoPago::class)
                            ->default(MetodoPago::EFECTIVO)
                            ->required(),
                        TextInput::make('monto')
                            ->label('Monto')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        TextInput::make('referencia_transaccion')
                            ->label('Referencia')
                            ->placeholder('Ej. Voucher / Aut.'),
                        TextInput::make('propina')
                            ->label('Propina')
                            ->numeric()
                            ->default(0.0),
                        TextInput::make('observaciones')
                            ->label('Nota')
                            ->placeholder('Observación'),
                    ])
                    ->columns(5)
                    ->defaultItems(2)
                    ->minItems(1)
                    ->addActionLabel('Agregar otro método de pago'),
            ])
            ->action(function (array $data, RegistrarPagosMultiplesCuenta $registrarMultiples) use ($page): void {
                $cuenta = $page->cuentaActiva();
                if ($cuenta === null) {
                    return;
                }

                $pagosRaw = $data['pagos'] ?? [];
                /** @var array<int, array<string, mixed>> $pagosData */
                $pagosData = is_array($pagosRaw) ? array_values(array_filter($pagosRaw, 'is_array')) : [];
                if ($pagosData === []) {
                    return;
                }

                $usuarioId = auth()->id();
                $resultado = $registrarMultiples->ejecutar(
                    cuenta: $cuenta,
                    pagos: $pagosData,
                    usuarioId: is_int($usuarioId) ? $usuarioId : null,
                );

                $page->refrescarEstancia();

                $totalPagadoStr = MonedaHelper::formatear((float) $resultado['totalPagado'], $cuenta->moneda);
                $saldoRestanteStr = MonedaHelper::formatear((float) $resultado['saldoRestante'], $cuenta->moneda);

                Notification::make()
                    ->title('Pagos múltiples registrados')
                    ->body("Se procesaron {$resultado['pagos']->count()} pagos por un total de {$totalPagadoStr}. Saldo restante: {$saldoRestanteStr}")
                    ->success()
                    ->send();
            });
    }
}
