<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Tiempo de Expiración del Token del Visor (Minutos)
    |--------------------------------------------------------------------------
    | Define la duración válida del enlace del reporte generado.
    | Al expirar, el visor deniega el acceso y elimina el archivo físico.
    */
    'token_ttl_minutos' => (int) env('REPORTE_TOKEN_TTL_MINUTOS', 30),

    /*
    |--------------------------------------------------------------------------
    | Umbral de días para despacho a segundo plano
    |--------------------------------------------------------------------------
    | Consultas con un rango superior a este número de días se procesarán
    | en segundo plano (Queue Job) para evitar saturación y timeouts.
    */
    'umbral_dias_background' => (int) env('REPORTE_UMBRAL_DIAS_BACKGROUND', 31),
];
