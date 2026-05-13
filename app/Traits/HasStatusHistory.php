<?php

namespace App\Traits;

use App\Models\Compras\CompraHistorial;
use Illuminate\Support\Facades\Auth;

trait HasStatusHistory
{
    public static function bootHasStatusHistory(): void
    {
        static::updated(function ($model) {
            if ($model->isDirty('estado')) {
                $estadoAnterior = $model->getOriginal('estado');
                $estadoNuevo = $model->estado;

                // Si el estado es un Enum, obtenemos su nombre o valor
                if (method_exists($estadoAnterior, 'label')) {
                    $estadoAnterior = $estadoAnterior->label();
                }
                if (method_exists($estadoNuevo, 'label')) {
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

        static::created(function ($model) {
            $estadoInicial = $model->estado;
            if (method_exists($estadoInicial, 'label')) {
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
