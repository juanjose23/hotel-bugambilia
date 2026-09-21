<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Schemas;

use App\Filament\Pages\Reservas\CheckOutPage;
use App\Support\MonedaHelper;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

final class CheckOutCuentaSchema
{
    /**
     * @return array<int, Component>
     */
    public static function make(CheckOutPage $page): array
    {
        return [
            Section::make('2. Balance Financiero & Pagos')
                ->icon('heroicon-o-banknotes')
                ->description('Liquidación de la cuenta, abonos aplicados y saldos pendientes.')
                ->collapsible()
                ->columns(3)
                ->schema([
                    TextEntry::make('total_cuenta')
                        ->label('Total')
                        ->state(fn (): string => MonedaHelper::formatear($page->montoCuenta('total'), $page->cuentaActiva()?->moneda)),
                    TextEntry::make('pagado_cuenta')
                        ->label('Pagado')
                        ->state(fn (): string => MonedaHelper::formatear($page->montoCuenta('total_pagado'), $page->cuentaActiva()?->moneda)),
                    TextEntry::make('saldo_cuenta')
                        ->label('Saldo')
                        ->state(fn (): string => MonedaHelper::formatear($page->saldoCuenta(), $page->cuentaActiva()?->moneda))
                        ->badge()
                        ->color(fn (): string => $page->saldoPermitido() ? 'success' : 'danger'),
                    TextEntry::make('pagos_detalle')
                        ->label('Historial de Pagos y Abonos Registrados')
                        ->html()
                        ->state(function () use ($page): string {
                            $cuenta = $page->cuentaActiva();
                            if ($cuenta === null || $cuenta->pagos->isEmpty()) {
                                return "<span class='text-gray-500 italic text-sm'>No hay pagos registrados aún en esta cuenta.</span>";
                            }

                            $html = "<div class='overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg'><table class='w-full text-sm text-left'><thead class='bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-700 dark:text-gray-300'><tr><th class='p-2'>Fecha</th><th class='p-2'>Método</th><th class='p-2'>Ref.</th><th class='p-2 text-right'>Monto</th></tr></thead><tbody class='divide-y divide-gray-100 dark:divide-gray-700'>";

                            foreach ($cuenta->pagos as $pago) {
                                $fecha = $pago->created_at->format('d/m/Y H:i');
                                $metodo = $pago->forma_pago->getLabel();
                                $ref = $pago->referencia_transaccion ?? '-';
                                $montoFormateado = MonedaHelper::formatear((float) $pago->monto, $cuenta->moneda);
                                $html .= "<tr><td class='p-2'>{$fecha}</td><td class='p-2 font-medium'>{$metodo}</td><td class='p-2 text-gray-500'>{$ref}</td><td class='p-2 text-right font-bold text-success-600'>{$montoFormateado}</td></tr>";
                            }

                            $html .= '</tbody></table></div>';

                            return $html;
                        })
                        ->columnSpanFull(),
                    Toggle::make('credito_autorizado')
                        ->label('Saldo pendiente autorizado por crédito')
                        ->helperText('Usar solo cuando exista autorización real. Si no hay autorización, primero registre el pago.')
                        ->live()
                        ->visible(fn (): bool => $page->saldoCuenta() > 0)
                        ->columnSpanFull(),
                    Textarea::make('motivo_credito')
                        ->label('Motivo de autorización')
                        ->maxLength(500)
                        ->rows(2)
                        ->visible(fn (): bool => $page->saldoCuenta() > 0 && (bool) ($page->data['credito_autorizado'] ?? false))
                        ->columnSpanFull(),
                ]),
        ];
    }
}
