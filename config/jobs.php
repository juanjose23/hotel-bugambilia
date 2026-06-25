<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Schedule de Jobs Automáticos
    |--------------------------------------------------------------------------
    |
    | Todas las horas en formato HH:MM (24h).
    | Asignar null para desactivar el job.
    |
    */

    'inventario_caducidades' => env('JOB_INVENTARIO_CADUCIDADES_AT', '06:00'),

    'mtto_preventivo' => env('JOB_MANTENIMIENTO_PREVENTIVO_AT', '06:00'),

    'mtto_garantias' => env('JOB_MANTENIMIENTO_GARANTIAS_AT', '06:15'),

    'mtto_atrasados' => env('JOB_MANTENIMIENTO_ATRASADOS_AT', '06:30'),

    'mtto_sin_historico' => env('JOB_MANTENIMIENTO_SIN_HISTORICO_AT', '06:35'),

    'mtto_sincronizar' => env('JOB_MANTENIMIENTO_SINCRONIZAR_AT', '06:40'),

    'mtto_notificar_proximos' => env('JOB_MANTENIMIENTO_NOTIFICAR_PROXIMOS_AT', '07:00'),

    'mtto_auto_completar' => env('JOB_MANTENIMIENTO_AUTO_COMPLETAR_AT', '23:30'),

    'limpieza_materializar' => env('JOB_LIMPIEZA_MATERIALIZAR_AT', '05:30'),

    'limpieza_recordatorio' => env('JOB_LIMPIEZA_RECORDATORIO_AT', '12:00'),
];
