<?php

declare(strict_types=1);

namespace App\Repository\Queries\Clientes;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BuscarClientesQuery
{
    /**
     * Busca clientes por nombre, apellido, razón social (persona jurídica),
     * identificación/RUC, teléfono, email y tipo de cliente.
     *
     * @return Collection<int, Cliente>
     */
    public function ejecutar(string $busqueda = '', int $limite = 50): Collection
    {
        $query = Cliente::query()
            ->with(['persona.personaNatural', 'persona.personaJuridica', 'tipoCliente']);

        $termino = trim($busqueda);

        if ($termino !== '') {
            $tokens = preg_split('/\s+/', $termino, flags: PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($tokens as $token) {
                $patron = "%{$token}%";

                $query->where(function (Builder $q) use ($token, $patron): void {
                    if (is_numeric($token)) {
                        $q->orWhere('id', (int) $token)
                            ->orWhere('persona_id', (int) $token);
                    }

                    // Persona Natural: Nombres, Teléfono, Email
                    $q->orWhereHas('persona', function (Builder $pq) use ($patron): void {
                        $pq->where('primer_nombre', 'ilike', $patron)
                            ->orWhere('segundo_nombre', 'ilike', $patron)
                            ->orWhere('telefono', 'ilike', $patron)
                            ->orWhere('email', 'ilike', $patron);
                    });

                    // Apellidos e Identificación Natural
                    $q->orWhereHas('persona.personaNatural', function (Builder $pnq) use ($patron): void {
                        $pnq->where('primer_apellido', 'ilike', $patron)
                            ->orWhere('segundo_apellido', 'ilike', $patron)
                            ->orWhere('numero_identificacion', 'ilike', $patron);
                    });

                    // Persona Jurídica: Razón Social, RUC, Nombre Comercial
                    $q->orWhereHas('persona.personaJuridica', function (Builder $pjq) use ($patron): void {
                        $pjq->where('razon_social', 'ilike', $patron)
                            ->orWhere('numero_identificacion', 'ilike', $patron)
                            ->orWhere('nombre_comercial', 'ilike', $patron);
                    });

                    // Tipo de Cliente / Catálogo (ej: VIP, General, Regular, Corporativo)
                    $q->orWhereHas('tipoCliente', function (Builder $tcq) use ($patron): void {
                        $tcq->where('nombre', 'ilike', $patron)
                            ->orWhere('codigo', 'ilike', $patron);
                    });
                });
            }
        }

        return $query->limit($limite)->get();
    }

    /**
     * Retorna array formateado [persona_id => 'Nombre (Tipo) · Identificación'] listo para componentes de Filament Select.
     *
     * Cuentas y pedidos guardan cliente_id como FK a personas, no a clientes.
     *
     * @return array<int, string>
     */
    public function paraSelect(string $busqueda = '', int $limite = 50): array
    {
        return $this->ejecutar($busqueda, $limite)
            ->mapWithKeys(function (Cliente $cliente): array {
                $persona = $cliente->persona;
                $nombre = $persona instanceof Persona ? app(ObtenerNombrePersona::class)->ejecutar($persona) : "Cliente #{$cliente->id}";
                $tipo = $cliente->tipoCliente?->nombre;
                $tipoStr = filled($tipo) ? " ({$tipo})" : '';

                $identificacion = null;
                if ($persona instanceof Persona) {
                    $natural = $persona->personaNatural;
                    $juridica = $persona->personaJuridica;
                    $identificacion = $natural !== null
                        ? $natural->numero_identificacion
                        : ($juridica !== null ? $juridica->numero_identificacion : null);
                }
                $identStr = filled($identificacion) ? " · {$identificacion}" : '';

                return [
                    $persona instanceof Persona ? $persona->id : $cliente->id => "{$nombre}{$tipoStr}{$identStr}",
                ];
            })
            ->all();
    }
}
