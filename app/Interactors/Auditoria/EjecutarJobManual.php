<?php

declare(strict_types=1);

namespace App\Interactors\Auditoria;

use App\Enums\Shared\EstadoEjecucionJob;
use App\Enums\Shared\TipoJob;
use App\Repository\Models\Audits\AuditoriaJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Throwable;

final class EjecutarJobManual
{
    public function ejecutar(TipoJob $tipoJob): AuditoriaJob
    {
        $usuarioId = Auth::id();

        $registro = AuditoriaJob::create([
            'usuario_id' => $usuarioId,
            'tipo_job' => $tipoJob,
            'nombre_job' => $tipoJob->getLabel(),
            'tipo_ejecucion' => 'manual',
            'estado' => EstadoEjecucionJob::Ejecutando,
            'ejecutado_en' => now(),
        ]);

        try {
            if ($tipoJob->esComando()) {
                Artisan::call($tipoJob->claseJob());
            } else {
                $clase = $tipoJob->claseJob();
                dispatch_sync(new $clase);
            }

            $registro->update([
                'estado' => EstadoEjecucionJob::Completado,
                'mensaje' => 'Job ejecutado exitosamente.',
                'completado_en' => now(),
            ]);
        } catch (Throwable $e) {
            $registro->update([
                'estado' => EstadoEjecucionJob::Fallido,
                'mensaje' => $e->getMessage(),
                'completado_en' => now(),
            ]);
        }

        $refrescado = $registro->fresh();

        return $refrescado ?? $registro;
    }
}
