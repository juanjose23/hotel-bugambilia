<?php

declare(strict_types=1);

namespace App\Traits;

use App\Repository\Models\Compras\CompraHistorial;
use Illuminate\Support\Facades\Auth;

trait TieneHistorialEstado
{
    public static function bootTieneHistorialEstado(): void
    {
        static::updated(function ($model): void {
            if ($model->isDirty('estado')) {
                $estadoAnterior = $model->getOriginal('estado');
                $estadoNuevo = $model->estado;

                if (is_object($estadoAnterior) && method_exists($estadoAnterior, 'label')) {
                    $estadoAnterior = $estadoAnterior->label();
                }
                if (is_object($estadoNuevo) && method_exists($estadoNuevo, 'label')) {
                    $estadoNuevo = $estadoNuevo->label();
                }

                CompraHistorial::create([
                    'model_type' => class_basename($model),
                    'model_id' => $model->id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $estadoNuevo,
                    'usuario_id' => Auth::id(),
                    'comentario' => 'Cambio de estado automático/manual detectado por el sistema.',
                ]);
            }
        });

        static::created(function ($model): void {
            $estadoInicial = $model->estado;
            if (is_object($estadoInicial) && method_exists($estadoInicial, 'label')) {
                $estadoInicial = $estadoInicial->label();
            }

            CompraHistorial::create([
                'model_type' => class_basename($model),
                'model_id' => $model->id,
                'estado_anterior' => null,
                'estado_nuevo' => $estadoInicial,
                'usuario_id' => Auth::id(),
                'comentario' => 'Registro inicial del documento.',
            ]);
        });
    }
}
