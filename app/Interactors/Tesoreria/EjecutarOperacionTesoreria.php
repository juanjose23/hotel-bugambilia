<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final readonly class EjecutarOperacionTesoreria
{
    public function __construct(private TesoreriaRepositorio $repo, private ValidarOperacionTesoreria $validar) {}

    /**
     * @param  array<array-key,scalar|null>  $datos
     * @param  \Closure(OperacionTesoreria):array<string,int|string|float|null>  $accion
     * @return array<string,mixed>
     */
    public function ejecutar(int $actor, string $tipo, string $clave, array $datos, \Closure $accion): array
    {
        $this->validar->clave($clave);
        $huella = hash('sha256', json_encode([$actor, $tipo, $datos], JSON_THROW_ON_ERROR));

        try {
            return DB::transaction(function () use ($actor, $tipo, $clave, $huella, $accion): array {
                // Serialize the same request key before checking it, including requests against different funds.
                if (DB::getDriverName() === 'pgsql') {
                    DB::select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', [$clave]);
                }
                $o = $this->repo->operacion($clave);
                if ($o !== null) {
                    if ($o->huella !== $huella) {
                        throw new DomainException('La referencia de operación ya se usó con otros datos.');
                    }

                    return $o->resultado ?? [];
                }
                $o = $this->repo->crearOperacion(['clave' => $clave, 'huella' => $huella, 'usuario_id' => $actor, 'tipo' => $tipo]);
                $resultado = $accion($o);
                $this->repo->finalizarOperacion($o, $resultado);

                return $resultado;
            });
        } catch (UniqueConstraintViolationException $e) {
            throw new DomainException('La referencia o el código ya está registrado. Consulta la operación existente.', 0, $e);
        }
    }
}
