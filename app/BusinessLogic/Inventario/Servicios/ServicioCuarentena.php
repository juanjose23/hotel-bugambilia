<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Estrategias\PutawayPolicy;
use App\BusinessLogic\Inventario\Validacion\ValidacionLotes;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;

class ServicioCuarentena
{
    public function __construct(
        private readonly MovimientoStock $modeloMovimiento,
        private readonly Stock $modeloStock,
        private readonly ValidacionLotes $validacion,
    ) {}

    public function enviarACuarentena(
        Lote $lote,
        ?int $ubicacionCuarentenaId = null,
        ?string $motivo = null,
        ?int $creadoPorId = null
    ): void {
        $this->validacion->validarNoEnCuarentena($lote);

        $lote->estado = EstadoLote::Cuarentena;
        $lote->save();

        $this->modeloMovimiento->create([
            'tipo' => 'MOV_AJUSTE',
            'lote_id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'cantidad' => 0,
            'ubicacion_destino_id' => $ubicacionCuarentenaId ?? $lote->ubicacion_id,
            'documento_tipo' => 'cuarentena',
            'referencia' => $motivo ?: sprintf('Lote %s enviado a cuarentena', $lote->codigo_lote),
            'creado_por_id' => $creadoPorId,
            'notas' => $motivo,
        ]);
    }

    public function rechazarLote(Lote $lote, string $motivo, ?int $usuarioId = null): void
    {
        if ($lote->estado !== EstadoLote::Cuarentena) {
            throw new \InvalidArgumentException(
                "El lote {$lote->codigo_lote} no está en cuarentena (estado: {$lote->estado->label()})"
            );
        }

        $ubicacionMerma = Ubicacion::query()
            ->where('tipo', 'zona')
            ->where(function ($q) {
                $q->where('nombre', 'like', '%merma%')
                    ->orWhere('descripcion', 'like', '%merma%');
            })
            ->first();

        if (! $ubicacionMerma) {
            throw new \RuntimeException(
                'No se ha configurado una ubicación de "Zona de Merma" activa en el sistema.'
            );
        }

        $ubicacionOrigenId = $lote->ubicacion_id;
        $cantidadRechazada = $lote->cantidad_disponible;

        $lote->update([
            'estado' => EstadoLote::Rechazado,
            'cantidad_disponible' => 0.0,
            'ubicacion_id' => $ubicacionMerma->id,
        ]);

        $this->modeloStock->query()->where([
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacionOrigenId,
        ])->delete();

        $costoUnitarioMov = $lote->costo_unitario;
        $costoTotalMov = $costoUnitarioMov !== null
            ? $costoUnitarioMov * $cantidadRechazada
            : null;

        $this->modeloMovimiento->create([
            'tipo' => 'MOV_AJUSTE',
            'lote_id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'cantidad' => $cantidadRechazada,
            'costo_unitario' => $costoUnitarioMov,
            'costo_total' => $costoTotalMov,
            'ubicacion_origen_id' => $ubicacionOrigenId,
            'ubicacion_destino_id' => $ubicacionMerma->id,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $lote->recepcion_item_id,
            'referencia' => "Rechazo Lote {$lote->codigo_lote}",
            'creado_por_id' => $usuarioId,
            'notas' => $motivo,
        ]);
    }

    public function liberarLote(Lote $lote, ?int $usuarioId = null): void
    {
        if ($lote->estado !== EstadoLote::Cuarentena) {
            throw new \InvalidArgumentException(
                "El lote {$lote->codigo_lote} no está en cuarentena (estado: {$lote->estado->label()})"
            );
        }

        $ubicacionOrigenId = $lote->ubicacion_id;
        $nuevaUbicacion = PutawayPolicy::sugerirUbicacion();

        $lote->update([
            'estado' => EstadoLote::Disponible,
            'ubicacion_id' => $nuevaUbicacion->id,
        ]);

        $stock = $this->modeloStock->query()->where([
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacionOrigenId,
        ])->first();

        if ($stock) {
            if ($ubicacionOrigenId !== $nuevaUbicacion->id) {
                $stockDestino = $this->modeloStock->query()->where([
                    'producto_id' => $lote->producto_id,
                    'producto_variante_id' => $lote->producto_variante_id,
                    'lote_id' => $lote->id,
                    'ubicacion_id' => $nuevaUbicacion->id,
                ])->first();

                if ($stockDestino) {
                    $stockDestino->cantidad += $stock->cantidad;
                    $stockDestino->save();
                    $stock->delete();
                } else {
                    $stock->update([
                        'ubicacion_id' => $nuevaUbicacion->id,
                    ]);
                }
            }
        } else {
            $this->modeloStock->create([
                'producto_id' => $lote->producto_id,
                'producto_variante_id' => $lote->producto_variante_id,
                'lote_id' => $lote->id,
                'ubicacion_id' => $nuevaUbicacion->id,
                'cantidad' => $lote->cantidad_disponible,
            ]);
        }

        $costoUnitarioMov = $lote->costo_unitario;
        $costoTotalMov = $costoUnitarioMov !== null
            ? $costoUnitarioMov * $lote->cantidad_disponible
            : null;

        $this->modeloMovimiento->create([
            'tipo' => 'MOV_TRANSFERENCIA',
            'lote_id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'cantidad' => $lote->cantidad_disponible,
            'costo_unitario' => $costoUnitarioMov,
            'costo_total' => $costoTotalMov,
            'ubicacion_origen_id' => $ubicacionOrigenId,
            'ubicacion_destino_id' => $nuevaUbicacion->id,
            'documento_tipo' => 'liberacion_cuarentena',
            'documento_id' => $lote->id,
            'referencia' => "Liberación {$lote->codigo_lote}",
            'creado_por_id' => $usuarioId,
            'notas' => 'Liberado de cuarentena a almacenamiento',
        ]);
    }
}
