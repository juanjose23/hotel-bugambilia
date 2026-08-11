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
use App\Repository\Models\Compras\DevolucionItem;
use App\Repository\Models\User;

final class ObtenerDevolucionReporteQuery
{
    public function ejecutar(int $id): ?DevolucionReporteData
    {
        $devolucion = DevolucionCompra::with([
            'ordenCompra',
            'recepcionCompra',
            'creador.persona.personaNatural',
            'items.producto',
            'items.variante',
            'items.unidadMedida',
            'items.lote.recepcionItem',
        ])->find($id);

        if ($devolucion === null) {
            return null;
        }

        $estado = new EstadoReporteData(
            value: (string) $devolucion->estado->value,
            label: $devolucion->estado->label()
        );

        return new DevolucionReporteData(
            id: $devolucion->id,
            codigo: $devolucion->codigo,
            fecha_devolucion: $devolucion->fecha_devolucion,
            motivo: $devolucion->motivo,
            documento_externo: $devolucion->documento_externo,
            creador: $this->mapearCreador($devolucion->creador),
            ordenCompraCodigo: $devolucion->ordenCompra?->codigo,
            recepcionCompraCodigo: $devolucion->recepcionCompra?->codigo,
            estado: $estado,
            items: $devolucion->items->map(fn ($item) => $this->mapearItem($item))
        );
    }

    private function mapearCreador(?User $creador): ?ColaboradorReporteData
    {
        if (! $creador) {
            return null;
        }

        $creadorPersona = null;
        if ($creador->persona) {
            $creadorPersona = new PersonaReporteData(
                primer_nombre: $creador->persona->primer_nombre,
                primer_apellido: $creador->persona->personaNatural?->primer_apellido,
                nombre_completo: $creador->persona->nombre_completo,
                razon_social: null
            );
        }

        return new ColaboradorReporteData(
            codigo: $creador->codigo ?? (string) $creador->id,
            persona: $creadorPersona
        );
    }

    private function mapearItem(DevolucionItem $item): DevolucionItemReporteData
    {
        $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
        $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
        $uMedida = $item->unidadMedida ? new ValorReporteData(valor: $item->unidadMedida->nombre) : null;

        return new DevolucionItemReporteData(
            id: $item->id,
            producto: $producto,
            variante: $variante,
            unidadMedida: $uMedida,
            cantidad_devolver: (float) $item->cantidad_devolver,
        );
    }
}
