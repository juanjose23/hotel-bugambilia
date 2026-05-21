<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Espacios;

use App\Models\Espacios\InventarioFijo;
use Illuminate\Support\Facades\DB;

class RetirarActivoDeEspacio
{
    public function execute(string $espacioTipo, int $espacioId, int $activoId, string $motivo): void
    {
        DB::transaction(function () use ($espacioTipo, $espacioId, $activoId, $motivo) {
            $activo = InventarioFijo::where('espacio_tipo', $espacioTipo)
                ->where('espacio_id', $espacioId)
                ->findOrFail($activoId);

            $activo->update([
                'estado' => 'dado_de_baja',
                'notas' => trim(($activo->notas ? $activo->notas."\n" : '').'Retirado: '.$motivo),
            ]);

            $activo->delete(); // Soft delete
        });
    }
}
