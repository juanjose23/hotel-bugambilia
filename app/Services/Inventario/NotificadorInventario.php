<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Filament\Resources\Inventario\Lote\LoteResource;
use App\Models\Inventario\Lote;
use App\Models\User;
use App\Services\Shared\NotificadorBase;
use Illuminate\Support\Collection;

class NotificadorInventario extends NotificadorBase
{
    /** @return Collection<int, User> */
    private function obtenerDestinatarios(): Collection
    {
        // Enviar a todos los usuarios del sistema para desarrollo y pruebas
        return User::all();
    }

    public function loteEnCuarentena(Lote $lote, string $motivo): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Lote en Cuarentena',
            "El lote {$lote->codigo_lote} (Producto: {$lote->producto?->nombre}) ha sido puesto en CUARENTENA. Motivo: {$motivo}.",
            'heroicon-o-shield-exclamation',
            LoteResource::getUrl('view', ['record' => $lote]),
            'warning'
        );
    }

    public function loteLiberado(Lote $lote): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Lote Liberado de Cuarentena',
            "El lote {$lote->codigo_lote} (Producto: {$lote->producto?->nombre}) ha sido liberado de cuarentena y ahora está Disponible.",
            'heroicon-o-check-circle',
            LoteResource::getUrl('view', ['record' => $lote]),
            'success'
        );
    }

    public function loteRechazado(Lote $lote, string $motivo): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Lote Rechazado / Desperdicio',
            "ATENCIÓN: El lote {$lote->codigo_lote} (Producto: {$lote->producto?->nombre}) ha sido RECHAZADO permanentemente y trasladado a la Zona de Merma. Motivo: {$motivo}.",
            'heroicon-o-no-symbol',
            LoteResource::getUrl('view', ['record' => $lote]),
            'danger'
        );
    }

    public function loteCaducado(Lote $lote): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Lote Caducado Automáticamente',
            "El lote {$lote->codigo_lote} (Producto: {$lote->producto?->nombre}) ha expirado y ha sido trasladado a la Zona de Merma.",
            'heroicon-o-x-circle',
            LoteResource::getUrl('view', ['record' => $lote]),
            'danger'
        );
    }

    public function loteProximoACaducar(Lote $lote, int $dias): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Lote Próximo a Caducar',
            "Advertencia: El lote {$lote->codigo_lote} (Producto: {$lote->producto?->nombre}) vencerá en {$dias} días (Fecha: {$lote->fecha_vencimiento?->format('Y-m-d')}).",
            'heroicon-o-clock',
            LoteResource::getUrl('view', ['record' => $lote]),
            'warning'
        );
    }
}
