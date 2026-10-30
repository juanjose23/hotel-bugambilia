<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Persistencia\Restaurante\BuffetRepositorio;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class OrganizarServicioBuffet
{
    public function __construct(private BuffetRepositorio $buffets, private GestionarBuffet $operacion) {}

    /**
     * @param  array{nombre: string, fecha_servicio: string, modalidad: string, moneda_id: int, precio_persona: float, precio_paquete: float, comensales_previstos: int, reserva_id?: int|null}  $datos
     * @param  list<array{plato_id: int, producto_variante_id: int, porciones_previstas: int}>  $menu
     */
    public function crear(array $datos, string $servicio, array $menu, ?int $usuarioId = null): Buffet
    {
        return DB::transaction(function () use ($datos, $servicio, $menu, $usuarioId): Buffet {
            $buffet = $this->operacion->crear($datos, $usuarioId);

            return $this->configurar($buffet->id, $servicio, $menu);
        });
    }

    /** @param list<array{plato_id: int, producto_variante_id: int, porciones_previstas: int}> $menu */
    public function configurar(int $buffetId, string $servicio, array $menu): Buffet
    {
        return DB::transaction(function () use ($buffetId, $servicio, $menu): Buffet {
            $buffet = $this->buffets->bloquear($buffetId);
            if ($buffet->estado !== 'abierto' || ! in_array($servicio, ['autoservicio', 'mesero', 'mixto'], true) || $menu === []) {
                throw new DomainException('Elija quién sirve e incluya al menos un platillo en un buffet abierto.');
            }
            $platos = [];
            $variantes = [];
            foreach ($menu as $fila) {
                $plato = $this->buffets->plato($fila['plato_id']);
                $variante = $this->buffets->variante($fila['producto_variante_id']);
                if ($plato === null || $plato->estado !== 1 || $plato->producto_receta_id === null || $variante === null || $variante->estado !== 1 || $fila['porciones_previstas'] < 1) {
                    throw new DomainException('Cada platillo debe tener receta, presentación activa y porciones previstas positivas.');
                }
                if (in_array($plato->id, $platos, true) || in_array($variante->id, $variantes, true)) {
                    throw new DomainException('No repita platillos ni asigne la misma presentación a dos platillos.');
                }
                if ($plato->ingredientes->contains('producto_variante_id', $variante->id)) {
                    throw new DomainException('Seleccione el producto terminado del platillo, no uno de sus ingredientes.');
                }
                $platos[] = $plato->id;
                $variantes[] = $variante->id;
            }
            foreach ($this->buffets->stocks($buffet) as $stock) {
                if (! in_array($stock->producto_variante_id, $variantes, true)) {
                    throw new DomainException('No quite del menú productos que ya fueron enviados al buffet.');
                }
            }

            return $this->buffets->guardarMenu($buffet, $servicio, $menu);
        });
    }
}
