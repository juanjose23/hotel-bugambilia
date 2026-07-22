<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Devoluciones;

use App\BusinessLogic\Compras\Data\Devoluciones\DevolucionItemReporteData;
use App\BusinessLogic\Compras\Data\Devoluciones\DevolucionReporteData;
use App\BusinessLogic\Compras\Data\Shared\ColaboradorReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use App\BusinessLogic\Compras\Data\Shared\PersonaReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ValorReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;
use App\Repository\Models\Compras\DevolucionCompra;

final class ObtenerDevolucionReporteQuery
{
    public function ejecutar(int $id): ?DevolucionReporteData
    {
        $devolucion = DevolucionCompra::with([
            'ordenCompra',
            'recepcionCompra',
            'creador.persona',
            'items.producto',
            'items.variante',
            'items.unidadMedida',
        ])->find($id);

        if ($devolucion === null) {
            return null;
        }

        $creador = null;
        if ($devolucion->creador) {
            $creadorPersona = null;
            if ($devolucion->creador->persona) {
                $creadorPersona = new PersonaReporteData(
                    primer_nombre: $devolucion->creador->persona->primer_nombre,
                    primer_apellido: $devolucion->creador->persona->personaNatural?->primer_apellido,
                    nombre_completo: $devolucion->creador->persona->nombre_completo,
                    razon_social: null
                );
            }
            $creador = new ColaboradorReporteData(
                codigo: $devolucion->creador->codigo ?? (string) $devolucion->creador->id,
                persona: $creadorPersona
            );
        }

        $estado = new EstadoReporteData(
            value: (string) $devolucion->estado->value,
            label: $devolucion->estado->label()
        );

        $items = collect();
        foreach ($devolucion->items as $item) {
            $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
            $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
            $uMedida = $item->unidadMedida ? new ValorReporteData(valor: $item->unidadMedida->nombre) : null;
            $items->push(new DevolucionItemReporteData(
                id: $item->id,
                producto: $producto,
                variante: $variante,
                unidadMedida: $uMedida,
                cantidad_devolver: (float) $item->cantidad_devolver,
            ));
        }

        return new DevolucionReporteData(
            id: $devolucion->id,
            codigo: $devolucion->codigo,
            fecha_devolucion: $devolucion->fecha_devolucion,
            motivo: $devolucion->motivo,
            documento_externo: $devolucion->documento_externo,
            creador: $creador,
            ordenCompraCodigo: $devolucion->ordenCompra?->codigo,
            recepcionCompraCodigo: $devolucion->recepcionCompra?->codigo,
            estado: $estado,
            items: $items
        );
    }
}
