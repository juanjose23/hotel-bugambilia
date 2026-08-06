<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Reservas\TipoPagoReserva;
use App\Repository\Queries\Cuentas\ObtenerCargosFacturacionReservaQuery;
use App\Repository\Queries\Cuentas\ObtenerCuentaReservaQuery;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Monedas\ObtenerMonedasQuery;
use App\Repository\Queries\Reservas\CalcularVistaPreviaFinancieraReservaQuery;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

final class ResumenFinancieroYAbonoSeccion
{
    public static function make(): Section
    {
        return Section::make('Resumen financiero y forma de pago')
            ->columnSpanFull()
            ->icon(Heroicon::Banknotes)
            ->description('El total definitivo incluirá descuentos, servicios adicionales y cargos de facturación. Puede registrar el 50 % o pagar la reserva completa.')
            ->columns(1)
            ->schema([
                TextEntry::make('resumen_financiero_automatico')
                    ->label('Cálculo automático')
                    ->helperText(function (Get $get): HtmlString {
                        $datos = [];
                        foreach (['tipo_reserva', 'habitacion_id', 'espacio_id', 'servicio_id', 'fecha_check_in', 'fecha_check_out', 'duracion_horas', 'adultos', 'servicios_adicionales', 'espacios_adicionales', 'items_preorden', 'promocion_id', 'cargos_facturacion_ids'] as $campo) {
                            $datos[$campo] = $get($campo);
                        }
                        $resumen = app(CalcularVistaPreviaFinancieraReservaQuery::class)->ejecutar($datos);
                        $dinero = static fn (float $monto): string => 'C$ '.number_format($monto, 2);
                        $cargos = $resumen['cargos'] === []
                            ? '<div class="text-sm text-gray-500 dark:text-gray-400">No hay cargos de facturación aplicables.</div>'
                            : implode('', array_map(static fn (array $cargo): string => sprintf(
                                '<div class="flex items-center justify-between gap-4 py-1.5 text-sm"><span class="text-gray-600 dark:text-gray-300">%s%s</span><span class="font-medium text-gray-900 dark:text-white">%s</span></div>',
                                e($cargo['nombre']),
                                $cargo['obligatorio'] ? ' <span class="text-xs text-gray-400">(obligatorio)</span>' : '',
                                $dinero($cargo['monto']),
                            ), $resumen['cargos']));

                        return new HtmlString(sprintf(
                            '<div class="space-y-4"><div class="grid grid-cols-2 gap-3 lg:grid-cols-4">%s</div><div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5"><div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Desglose de cargos de facturación</div>%s<div class="mt-2 flex items-center justify-between border-t border-gray-200 pt-3 text-sm dark:border-white/10"><span>Total cargos</span><strong>%s</strong></div></div><div class="grid gap-3 sm:grid-cols-2"><div class="rounded-xl bg-gray-900 p-4 text-white dark:bg-white dark:text-gray-950"><div class="text-xs opacity-70">Total definitivo</div><div class="mt-1 text-2xl font-semibold">%s</div></div><div class="rounded-xl border border-gray-300 p-4 dark:border-white/20"><div class="text-xs text-gray-500 dark:text-gray-400">Abono automático del 50 %%</div><div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">%s</div></div></div></div>',
                            implode('', [
                                self::tarjetaResumen('Tiempo / Estancia', e($resumen['duracion'])),
                                self::tarjetaResumen('Tarifa base', $dinero($resumen['tarifa_base'])),
                                self::tarjetaResumen('Subtotal', $dinero($resumen['subtotal'])),
                                self::tarjetaResumen('Descuento', '- '.$dinero($resumen['descuento'])),
                            ]),
                            $cargos,
                            $dinero($resumen['total_cargos']),
                            $dinero($resumen['total']),
                            $dinero($resumen['abono_50']),
                        ));
                    })
                    ->columnSpanFull(),

                Section::make('Cobro inicial de la reserva')
                    ->description('Seleccione cómo pagará el cliente. El monto se calcula automáticamente y no puede modificarse manualmente.')
                    ->icon(Heroicon::CreditCard)
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        ToggleButtons::make('tipo_pago_reserva')
                            ->label('¿Cuánto pagará ahora?')
                            ->options(TipoPagoReserva::options())
                            ->default(TipoPagoReserva::ABONO_50->value)
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione si registrará la reserva sin pago, con abono del 50 % o con pago completo.',
                            ])
                            ->live()
                            ->inline()
                            ->columnSpanFull()
                            ->helperText('Para un abono se sugiere el 50 %, pero puede ingresar el monto realmente recibido. El pago completo debe coincidir con el total.')
                            ->visible(fn (Get $get, string $operation, $record): bool => self::mostrarCobroInicial($get, $operation, $record)),

                        TextInput::make('monto_pago_reserva')
                            ->label('Monto recibido')
                            ->prefix(function (Get $get): string {
                                $monedaId = is_numeric($get('moneda_id')) ? (int) $get('moneda_id') : null;
                                $moneda = app(ObtenerMonedasQuery::class)->ejecutar()->firstWhere('id', $monedaId);

                                return $moneda !== null ? ($moneda->simbolo ?: $moneda->codigo) : 'C$';
                            })
                            ->prefixIcon(Heroicon::Banknotes)
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->required(fn (Get $get): bool => $get('tipo_pago_reserva') !== TipoPagoReserva::SIN_PAGO->value)
                            ->validationMessages([
                                'required' => 'Ingrese el monto recibido del cliente.',
                                'numeric' => 'El monto recibido debe ser un número válido.',
                                'min' => 'El monto recibido debe ser mayor que cero.',
                            ])
                            ->placeholder(function (Get $get, string $operation, $record): string {
                                $montoBase = self::montoSugeridoPago($get, $operation, $record);
                                $monedaId = is_numeric($get('moneda_id')) ? (int) $get('moneda_id') : null;
                                $monto = app(ConvertirMoneda::class)->desdeBase($montoBase, $monedaId);

                                return number_format($monto, 2, '.', '');
                            })
                            ->helperText(fn (Get $get): string => $get('tipo_pago_reserva') === TipoPagoReserva::PAGO_COMPLETO->value
                                ? 'Ingrese el total indicado para dejar la reserva completamente pagada.'
                                : 'En edición se sugiere solo el faltante para que el pago acumulado cubra el 50 % del total.')
                            ->visible(fn (Get $get, string $operation, $record): bool => self::mostrarCobroInicial($get, $operation, $record)
                                && $get('tipo_pago_reserva') !== TipoPagoReserva::SIN_PAGO->value),

                        Select::make('moneda_id')
                            ->label('Moneda del pago')
                            ->options(fn (): array => app(ObtenerMonedasQuery::class)->ejecutar()
                                ->mapWithKeys(fn ($moneda): array => [$moneda->id => "$moneda->codigo — $moneda->nombre"])
                                ->all())
                            ->default(fn (): ?int => app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar()?->id)
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione la moneda en la que se cobrará la reserva.',
                            ])
                            ->live()
                            ->native(false)
                            ->visible(fn (Get $get, string $operation, $record): bool => self::mostrarCobroInicial($get, $operation, $record)
                                && $get('tipo_pago_reserva') !== TipoPagoReserva::SIN_PAGO->value),

                        Select::make('metodo_pago_reserva')
                            ->label('Forma de pago')
                            ->options(MetodoPago::options())
                            ->default(MetodoPago::EFECTIVO->value)
                            ->required(fn (Get $get): bool => $get('tipo_pago_reserva') !== TipoPagoReserva::SIN_PAGO->value)
                            ->validationMessages([
                                'required' => 'Seleccione la forma de pago utilizada por el cliente.',
                            ])
                            ->visible(fn (Get $get, string $operation, $record): bool => self::mostrarCobroInicial($get, $operation, $record)
                                && $get('tipo_pago_reserva') !== TipoPagoReserva::SIN_PAGO->value)
                            ->native(false),

                        TextInput::make('referencia_pago_reserva')
                            ->label('N° Referencia / Comprobante')
                            ->placeholder('Ej: Voucher #9876, Transf. #1234')
                            ->maxLength(100)
                            ->visible(fn (Get $get, string $operation, $record): bool => self::mostrarCobroInicial($get, $operation, $record)
                                && $get('tipo_pago_reserva') !== TipoPagoReserva::SIN_PAGO->value),
                    ])
                    ->visible(fn (Get $get, string $operation, $record): bool => self::mostrarCobroInicial($get, $operation, $record)),

                CheckboxList::make('cargos_facturacion_ids')
                    ->label('Cargos de facturación adicionales')
                    ->options(fn (): array => app(ObtenerCargosFacturacionReservaQuery::class)->ejecutar()
                        ->reject(fn ($cargo): bool => $cargo->obligatorio)
                        ->mapWithKeys(fn ($cargo): array => [$cargo->id => sprintf(
                            '%s (%s)',
                            $cargo->nombre,
                            $cargo->modo_calculo === ModoCargo::Porcentaje
                                ? number_format((float) $cargo->valor, 2).' %'
                                : number_format((float) $cargo->valor, 2),
                        )])
                        ->all())
                    ->descriptions(fn (): array => app(ObtenerCargosFacturacionReservaQuery::class)->ejecutar()
                        ->reject(fn ($cargo): bool => $cargo->obligatorio)
                        ->mapWithKeys(fn ($cargo): array => [
                            $cargo->id => 'Se calculará sobre la base configurada y aparecerá separado en la cuenta.',
                        ])
                        ->all())
                    ->columns()
                    ->bulkToggleable()
                    ->live()
                    ->visibleOn('create')
                    ->columnSpanFull(),
            ]);
    }

    private static function tarjetaResumen(string $etiqueta, string $valor): string
    {
        return sprintf(
            '<div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5"><div class="text-xs text-gray-500 dark:text-gray-400">%s</div><div class="mt-1 font-semibold text-gray-950 dark:text-white">%s</div></div>',
            e($etiqueta),
            $valor,
        );
    }

    /** @return array{total: float} */
    private static function calcularResumen(Get $get): array
    {
        $datos = [];
        foreach (['tipo_reserva', 'habitacion_id', 'espacio_id', 'servicio_id', 'fecha_check_in', 'fecha_check_out', 'duracion_horas', 'adultos', 'servicios_adicionales', 'espacios_adicionales', 'items_preorden', 'promocion_id', 'cargos_facturacion_ids'] as $campo) {
            $datos[$campo] = $get($campo);
        }

        return app(CalcularVistaPreviaFinancieraReservaQuery::class)->ejecutar($datos);
    }

    private static function mostrarCobroInicial(Get $get, string $operation, mixed $record): bool
    {
        if ($operation === 'create') {
            return true;
        }

        if ($operation !== 'edit' || ! is_object($record) || ! isset($record->id) || ! is_numeric($record->id)) {
            return false;
        }

        if (self::montoFaltante50($get, $record) > 0) {
            return true;
        }

        return app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $record->id) === null;
    }

    private static function montoSugeridoPago(Get $get, string $operation, mixed $record): float
    {
        $resumen = self::calcularResumen($get);
        $valor = $get('tipo_pago_reserva');
        $tipoPago = is_string($valor) ? TipoPagoReserva::tryFrom($valor) : null;

        if ($operation === 'edit' && $record !== null && ($tipoPago ?? TipoPagoReserva::ABONO_50) === TipoPagoReserva::ABONO_50) {
            return self::montoFaltante50($get, $record);
        }

        return ($tipoPago ?? TipoPagoReserva::ABONO_50)->monto($resumen['total']);
    }

    private static function montoFaltante50(Get $get, mixed $record): float
    {
        if (! is_object($record)) {
            return 0.0;
        }

        $resumen = self::calcularResumen($get);
        $pagado = (float) ($record->total_pagado ?? 0);

        return round(max(0.0, ((float) $resumen['total'] * 0.5) - $pagado), 2);
    }
}
