<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Promociones;

use App\Enums\Promociones\EstadoUsoBeneficioCliente;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\Promociones\PromocionBeneficioUso;

final class RegistrarUsoBeneficioCliente
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function registrar(PromocionBeneficio $beneficio, Cliente $cliente, array $datos = []): PromocionBeneficioUso
    {
        return PromocionBeneficioUso::create([
            'beneficio_id' => $beneficio->id,
            'cliente_id' => $cliente->id,
            'reserva_id' => $datos['reserva_id'] ?? null,
            'cuenta_id' => $datos['cuenta_id'] ?? null,
            'venta_id' => $datos['venta_id'] ?? null,
            'monto_descuento' => $datos['monto_descuento'] ?? 0,
            'estado' => $datos['estado'] ?? EstadoUsoBeneficioCliente::Aplicado,
            'usado_en' => $datos['usado_en'] ?? now(),
            'metadata' => $datos['metadata'] ?? null,
        ]);
    }
}
