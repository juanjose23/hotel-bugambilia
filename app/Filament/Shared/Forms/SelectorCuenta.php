<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use Filament\Forms\Components\Select;

/**
 * Selector de Cuentas abiertas para formularios Filament.
 * Reemplaza el antiguo SelectorCuenta orientado a CuentaEstancia.
 * Soporta filtrar por reserva, estancia o tipo de cuenta.
 */
final class SelectorCuenta
{
    /**
     * Selector de cuentas abiertas.
     *
     * @param  array<int|string, mixed>  $extraWhere
     */
    public static function make(
        string $column = 'cuenta_id',
        ?int $reservaId = null,
        ?int $estanciaId = null,
        ?TipoCuenta $tipo = null,
        array $extraWhere = [],
        int $columnSpan = 1,
        bool $required = false,
    ): Select {
        return Select::make($column)
            ->label('Cuenta')
            ->placeholder('Seleccionar cuenta...')
            ->options(fn (): array => self::obtenerOpciones($reservaId, $estanciaId, $tipo, $extraWhere))
            ->getOptionLabelUsing(function ($value): ?string {
                if (! is_numeric($value)) {
                    return null;
                }

                $cuenta = Cuenta::with(['cliente', 'estancia.habitacion', 'reserva'])->find((int) $value);

                return $cuenta instanceof Cuenta ? self::etiqueta($cuenta) : null;
            })
            ->searchable()
            ->nullable(! $required)
            ->required($required)
            ->native(false)
            ->columnSpan($columnSpan);
    }

    /**
     * @param  array<int|string, mixed>  $extraWhere
     * @return array<int, string>
     */
    private static function obtenerOpciones(
        ?int $reservaId,
        ?int $estanciaId,
        ?TipoCuenta $tipo,
        array $extraWhere,
    ): array {
        $query = Cuenta::query()
            ->where('estado', EstadoCuenta::ABIERTA);

        if ($reservaId !== null) {
            $query->where('reserva_id', $reservaId);
        }

        if ($estanciaId !== null) {
            $query->where('estancia_id', $estanciaId);
        }

        if ($tipo !== null) {
            $query->where('tipo_cuenta', $tipo);
        }

        foreach ($extraWhere as $campo => $valor) {
            $query->where((string) $campo, $valor);
        }

        return $query
            ->with(['cliente', 'estancia.habitacion', 'reserva'])
            ->get()
            ->mapWithKeys(fn (Cuenta $cuenta): array => [
                $cuenta->id => self::etiqueta($cuenta),
            ])
            ->all();
    }

    private static function etiqueta(Cuenta $cuenta): string
    {
        $folio = $cuenta->numero_cuenta;
        $tipo = $cuenta->tipo_cuenta->getLabel();
        $saldo = number_format((float) $cuenta->saldo, 2);

        $titular = match (true) {
            $cuenta->estancia !== null && $cuenta->estancia->habitacion !== null => $cuenta->estancia->habitacion->nombre,
            $cuenta->cliente !== null => $cuenta->cliente->nombre_completo,
            $cuenta->reserva !== null && $cuenta->reserva->nombre_cliente !== null => $cuenta->reserva->nombre_cliente,
            default => null,
        };

        $partes = ["{$folio} ({$tipo})"];
        if ($titular !== null) {
            $partes[] = $titular;
        }
        $partes[] = "Saldo: C\$ {$saldo}";

        return implode(' — ', $partes);
    }
}
