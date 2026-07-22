<?php

declare(strict_types=1);

namespace App\Notifications\Inventario;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Inventario\Lote;

final class NotificadorInventario extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosInventario $destinatarios,
        private readonly MensajesInventario $mensajes,
    ) {}

    public function loteRechazado(Lote $lote, string $motivo): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->loteRechazado($lote, $motivo));
    }

    public function loteLiberado(Lote $lote): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->loteLiberado($lote));
    }

    public function loteCaducado(Lote $lote): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->loteCaducado($lote));
    }

    public function loteProximoACaducar(Lote $lote, int $dias): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->loteProximoACaducar($lote, $dias));
    }

    public function loteEnCuarentena(Lote $lote, string $motivo): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->loteEnCuarentena($lote, $motivo));
    }
}
