<?php

declare(strict_types=1);

namespace App\Interactors\Activos\Mantenimiento;

use App\BusinessLogic\Activos\ProcesadorNotificacionesMantenimiento;

class NotificarMantenimientos
{
    public function __construct(
        private readonly ProcesadorNotificacionesMantenimiento $procesador,
    ) {}

    public function ejecutar(): int
    {
        $notificacionesEnviadas = 0;

        $notificacionesEnviadas += $this->procesador->procesarFuturos(7, 'proximo_7_dias');
        $notificacionesEnviadas += $this->procesador->procesarFuturos(3, 'proximo_3_dias');
        $notificacionesEnviadas += $this->procesador->procesarFuturos(1, 'proximo_1_dia');
        $notificacionesEnviadas += $this->procesador->procesarFuturos(0, 'hoy');
        $notificacionesEnviadas += $this->procesador->procesarAtrasados(1, 'vencido');
        $notificacionesEnviadas += $this->procesador->procesarAtrasadosCriticos(7, 'critico');
        $notificacionesEnviadas += $this->procesador->procesarProlongados(15, 'prolongado');

        return $notificacionesEnviadas;
    }
}
