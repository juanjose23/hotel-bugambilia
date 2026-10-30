<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Inventario\EstadoLote;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Persistencia\Restaurante\BuffetRepositorio;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class GestionarBuffet
{
    public function __construct(private BuffetRepositorio $buffets, private ExistenciasCocinaRepositorio $existencias, private ConsumirExistenciasCocina $consumir, private RegistrarDetalleCuenta $registrarCargo) {}

    /** @param array{nombre: string, fecha_servicio: string, modalidad: string, precio_persona?: float, precio_paquete?: float, comensales_previstos?: int, reserva_id?: int|null, moneda_id?: int} $data */
    public function crear(array $data, ?int $usuarioId = null): Buffet
    {
        if (trim($data['nombre']) === '' || ! in_array($data['modalidad'], ['persona', 'evento', 'mixto'], true)) {
            throw new DomainException('Indique nombre y modalidad válidos.');
        }
        foreach (['precio_persona', 'precio_paquete'] as $campo) {
            if (($data[$campo] ?? 0) < 0 || ! is_finite((float) ($data[$campo] ?? 0))) {
                throw new DomainException('Los precios deben ser válidos.');
            }
        }
        $monedaId = $data['moneda_id'] ?? $this->buffets->monedaPredeterminadaId();
        if ($monedaId === null) {
            throw new DomainException('Configure una moneda para el buffet.');
        }

        return $this->buffets->crear(array_merge($data, ['moneda_id' => $monedaId, 'estado' => 'abierto', 'creado_por' => $usuarioId]));
    }

    public function abastecer(int $buffetId, int $stockId, float $cantidad): Buffet
    {
        return DB::transaction(function () use ($buffetId, $stockId, $cantidad): Buffet {
            $buffet = $this->buffets->bloquear($buffetId);
            if ($buffet->estado !== 'abierto') {
                throw new DomainException('El buffet ya está cerrado.');
            }
            $stock = $this->buffets->stockOrigen($stockId);
            if ($stock->stockable_type !== Ubicacion::class || $cantidad <= 0 || ! is_finite($cantidad) || (float) $stock->cantidad_actual + 0.00001 < $cantidad) {
                throw new DomainException('Existencia o cantidad no válida para abastecer el buffet.');
            }
            if (! $this->buffets->admiteVariante($buffet, $stock->producto_variante_id)) {
                throw new DomainException('Esta presentación no pertenece al menú. Envíe el producto preparado del platillo seleccionado.');
            }
            $lote = $this->existencias->loteBloqueado($stock->lote_id);
            if ($stock->lote_id !== null && ($lote === null || $lote->estado !== EstadoLote::Disponible || ($lote->fecha_vencimiento !== null && $lote->fecha_vencimiento->toDateString() < now()->toDateString()))) {
                throw new DomainException('El lote no está disponible para servir.');
            }
            $stock->cantidad_actual = round((float) $stock->cantidad_actual - $cantidad, 4);
            $this->existencias->guardar($stock);
            $this->buffets->recibir($buffet, $stock, $cantidad);
            $this->existencias->movimiento(['tipo' => 'TRASLADO', 'producto_id' => $stock->producto_id ?? $stock->variante?->producto_id, 'lote_id' => $stock->lote_id, 'cantidad' => $cantidad, 'costo_unitario' => $lote?->costo_unitario, 'costo_total' => round($cantidad * (float) ($lote->costo_unitario ?? 0), 6), 'ubicacion_origen_id' => $stock->stockable_id, 'documento_tipo' => 'buffet', 'documento_id' => $buffet->id, 'referencia' => 'Abastecimiento de '.$buffet->nombre]);

            return $buffet;
        });
    }

    /** @param list<array{stock_id: int, sobrante: float, merma: float}> $mediciones */
    public function cerrar(int $buffetId, int $comensales, array $mediciones): Buffet
    {
        return DB::transaction(function () use ($buffetId, $comensales, $mediciones): Buffet {
            $buffet = $this->buffets->bloquear($buffetId);
            if ($buffet->estado !== 'abierto' || $comensales < 0) {
                throw new DomainException('Estado o comensales inválidos para el cierre.');
            }
            $stocks = $this->buffets->stocks($buffet);
            if ($stocks->isEmpty() || count($mediciones) !== $stocks->count()) {
                throw new DomainException('Debe medir todas las existencias del buffet.');
            }
            $ids = array_column($mediciones, 'stock_id');
            if (count(array_unique($ids)) !== count($ids)) {
                throw new DomainException('No repita existencias en el cierre.');
            }
            foreach ($mediciones as $medicion) {
                $stock = $stocks->firstWhere('id', $medicion['stock_id']);
                $sobrante = $medicion['sobrante'];
                $merma = $medicion['merma'];
                if ($stock === null || $sobrante < 0 || $merma < 0 || ! is_finite($sobrante) || ! is_finite($merma) || $sobrante + $merma > (float) $stock->cantidad_actual + 0.00001) {
                    throw new DomainException('El sobrante y la pérdida deben corresponder a la cantidad enviada.');
                }
                $servido = round((float) $stock->cantidad_actual - $sobrante - $merma, 4);
                $costo = 0.0;
                foreach ([['cantidad' => $servido, 'tipo' => 'CONSUMO'], ['cantidad' => $merma, 'tipo' => 'MERMA_COCINA']] as $salida) {
                    if ($salida['cantidad'] <= 0) {
                        continue;
                    }
                    $resultados = $this->consumir->ejecutar(collect([$stock]), $salida['cantidad'], ['tipo' => $salida['tipo'], 'documento_tipo' => 'buffet', 'documento_id' => $buffet->id, 'referencia' => 'Cierre de '.$buffet->nombre], true);
                    $costo += array_sum(array_column($resultados, 'costo_total'));
                }
                $this->buffets->cierre(['buffet_id' => $buffet->id, 'stock_id' => $stock->id, 'cantidad_servida' => $servido, 'sobrante' => $sobrante, 'merma' => $merma, 'costo_consumido' => round($costo, 2)]);
            }

            return $this->buffets->actualizar($buffet, ['estado' => 'cerrado', 'cerrado_at' => now(), 'comensales_reales' => $comensales]);
        });
    }

    public function cargarCuenta(int $buffetId, int $cuentaId, int $personas, int $paquetes, ?int $usuarioId = null): Buffet
    {
        return DB::transaction(function () use ($buffetId, $cuentaId, $personas, $paquetes, $usuarioId): Buffet {
            $buffet = $this->buffets->bloquear($buffetId);
            if ($buffet->cargado_at !== null) {
                throw new DomainException('El buffet ya tiene un cargo registrado.');
            }
            if ($personas < 0 || $paquetes < 0 || ($buffet->modalidad === 'persona' && $paquetes > 0) || ($buffet->modalidad === 'evento' && $personas > 0)) {
                throw new DomainException('Las cantidades no corresponden a la modalidad.');
            }
            $importe = round($personas * (float) $buffet->precio_persona + $paquetes * (float) $buffet->precio_paquete, 2);
            if ($importe <= 0) {
                throw new DomainException('Indique personas o paquetes con precio positivo.');
            }
            $cuenta = $this->buffets->cuenta($cuentaId);
            if ((int) $cuenta->moneda_id !== (int) $buffet->moneda_id || ($buffet->reserva_id !== null && (int) $cuenta->reserva_id !== (int) $buffet->reserva_id)) {
                throw new DomainException('La moneda y la reserva de la cuenta deben coincidir con el buffet.');
            }
            $this->registrarCargo->ejecutar(cuenta: $cuenta, concepto: 'Buffet '.$buffet->nombre.' ('.$personas.' personas adicionales y '.$paquetes.' paquetes)', precioUnitario: $importe, origen: $buffet, creadorId: $usuarioId);

            return $this->buffets->actualizar($buffet, ['cuenta_id' => $cuentaId, 'cargado_at' => now(), 'personas_cobradas' => $personas, 'paquetes_cobrados' => $paquetes]);
        });
    }

    public function descartarSobrantes(int $buffetId, string $motivo): void
    {
        DB::transaction(function () use ($buffetId, $motivo): void {
            $buffet = $this->buffets->bloquear($buffetId);
            if ($buffet->estado !== 'cerrado' || trim($motivo) === '') {
                throw new DomainException('Cierre el buffet e indique el motivo del descarte.');
            }
            foreach ($this->buffets->stocks($buffet) as $stock) {
                $this->consumir->ejecutar(collect([$stock]), (float) $stock->cantidad_actual, ['tipo' => 'MERMA_COCINA', 'documento_tipo' => 'buffet', 'documento_id' => $buffet->id, 'referencia' => 'Disposición de sobrantes: '.$motivo], true);
            }
        });
    }
}
