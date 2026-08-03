<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Actions\Restaurante\NormalizarMetaDatosAction;
use App\BusinessLogic\Restaurante\Mesas\ResolverUnionMesasAuto;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ReasignarMesaReserva
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private ReservaRepositorioInterface $reservas,
        private UnirMesas $unirMesas,
        private SepararMesas $separarMesas,
        private ResolverUnionMesasAuto $resolverUnionAuto,
        private NormalizarMetaDatosAction $normalizarMetaDatosAction,
    ) {}

    /**
     * Reasigna inteligentemente una reserva a una nueva mesa principal (y une mesas libres secundarias si se requiere),
     * liberando las mesas previas asignadas a la reserva.
     */
    public function ejecutar(Reserva $reserva, int $nuevaMesaPrincipalId): Espacio
    {
        $nuevaMesa = $this->repositorio->obtenerEspacioPorId($nuevaMesaPrincipalId);

        if (! $nuevaMesa instanceof Espacio) {
            throw new DomainException("La mesa de destino #{$nuevaMesaPrincipalId} no existe.");
        }

        if ($nuevaMesa->estado === EstadoEspacio::Ocupado) {
            throw new DomainException("La mesa {$nuevaMesa->nombre} se encuentra actualmente ocupada.");
        }

        return DB::transaction(function () use ($reserva, $nuevaMesa): Espacio {
            // 1. Liberar la mesa anterior si existía
            if ($reserva->espacio_id !== null) {
                $mesaAnterior = $this->repositorio->obtenerEspacioPorId((int) $reserva->espacio_id);
                if ($mesaAnterior instanceof Espacio) {
                    $meta = $this->normalizarMetaDatosAction->ejecutar($mesaAnterior->meta_datos);
                    unset($meta['reserva_id'], $meta['codigo_reserva']);

                    $this->repositorio->actualizarEspacio($mesaAnterior, [
                        'estado' => EstadoEspacio::Disponible,
                        'meta_datos' => $meta,
                    ]);

                    if (isset($meta['mesas_unidas']) && is_array($meta['mesas_unidas']) && $meta['mesas_unidas'] !== []) {
                        $this->separarMesas->ejecutar((int) $mesaAnterior->id);
                    }
                }
            }

            // 2. Resolver unión automática si la nueva mesa requiere más espacio para los comensales
            $mesaAnteriorId = (int) $reserva->espacio_id;
            $comensales = (int) ($reserva->adultos + $reserva->ninos);
            $mesasLibres = Espacio::query()
                ->where('estado', EstadoEspacio::Disponible->value)
                ->where('id', '!=', $nuevaMesa->id)
                ->where('id', '!=', $mesaAnteriorId)
                ->get();
            $secundariasParaUnir = $this->resolverUnionAuto->resolver($nuevaMesa, $comensales, $mesasLibres);

            // 3. Actualizar la reserva con la nueva mesa asignada
            $this->reservas->actualizar($reserva, [
                'espacio_id' => $nuevaMesa->id,
            ]);

            // 4. Bloquear y unir la nueva mesa principal y secundarias
            $metaNueva = $this->normalizarMetaDatosAction->ejecutar($nuevaMesa->meta_datos);
            $metaNueva['reserva_id'] = $reserva->id;
            $metaNueva['codigo_reserva'] = $reserva->codigo_reserva;

            $this->repositorio->actualizarEspacio($nuevaMesa, [
                'estado' => EstadoEspacio::Reservado,
                'meta_datos' => $metaNueva,
            ]);

            if ($secundariasParaUnir !== []) {
                $this->unirMesas->ejecutar(
                    mesaPrincipalId: (int) $nuevaMesa->id,
                    mesasSecundariasIds: $secundariasParaUnir,
                    reservaId: (int) $reserva->id,
                    motivo: 'reasignacion_inteligente',
                );
            }

            return $nuevaMesa->refresh();
        });
    }
}
