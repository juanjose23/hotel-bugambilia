<?php

declare(strict_types=1);

namespace App\Repository\Queries\Promociones;

use App\BusinessLogic\Promociones\EvaluarReglasBeneficioCliente;
use App\Enums\Promociones\TipoReglaBeneficioCliente;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Promociones\PromocionBeneficio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

final readonly class ObtenerBeneficiosClienteElegiblesQuery
{
    public function __construct(
        private EvaluarReglasBeneficioCliente $evaluarReglas,
    ) {}

    /**
     * @param  array<string, mixed>  $contexto
     * @return Collection<int, PromocionBeneficio>
     */
    public function paraCliente(Cliente $cliente, array $contexto = []): Collection
    {
        if (
            ! Schema::hasTable('promocion_beneficios')
            || ! Schema::hasTable('promocion_beneficio_reglas')
            || ! Schema::hasTable('promocion_beneficio_usos')
        ) {
            return new Collection;
        }

        $beneficios = PromocionBeneficio::query()
            ->with(['reglas', 'segmentoCliente'])
            ->vigentes()
            ->where(fn ($query) => $query
                ->whereNull('segmento_cliente_id')
                ->orWhere('segmento_cliente_id', $cliente->catalogo_id)
            )
            ->latest()
            ->get();

        return $beneficios
            ->filter(fn (PromocionBeneficio $beneficio): bool => $this->cumpleLimiteUso($beneficio, $cliente))
            ->filter(fn (PromocionBeneficio $beneficio): bool => $this->evaluarReglas->cumple($beneficio, $contexto))
            ->values();
    }

    private function cumpleLimiteUso(PromocionBeneficio $beneficio, Cliente $cliente): bool
    {
        if ($beneficio->limite_usos_por_cliente === null) {
            return true;
        }

        $limitaUnaVez = $beneficio->reglas
            ->contains(fn ($regla): bool => $regla->tipo_regla === TipoReglaBeneficioCliente::UnaVezPorCliente);

        $limite = $limitaUnaVez ? 1 : $beneficio->limite_usos_por_cliente;

        return $beneficio->usos()
            ->where('cliente_id', $cliente->id)
            ->count() < $limite;
    }
}
