<?php

declare(strict_types=1);

namespace App\Notifications\Inventario;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Repository\Models\Inventario\Lote;
use Filament\Actions\Action;

final class MensajesInventario
{
    public function __construct(
        private readonly UrlNotificador $url,
    ) {}

    public function loteRechazado(Lote $lote, string $motivo): DatosNotificacion
    {
        return new DatosNotificacion(
            title: "Lote {$lote->codigo_lote} rechazado",
            body: "El lote del producto \"{$lote->producto?->nombre}\" ha sido rechazado. Motivo: {$motivo}",
            type: TipoNotificacion::Error,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->lote($lote))
                    ->button(),
            ],
        );
    }

    public function loteLiberado(Lote $lote): DatosNotificacion
    {
        return new DatosNotificacion(
            title: "Lote {$lote->codigo_lote} liberado",
            body: "El lote del producto \"{$lote->producto?->nombre}\" ha sido liberado de cuarentena y reubicado.",
            type: TipoNotificacion::Success,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->lote($lote))
                    ->button(),
            ],
        );
    }

    public function loteCaducado(Lote $lote): DatosNotificacion
    {
        return new DatosNotificacion(
            title: "Lote {$lote->codigo_lote} vencido",
            body: "El lote del producto \"{$lote->producto?->nombre}\" ha vencido. Se requiere revisión.",
            type: TipoNotificacion::Warning,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->lote($lote))
                    ->button(),
            ],
        );
    }

    public function loteProximoACaducar(Lote $lote, int $dias): DatosNotificacion
    {
        return new DatosNotificacion(
            title: "Lote {$lote->codigo_lote} próximo a vencer",
            body: "El lote del producto \"{$lote->producto?->nombre}\" vence en {$dias} días.",
            type: TipoNotificacion::Warning,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->lote($lote))
                    ->button(),
            ],
        );
    }

    public function loteEnCuarentena(Lote $lote, string $motivo): DatosNotificacion
    {
        return new DatosNotificacion(
            title: "Lote {$lote->codigo_lote} en cuarentena",
            body: "El lote del producto \"{$lote->producto?->nombre}\" ha sido enviado a cuarentena. Motivo: {$motivo}",
            type: TipoNotificacion::Warning,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->lote($lote))
                    ->button(),
            ],
        );
    }
}
