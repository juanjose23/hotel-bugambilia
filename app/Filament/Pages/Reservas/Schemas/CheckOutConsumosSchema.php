<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Schemas;

use App\Filament\Pages\Reservas\CheckOutPage;
use App\Support\MonedaHelper;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

final class CheckOutConsumosSchema
{
    /**
     * @return array<int, Component>
     */
    public static function make(CheckOutPage $page): array
    {
        return [
            Section::make('1. Folio de Consumos & Servicios')
                ->icon('heroicon-o-clipboard-document-list')
                ->description('Revise y valide los consumos cargados a la habitación por hospedaje, minibar, restaurante o spa.')
                ->collapsible()
                ->schema([
                    Hidden::make('reserva_id'),
                    Hidden::make('estancia_id'),
                    TextEntry::make('cuenta_numero')
                        ->label('Cuenta')
                        ->state(fn (): string => $page->cuentaActiva()->numero_cuenta ?? 'Sin cuenta'),
                    TextEntry::make('total_cargos')
                        ->label('Total cargos')
                        ->state(fn (): string => MonedaHelper::formatear($page->montoCuenta('total'), $page->cuentaActiva()?->moneda)),
                    TextEntry::make('consumos_estado')
                        ->label('Validación de consumos')
                        ->state(fn (): string => (bool) ($page->data['consumos_revisados'] ?? false) ? 'Consumos revisados' : 'Pendiente de revisión final')
                        ->badge()
                        ->color(fn (): string => (bool) ($page->data['consumos_revisados'] ?? false) ? 'success' : 'warning'),
                    Toggle::make('consumos_revisados')
                        ->label('Consumos finales revisados')
                        ->helperText('Confirma que recepción revisó hospedaje, restaurante, minibar, lavandería y cargos finales reportados.')
                        ->live()
                        ->columnSpanFull(),
                    TextEntry::make('cargos_detalle')
                        ->label('Desglose de Cargos en Folio')
                        ->html()
                        ->state(function () use ($page): string {
                            $cuenta = $page->cuentaActiva();
                            if ($cuenta === null || $cuenta->detalles->isEmpty()) {
                                return "<span class='text-gray-500 italic text-sm'>No hay consumos registrados en este folio.</span>";
                            }

                            $html = "<div class='overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg'><table class='w-full text-sm text-left'><thead class='bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-700 dark:text-gray-300'><tr><th class='p-2'>Concepto</th><th class='p-2 text-center'>Cant.</th><th class='p-2 text-right'>Precio</th><th class='p-2 text-right'>Total</th></tr></thead><tbody class='divide-y divide-gray-100 dark:divide-gray-700'>";

                            foreach ($cuenta->detalles as $detalle) {
                                $precioUnitario = MonedaHelper::formatear((float) $detalle->precio_unitario, $cuenta->moneda);
                                $total = MonedaHelper::formatear((float) $detalle->total, $cuenta->moneda);
                                $html .= "<tr><td class='p-2 font-medium'>{$detalle->concepto}</td><td class='p-2 text-center'>{$detalle->cantidad}</td><td class='p-2 text-right'>{$precioUnitario}</td><td class='p-2 text-right font-bold'>{$total}</td></tr>";
                            }

                            $html .= '</tbody></table></div>';

                            return $html;
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }
}
