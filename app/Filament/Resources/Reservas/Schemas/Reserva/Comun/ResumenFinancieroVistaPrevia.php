<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use App\Repository\Queries\Reservas\CalcularVistaPreviaFinancieraReservaQuery;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

final class ResumenFinancieroVistaPrevia
{
    /** @var list<string> */
    private const CAMPOS = [
        'tipo_reserva',
        'habitacion_id',
        'espacio_id',
        'servicio_id',
        'fecha_check_in',
        'fecha_check_out',
        'duracion_horas',
        'adultos',
        'servicios_adicionales',
        'espacios_adicionales',
        'habitaciones_adicionales',
        'items_preorden',
        'promocion_id',
        'cargos_facturacion_ids',
    ];

    /** @return array<string, mixed> */
    public static function calcular(Get $get): array
    {
        $datos = [];
        foreach (self::CAMPOS as $campo) {
            $datos[$campo] = $get($campo);
        }

        return app(CalcularVistaPreviaFinancieraReservaQuery::class)->ejecutar($datos);
    }

    public static function html(Get $get, bool $compacto = false): HtmlString
    {
        $resumen = self::calcular($get);
        $dinero = static fn (float $monto): string => 'C$ '.number_format($monto, 2);

        $duracionStr = is_string($resumen['duracion'] ?? null) ? $resumen['duracion'] : '—';
        $tarifaBase = is_numeric($resumen['tarifa_base'] ?? null) ? (float) $resumen['tarifa_base'] : 0.0;
        $subtotal = is_numeric($resumen['subtotal'] ?? null) ? (float) $resumen['subtotal'] : 0.0;
        $descuento = is_numeric($resumen['descuento'] ?? null) ? (float) $resumen['descuento'] : 0.0;
        $totalCargos = is_numeric($resumen['total_cargos'] ?? null) ? (float) $resumen['total_cargos'] : 0.0;
        $total = is_numeric($resumen['total'] ?? null) ? (float) $resumen['total'] : 0.0;
        $abono50 = is_numeric($resumen['abono_50'] ?? null) ? (float) $resumen['abono_50'] : 0.0;

        /** @var array<int, array{nombre: string, monto: float, obligatorio: bool}> $cargosList */
        $cargosList = is_array($resumen['cargos'] ?? null) ? $resumen['cargos'] : [];

        $cargos = $cargosList === []
            ? '<div class="text-sm text-gray-500 dark:text-gray-400">No hay cargos de facturación aplicables.</div>'
            : implode('', array_map(static fn (array $cargo): string => sprintf(
                '<div class="flex items-center justify-between gap-4 py-1.5 text-sm"><span class="text-gray-600 dark:text-gray-300">%s%s</span><span class="font-medium text-gray-900 dark:text-white">%s</span></div>',
                e($cargo['nombre']),
                $cargo['obligatorio'] ? ' <span class="text-xs text-gray-400">(obligatorio)</span>' : '',
                $dinero((float) $cargo['monto']),
            ), $cargosList));

        if ($compacto) {
            return new HtmlString(sprintf(
                '<div class="w-full space-y-3 font-sans">'.
                    '<div class="w-full grid grid-cols-2 gap-2">%s</div>'.
                    '<div class="w-full relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 p-4 text-white shadow-md shadow-emerald-950/20 dark:from-emerald-700 dark:to-teal-900 flex items-center justify-between gap-3">'.
                        '<div>'.
                            '<div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-100/90">Total definitivo</div>'.
                            '<div class="text-[11px] font-medium text-emerald-200/80">I.V.A y cargos incl.</div>'.
                        '</div>'.
                        '<div class="text-2xl font-black tracking-tight text-white">%s</div>'.
                    '</div>'.
                    '<div class="w-full rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-3.5 dark:border-emerald-500/20 dark:bg-emerald-500/5 flex items-center justify-between gap-3">'.
                        '<div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Abono sugerido (50 %%)</div>'.
                        '<div class="text-lg font-black text-emerald-800 dark:text-emerald-300">%s</div>'.
                    '</div>'.
                '</div>',
                implode('', [
                    self::tarjetaResumen('Estancia', e($duracionStr), true),
                    self::tarjetaResumen('Tarifa base', $dinero($tarifaBase), true),
                    self::tarjetaResumen('Subtotal', $dinero($subtotal), true),
                    self::tarjetaResumen('Descuento', '- '.$dinero($descuento), true),
                ]),
                $dinero($total),
                $dinero($abono50),
            ));
        }

        return new HtmlString(sprintf(
            '<div class="w-full space-y-4 font-sans">'.
                '<div class="w-full grid grid-cols-2 gap-3 lg:grid-cols-4">%s</div>'.
                '<div class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 p-4 dark:border-white/10 dark:bg-white/5">'.
                    '<div class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Desglose de cargos de facturación</div>'.
                    '%s'.
                    '<div class="mt-3 flex items-center justify-between border-t border-gray-200/80 pt-3 text-sm dark:border-white/10">'.
                        '<span class="font-medium text-gray-600 dark:text-gray-300">Total cargos</span>'.
                        '<strong class="font-bold text-gray-900 dark:text-white">%s</strong>'.
                    '</div>'.
                '</div>'.
                '<div class="w-full grid gap-3 sm:grid-cols-2">'.
                    '<div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 p-4 text-white shadow-md shadow-emerald-950/20 dark:from-emerald-700 dark:to-teal-900 flex items-center justify-between gap-3">'.
                        '<div>'.
                            '<div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-100/90">Total definitivo</div>'.
                            '<div class="text-[11px] font-medium text-emerald-200/80">I.V.A y cargos incl.</div>'.
                        '</div>'.
                        '<div class="text-2xl font-black tracking-tight text-white">%s</div>'.
                    '</div>'.
                    '<div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/5 flex items-center justify-between gap-3">'.
                        '<div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Abono automático (50 %%)</div>'.
                        '<div class="text-2xl font-black tracking-tight text-emerald-800 dark:text-emerald-300">%s</div>'.
                    '</div>'.
                '</div>'.
            '</div>',
            implode('', [
                self::tarjetaResumen('Tiempo / Estancia', e($duracionStr)),
                self::tarjetaResumen('Tarifa base', $dinero($tarifaBase)),
                self::tarjetaResumen('Subtotal', $dinero($subtotal)),
                self::tarjetaResumen('Descuento', '- '.$dinero($descuento)),
            ]),
            $cargos,
            $dinero($totalCargos),
            $dinero($total),
            $dinero($abono50),
        ));
    }

    private static function tarjetaResumen(string $etiqueta, string $valor, bool $compacto = false): string
    {
        $padding = $compacto ? 'p-2.5' : 'p-3.5';

        return sprintf(
            '<div class="w-full rounded-2xl border border-gray-200/80 bg-gray-50/80 %s transition-colors dark:border-white/10 dark:bg-white/5 flex flex-col justify-between">'.
                '<div class="text-[10px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">%s</div>'.
                '<div class="mt-1 text-sm font-bold text-gray-900 dark:text-white">%s</div>'.
            '</div>',
            $padding,
            e($etiqueta),
            $valor,
        );
    }
}
