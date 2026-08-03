<?php

declare(strict_types=1);

namespace App\Enums\Shared;

use App\Enums\Concerns\TieneAyudantesEnum;
use App\Jobs\Activos\NotificarMantenimientosJob;
use App\Jobs\Activos\SincronizarEstadoActivoJob;
use App\Jobs\Activos\VerificarGarantiasJob;
use App\Jobs\Activos\VerificarMantenimientosPreventivosJob;
use App\Jobs\Inventario\VerificarCaducidadesJob;
use App\Jobs\Reservas\EnviarRecordatoriosReservasJob;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoJob: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case VerificarCaducidades = 'verificar_caducidades';
    case VerificarMantenimientosPreventivos = 'verificar_mantenimientos_preventivos';
    case NotificarMantenimientos = 'notificar_mantenimientos';
    case SincronizarEstadoActivo = 'sincronizar_estado_activo';
    case VerificarGarantias = 'verificar_garantias';
    case LimpiezaMaterializar = 'limpieza_materializar';
    case LimpiezaRecordatorio = 'limpieza_recordatorio';
    case ReservasRecordatorio = 'reservas_recordatorio';

    public function getLabel(): string
    {
        return match ($this) {
            self::VerificarCaducidades => 'Verificar Caducidades',
            self::VerificarMantenimientosPreventivos => 'Verificar Mantenimientos Preventivos',
            self::NotificarMantenimientos => 'Notificar Mantenimientos',
            self::SincronizarEstadoActivo => 'Sincronizar Estado de Activos',
            self::VerificarGarantias => 'Verificar Garantías',
            self::LimpiezaMaterializar => 'Materializar Ejecuciones de Limpieza',
            self::LimpiezaRecordatorio => 'Enviar Recordatorios de Limpieza',
            self::ReservasRecordatorio => 'Enviar Recordatorios de Reservas',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::VerificarCaducidades => 'danger',
            self::VerificarMantenimientosPreventivos => 'warning',
            self::NotificarMantenimientos => 'info',
            self::SincronizarEstadoActivo => 'primary',
            self::VerificarGarantias => 'warning',
            self::LimpiezaMaterializar => 'success',
            self::LimpiezaRecordatorio => 'info',
            self::ReservasRecordatorio => 'warning',
        };
    }

    public function claseJob(): string
    {
        return match ($this) {
            self::VerificarCaducidades => VerificarCaducidadesJob::class,
            self::VerificarMantenimientosPreventivos => VerificarMantenimientosPreventivosJob::class,
            self::NotificarMantenimientos => NotificarMantenimientosJob::class,
            self::SincronizarEstadoActivo => SincronizarEstadoActivoJob::class,
            self::VerificarGarantias => VerificarGarantiasJob::class,
            self::LimpiezaMaterializar => 'limpieza:materializar-ejecuciones',
            self::LimpiezaRecordatorio => 'limpieza:enviar-recordatorios',
            self::ReservasRecordatorio => EnviarRecordatoriosReservasJob::class,
        };
    }

    public function esComando(): bool
    {
        return match ($this) {
            self::LimpiezaMaterializar, self::LimpiezaRecordatorio => true,
            default => false,
        };
    }

    public function horarioConfigurado(): ?string
    {
        $clave = match ($this) {
            self::VerificarCaducidades => 'jobs.inventario_caducidades',
            self::VerificarMantenimientosPreventivos => 'jobs.mtto_preventivo',
            self::VerificarGarantias => 'jobs.mtto_garantias',
            self::SincronizarEstadoActivo => 'jobs.mtto_sincronizar',
            self::NotificarMantenimientos => 'jobs.mtto_notificar_proximos',
            self::LimpiezaMaterializar => 'jobs.limpieza_materializar',
            self::LimpiezaRecordatorio => 'jobs.limpieza_recordatorio',
            self::ReservasRecordatorio => 'jobs.reservas_recordatorio',
        };

        $valor = config($clave);

        return is_string($valor) ? $valor : null;
    }
}
