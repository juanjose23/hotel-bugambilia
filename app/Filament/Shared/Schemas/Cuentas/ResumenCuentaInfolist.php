<?php

declare(strict_types=1);

namespace App\Filament\Shared\Schemas\Cuentas;

use App\Presenters\Cuentas\ResumenCuentaPresenter;
use App\Repository\Models\Cuentas\Cuenta;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\HtmlString;

final class ResumenCuentaInfolist
{
    public static function make(?Cuenta $cuenta): TextEntry
    {
        return TextEntry::make('resumen_visual')
            ->hiddenLabel()
            ->state(function () use ($cuenta): HtmlString {
                if (! $cuenta instanceof Cuenta) {
                    return new HtmlString('<div class="text-sm text-gray-500">No hay información de cuenta disponible.</div>');
                }

                $resumen = app(ResumenCuentaPresenter::class)->paraModal($cuenta);

                $simboloStr = is_string($resumen['moneda_simbolo'] ?? null) ? $resumen['moneda_simbolo'] : 'C$';
                $simbolo = e($simboloStr);
                $fmt = static fn (mixed $v): string => "{$simbolo} ".number_format(is_numeric($v) ? (float) $v : 0.0, 2);

                $clienteStr = is_string($resumen['nombre_cliente'] ?? null) ? $resumen['nombre_cliente'] : '—';
                $origenStr = is_string($resumen['origen_descripcion'] ?? null) ? $resumen['origen_descripcion'] : '—';
                $nroCuentaStr = is_string($resumen['numero_cuenta'] ?? null) ? $resumen['numero_cuenta'] : '—';

                $cliente = e($clienteStr);
                $origen = e($origenStr);
                $nroCuenta = e($nroCuentaStr);

                $totalFmt = $fmt($resumen['total'] ?? 0);
                $pagadoFmt = $fmt($resumen['total_pagado'] ?? 0);
                $saldoFmt = $fmt($resumen['saldo'] ?? 0);
                $subtotalFmt = $fmt($resumen['subtotal'] ?? 0);

                $detallesHtml = '';
                $detalles = is_array($resumen['detalles'] ?? null) ? $resumen['detalles'] : [];
                if (! empty($detalles)) {
                    $detallesRows = '';
                    foreach ($detalles as $det) {
                        if (! is_array($det)) {
                            continue;
                        }

                        $concepto = e(is_string($det['concepto'] ?? null) ? $det['concepto'] : 'Consumo');
                        $cant = is_numeric($det['cantidad'] ?? null) ? (float) $det['cantidad'] : 1.0;
                        $sub = $fmt($det['subtotal'] ?? 0);
                        $detallesRows .= "
                        <tr class='border-b border-gray-100 dark:border-gray-800/50 text-xs'>
                            <td class='py-1.5 px-2 text-gray-800 dark:text-gray-200 font-medium'>{$concepto}</td>
                            <td class='py-1.5 px-2 text-center text-gray-600 dark:text-gray-400'>{$cant}</td>
                            <td class='py-1.5 px-2 text-right font-semibold text-gray-900 dark:text-gray-100'>{$sub}</td>
                        </tr>";
                    }

                    $detallesHtml = "
                    <div class='mt-2 pt-2 border-t border-gray-200 dark:border-gray-800'>
                        <span class='text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1'>Consumos en Cuenta</span>
                        <div class='max-h-28 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950/40'>
                            <table class='w-full text-left border-collapse'>
                                <thead>
                                    <tr class='bg-gray-100 dark:bg-gray-800/80 text-[10px] text-gray-500 uppercase font-bold'>
                                        <th class='py-1 px-2'>Concepto</th>
                                        <th class='py-1 px-2 text-center'>Cant.</th>
                                        <th class='py-1 px-2 text-right'>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$detallesRows}
                                </tbody>
                            </table>
                        </div>
                    </div>";
                }

                $cargosFacturacionLineasHtml = '';
                $cargosVigentes = is_array($resumen['cargos_vigentes'] ?? null) ? $resumen['cargos_vigentes'] : [];
                if (! empty($cargosVigentes)) {
                    $subtotalNum = is_numeric($resumen['subtotal'] ?? null) ? (float) $resumen['subtotal'] : 0.0;
                    $lineas = '';
                    foreach ($cargosVigentes as $cv) {
                        if (! is_array($cv)) {
                            continue;
                        }

                        $nombre = e(is_string($cv['nombre'] ?? null) ? $cv['nombre'] : 'Cargo');
                        $valor = is_numeric($cv['valor'] ?? null) ? (float) $cv['valor'] : 0.0;
                        $esOblig = ! empty($cv['es_obligatorio']);

                        $montoCalc = round(($subtotalNum * $valor) / 100, 2);
                        $montoStr = $fmt($montoCalc);
                        $tipoTag = $esOblig ? 'Obligatorio' : 'Voluntario';

                        $lineas .= "
                        <div class='flex justify-between items-center py-1 text-xs text-gray-700 dark:text-gray-300 border-b border-dashed border-gray-100 dark:border-gray-800/60 last:border-0'>
                            <span class='font-medium'>{$nombre} <span class='text-gray-400 text-[10px]'>({$valor}% · {$tipoTag})</span></span>
                            <span class='font-semibold text-gray-900 dark:text-gray-100'>{$montoStr}</span>
                        </div>";
                    }

                    $cargosFacturacionLineasHtml = "
                    <div class='mt-2 pt-2 border-t border-gray-200 dark:border-gray-800 space-y-1'>
                        <div class='flex justify-between text-xs text-gray-500 font-medium mb-1'>
                            <span>Subtotal Consumos</span>
                            <span class='font-semibold text-gray-800 dark:text-gray-200'>{$subtotalFmt}</span>
                        </div>
                        {$lineas}
                    </div>";
                }

                $html = "
                <div class='p-3 sm:p-4 bg-gray-50 dark:bg-gray-900/70 border border-gray-200 dark:border-gray-800 rounded-xl space-y-3'>
                    <div class='flex flex-col sm:flex-row justify-between items-start sm:items-center pb-2 border-b border-gray-200 dark:border-gray-800 gap-1'>
                        <div>
                            <span class='text-[10px] font-bold text-gray-400 uppercase tracking-wider block'>Cuenta #{$nroCuenta}</span>
                            <span class='font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base'>{$cliente}</span>
                        </div>
                        <div class='text-left sm:text-right'>
                            <span class='text-xs text-gray-500 dark:text-gray-400 font-medium'>{$origen}</span>
                        </div>
                    </div>

                    {$detallesHtml}
                    {$cargosFacturacionLineasHtml}

                    <div class='grid grid-cols-3 gap-2 pt-2 border-t border-gray-200 dark:border-gray-800 text-center'>
                        <div class='bg-white dark:bg-gray-800/80 p-2 rounded-lg border border-gray-100 dark:border-gray-700/50'>
                            <span class='text-[10px] text-gray-500 dark:text-gray-400 block font-medium uppercase'>Total Factura</span>
                            <span class='font-semibold text-gray-800 dark:text-gray-200 text-xs sm:text-sm'>{$totalFmt}</span>
                        </div>
                        <div class='bg-white dark:bg-gray-800/80 p-2 rounded-lg border border-gray-100 dark:border-gray-700/50'>
                            <span class='text-[10px] text-gray-500 dark:text-gray-400 block font-medium uppercase'>Abonado</span>
                            <span class='font-semibold text-gray-600 dark:text-gray-300 text-xs sm:text-sm'>{$pagadoFmt}</span>
                        </div>
                        <div class='bg-emerald-600 text-white p-2 rounded-lg shadow-sm'>
                            <span class='text-[10px] text-emerald-100 block font-bold uppercase'>Saldo Pendiente</span>
                            <span class='font-extrabold text-xs sm:text-base'>{$saldoFmt}</span>
                        </div>
                    </div>
                </div>
                ";

                return new HtmlString($html);
            });
    }
}
