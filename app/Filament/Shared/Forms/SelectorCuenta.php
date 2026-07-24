<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Repository\Models\Estancias\CuentaEstancia;
use Filament\Forms\Components\Select;

class SelectorCuenta
{
    /**
     * Selector de cuentas de estancia abiertas.
     *
     * @param  array<int|string, mixed>  $extraWhere
     */
    public static function make(
        string $column = 'cuenta_estancia_id',
        ?int $reservaId = null,
        ?int $estanciaId = null,
        array $extraWhere = [],
        int $columnSpan = 1,
        bool $required = false,
    ): Select {
        return Select::make($column)
            ->label('Cuenta de Estancia')
            ->placeholder('Seleccionar cuenta...')
            ->options(fn (): array => self::obtenerOpciones($reservaId, $estanciaId, $extraWhere))
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
    private static function obtenerOpciones(?int $reservaId, ?int $estanciaId, array $extraWhere): array
    {
        $query = CuentaEstancia::query()
            ->where('estado', EstadoCuentaEstancia::ABIERTA);

        if ($reservaId !== null) {
            $query->where('reserva_id', $reservaId);
        }

        if ($estanciaId !== null) {
            $query->where('estancia_id', $estanciaId);
        }

        foreach ($extraWhere as $campo => $valor) {
            $query->where((string) $campo, $valor);
        }

        return $query
            ->with(['estancia.habitacion', 'reserva', 'cuentaable'])
            ->get()
            ->mapWithKeys(fn (CuentaEstancia $cuenta): array => [
                $cuenta->id => self::etiqueta($cuenta),
            ])
            ->all();
    }

    private static function etiqueta(CuentaEstancia $cuenta): string
    {
        $folio = $cuenta->numero_cuenta ?? $cuenta->numero_folio;
        $tipo = $cuenta->tipo_titular->getLabel();
        $saldo = number_format((float) $cuenta->saldo, 2);

        $titular = match (true) {
            $cuenta->estancia !== null && $cuenta->estancia->habitacion !== null => $cuenta->estancia->habitacion->nombre,
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
