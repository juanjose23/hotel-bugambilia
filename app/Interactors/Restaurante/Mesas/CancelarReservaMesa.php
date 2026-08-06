<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Mesas\ObtenerReservasVigentesMesaQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class CancelarReservaMesa
{
    public function __construct(
        private SepararMesas $separarMesas,
        private RestauranteRepositorioInterface $repositorio,
        private ReservaRepositorioInterface $reservas,
        private ObtenerReservasVigentesMesaQuery $reservasVigentes,
        private CambiarEstadoMesa $cambiarEstadoMesa,
    ) {}

    public function ejecutar(int $mesaId, ?string $motivo = null): void
    {
        $mesa = $this->repositorio->obtenerEspacioPorId($mesaId);

        if (! $mesa instanceof Espacio) {
            throw new DomainException('Mesa no encontrada.');
        }

        DB::transaction(function () use ($mesa, $motivo): void {
            $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
            $reservaId = isset($meta['reserva_id']) && is_numeric($meta['reserva_id'])
                ? (int) $meta['reserva_id']
                : $this->reservasVigentes->paraMesa($mesa->id)?->id;

            if ($reservaId !== null) {
                $reserva = $this->reservas->obtenerPorId($reservaId);
                if ($reserva instanceof Reserva) {
                    $this->reservas->actualizar($reserva, [
                        'estado' => EstadoReserva::CANCELADA,
                        'notas' => $motivo ? "Cancelada: {$motivo}" : $reserva->notas,
                    ]);
                }
            }

            if (
                $mesa->estado === EstadoEspacio::Ocupado
                && $this->repositorio->obtenerPedidosActivosDeMesa($mesa->id)->isNotEmpty()
            ) {
                throw new DomainException("No se puede cancelar/liberar la reserva de la mesa '{$mesa->nombre}' porque tiene pedidos activos.");
            }

            // Si la mesa tenía mesas unidas, separarlas y liberarlas
            $metaUnidas = $meta['mesas_unidas'] ?? null;
            if (is_array($metaUnidas) && $metaUnidas !== []) {
                $this->separarMesas->ejecutar($mesa->id);
            }

            $metaLimpia = $meta;
            unset(
                $metaLimpia['reserva_id'],
                $metaLimpia['codigo_reserva'],
                $metaLimpia['nombre_cliente'],
                $metaLimpia['identificacion_cliente'],
                $metaLimpia['hora_reserva'],
                $metaLimpia['total_personas'],
                $metaLimpia['platos_preordenados'],
                $metaLimpia['platos_preordenados_count']
            );

            $mesaActualizada = $this->cambiarEstadoMesa->ejecutar($mesa->id, EstadoEspacio::Disponible, MotivoTransicionMesa::CancelacionReserva);
            $this->repositorio->actualizarEspacio($mesaActualizada, ['meta_datos' => $metaLimpia]);
        });
    }
}
