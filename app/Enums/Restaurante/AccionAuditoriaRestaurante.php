<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use Filament\Support\Contracts\HasLabel;

enum AccionAuditoriaRestaurante: string implements HasLabel
{
    case CambioEstadoMesa = 'CAMBIO_ESTADO_MESA';
    case MoverCuentaMesa = 'MOVER_CUENTA_MESA';
    case AplicarDescuento = 'APLICAR_DESCUENTO';
    case ImprimirComanda = 'IMPRIMIR_COMANDA';
    case ReimprimirComanda = 'REIMPRIMIR_COMANDA';
    case GuardarConfiguracion = 'GUARDAR_CONFIGURACION_RESTAURANTE';
    case PagoRegistrado = 'PAGO_REGISTRADO';
    case ClienteRapidoCreado = 'CLIENTE_RAPIDO_CREADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::CambioEstadoMesa => 'Cambio Estado Mesa',
            self::MoverCuentaMesa => 'Mover Cuenta Mesa',
            self::AplicarDescuento => 'Aplicar Descuento',
            self::ImprimirComanda => 'Imprimir Comanda',
            self::ReimprimirComanda => 'Reimprimir Comanda',
            self::GuardarConfiguracion => 'Guardar Configuración',
            self::PagoRegistrado => 'Pago Registrado',
            self::ClienteRapidoCreado => 'Cliente Rápido Creado',
        };
    }
}
