<?php

declare(strict_types=1);

namespace App\BusinessLogic\Facturacion\Stripe;

use App\Exceptions\StripeApiException;

final readonly class ReintentarOperacionStripe
{
    public function __construct(
        private int $maxIntentos = 3,
        private int $esperaMilisegundos = 400,
    ) {}

    public function maxIntentos(): int
    {
        return $this->maxIntentos;
    }

    /**
     * Ejecuta la operación reintentando cuando Stripe no responde.
     *
     * @template T
     *
     * @param  callable(): T  $operacion
     * @param  int  $intentosUsados  Cantidad de intentos realmente empleados.
     * @return T
     */
    public function ejecutar(callable $operacion, int &$intentosUsados)
    {
        $intentos = 0;

        while (true) {
            $intentos++;

            try {
                $resultado = $operacion();

                $intentosUsados = $intentos;

                return $resultado;
            } catch (StripeApiException $exception) {
                if ($intentos >= $this->maxIntentos) {
                    $intentosUsados = $intentos;

                    throw $exception;
                }

                usleep($this->esperaMilisegundos * 1000);
            }
        }
    }
}
