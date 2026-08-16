<?php

declare(strict_types=1);

namespace App\BusinessLogic\Promociones;

use App\Enums\Promociones\TipoReglaBeneficioCliente;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\Promociones\PromocionBeneficioRegla;

final class EvaluarReglasBeneficioCliente
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function cumple(PromocionBeneficio $beneficio, array $contexto): bool
    {
        foreach ($beneficio->reglas as $regla) {
            if ($regla->obligatoria === false) {
                continue;
            }

            if (! $this->cumpleRegla($regla, $contexto)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    private function cumpleRegla(PromocionBeneficioRegla $regla, array $contexto): bool
    {
        return match ($regla->tipo_regla) {
            TipoReglaBeneficioCliente::MontoMinimo => $this->compararNumero($this->extraerNumero($contexto['monto'] ?? null), $regla),
            TipoReglaBeneficioCliente::NochesMinimas => $this->compararNumero($this->extraerNumero($contexto['noches'] ?? null), $regla),
            TipoReglaBeneficioCliente::CategoriaHabitacion => $this->compararTexto($contexto['categoria_habitacion_id'] ?? null, $regla),
            TipoReglaBeneficioCliente::PrimerReserva => (bool) ($contexto['primera_reserva'] ?? false),
            TipoReglaBeneficioCliente::FechaNacimiento => (bool) ($contexto['es_cumpleanos'] ?? false),
            TipoReglaBeneficioCliente::UnaVezPorCliente => true,
        };
    }

    private function extraerNumero(mixed $valor): float
    {
        $numero = filter_var($valor, FILTER_VALIDATE_FLOAT);

        return $numero === false ? 0.0 : $numero;
    }

    private function compararNumero(float $valor, PromocionBeneficioRegla $regla): bool
    {
        $esperado = (float) ($regla->valor_numerico ?? 0);

        return match ($regla->operador) {
            '<=' => $valor <= $esperado,
            '=' => $valor === $esperado,
            '!=' => $valor !== $esperado,
            default => $valor >= $esperado,
        };
    }

    private function compararTexto(mixed $valor, PromocionBeneficioRegla $regla): bool
    {
        $valorActual = is_scalar($valor) ? (string) $valor : '';
        $esperado = (string) ($regla->valor_texto ?? '');

        return match ($regla->operador) {
            '!=' => $valorActual !== $esperado,
            default => $valorActual === $esperado,
        };
    }
}
