<?php

declare(strict_types=1);

namespace App\Notifications\Activos;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use App\Filament\Resources\Activos\ActivoBaja\ActivoBajaResource;
use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use App\Filament\Resources\Activos\ActPlanMantenimiento\ActPlanMantenimientoResource;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoBaja;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Activos\ActPlanMantenimiento;

final class UrlNotificador
{
    public function activo(Activo $activo): string
    {
        return ActivoResource::getUrl('view', ['record' => $activo->id]);
    }

    public function mantenimiento(ActivoMantenimiento $mantenimiento): string
    {
        return ActivoMantenimientoResource::getUrl('view', ['record' => $mantenimiento->id]);
    }

    public function plan(ActPlanMantenimiento $plan): string
    {
        return ActPlanMantenimientoResource::getUrl('view', ['record' => $plan->id]);
    }

    public function baja(ActivoBaja $baja): string
    {
        return ActivoBajaResource::getUrl('index');
    }
}
