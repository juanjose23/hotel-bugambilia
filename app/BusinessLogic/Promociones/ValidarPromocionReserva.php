<?php

declare(strict_types=1);

namespace App\BusinessLogic\Promociones;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class ValidarPromocionReserva
{
    public function __construct(private readonly ValidarPeriodoPromocion $periodo, private readonly ObtenerPromocionReservaQuery $promociones) {}

    /** @param array<string, mixed> $datos */
    public function vigente(int $id, ?DateTimeInterface $fecha = null, array $datos = [], bool $publica = false): Promocion
    {
        $promocion = $this->promociones->vigente($id, $publica || ($datos['origen_pago_reserva'] ?? null) === 'publico');
        if ($fecha !== null && (($promocion->fecha_inicio !== null && $fecha->format('Y-m-d') < $promocion->fecha_inicio->format('Y-m-d'))
            || ($promocion->fecha_fin !== null && $fecha->format('Y-m-d') > $promocion->fecha_fin->format('Y-m-d')))) {
            throw new InvalidArgumentException('La fecha de la reserva no está dentro de la vigencia de la promoción.');
        }
        if (($datos['origen_pago_reserva'] ?? null) === 'publico') {
            $entrada = is_string($datos['fecha_check_in'] ?? null) ? $datos['fecha_check_in'] : ($fecha?->format('Y-m-d') ?? '');
            $salida = is_string($datos['fecha_check_out'] ?? null) ? $datos['fecha_check_out'] : null;
            $hora = is_string($datos['hora_reserva'] ?? null) ? $datos['hora_reserva'] : '00:00';
            $adultos = is_numeric($datos['adultos'] ?? null) ? (int) $datos['adultos'] : 1;
            $ninos = is_numeric($datos['ninos'] ?? null) ? (int) $datos['ninos'] : 0;
            if ($entrada === '') {
                throw new InvalidArgumentException('Selecciona la fecha de la reserva.');
            }
            if ((float) $promocion->precio_paquete > 0 && $promocion->items->isEmpty()) {
                throw new InvalidArgumentException('Esta oferta necesita configurar sus componentes antes de reservar. Contacta con recepción.');
            }
            $this->periodo->ejecutar(
                new DateTimeImmutable($entrada.' '.$hora),
                $salida !== null && $salida !== '' && $salida !== $entrada ? new DateTimeImmutable($salida) : null,
                $adultos + $ninos,
                is_numeric($promocion->getAttribute('noches_incluidas')) ? (int) $promocion->getAttribute('noches_incluidas') : null,
                is_numeric($promocion->getAttribute('personas_incluidas')) ? (int) $promocion->getAttribute('personas_incluidas') : null,
                $promocion->anticipacion_horas, new DateTimeImmutable(now()->toDateTimeString()),
                ! empty($datos['habitacion_id']) && (float) $promocion->precio_paquete > 0,
            );
            if ($promocion->items->isNotEmpty()) {
                $this->validarComponentes($promocion, $datos);
            }
        }

        return $promocion;
    }

    /** @param array<string, mixed> $datos */
    private function validarComponentes(Promocion $promocion, array $datos): void
    {
        $campos = [Habitacion::class => ['habitacion_id', 'habitaciones_adicionales'],
            Espacio::class => ['espacio_id', 'espacios_adicionales'],
            Servicio::class => ['servicio_id', 'servicios_adicionales'],
            Plato::class => [null, 'items_preorden']];
        foreach ($campos as $modelo => [$campo, $coleccion]) {
            $permitidos = $promocion->items->filter(fn ($item) => $item->item instanceof $modelo && (bool) ($item->getAttribute('incluido') ?? true))
                ->map(fn ($item): int => (int) $item->item_id)->all();
            $elegidos = [];
            if ($campo !== null && is_numeric($datos[$campo] ?? null)) {
                $elegidos[] = (int) $datos[$campo];
            }
            foreach (is_array($datos[$coleccion] ?? null) ? $datos[$coleccion] : [] as $adicional) {
                if (is_array($adicional)) {
                    if ($modelo === Plato::class && (! is_numeric($adicional['cantidad'] ?? null) || (float) $adicional['cantidad'] !== 1.0)) {
                        throw new InvalidArgumentException('La oferta incluye una unidad de cada platillo. Los consumos adicionales se solicitan por separado.');
                    }
                    $valor = $adicional[$campo ?? 'plato_id'] ?? null;
                    if (is_numeric($valor)) {
                        $elegidos[] = (int) $valor;
                    }
                }
            }
            // Una oferta gastronómica puede definir el restaurante padre y reservar una de sus mesas.
            if ($modelo === Espacio::class && isset($elegidos[0])) {
                $padre = $this->promociones->padreDeEspacio($elegidos[0]);
                if (is_numeric($padre) && in_array((int) $padre, $permitidos, true)) {
                    $elegidos = [(int) $padre];
                }
                $soloPlatillos = $promocion->items->every(fn ($item): bool => $item->item instanceof Plato);
                if ($permitidos === [] && $soloPlatillos) {
                    continue;
                }
            }
            sort($permitidos);
            $elegidos = array_values(array_unique($elegidos));
            sort($elegidos);
            if ($elegidos !== $permitidos) {
                throw new InvalidArgumentException('La selección no corresponde a los componentes de esta promoción. Reserva la oferta desde su ficha para conservar todos los beneficios.');
            }
        }
    }
}
