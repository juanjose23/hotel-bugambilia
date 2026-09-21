<?php

declare(strict_types=1);

use App\BusinessLogic\Reportes\ClasificarAnomaliasOperacion;
use App\Enums\Reportes\SeveridadAnomalia;

it('no genera anomalías cuando las métricas están dentro de los umbrales', function (): void {
    $clasificador = new ClasificarAnomaliasOperacion;

    $anomalias = $clasificador->clasificar([
        'limpiezas_pendientes' => 3,
        'tiempo_promedio_minutos' => 40,
        'habitaciones_bloqueadas' => 1,
        'mantenimientos_vencidos' => 0,
        'mantenimientos_proximos' => 2,
        'activos_en_mantenimiento' => 1,
        'garantias_proximas' => 3,
    ]);

    expect($anomalias)->toBeEmpty();
});

it('genera una anomalía cuando una métrica supera su umbral', function (): void {
    $clasificador = new ClasificarAnomaliasOperacion;

    $anomalias = $clasificador->clasificar([
        'limpiezas_pendientes' => 8,
        'tiempo_promedio_minutos' => 40,
    ]);

    expect($anomalias)->toHaveCount(1)
        ->and($anomalias[0]['clave'])->toBe('limpiezas_pendientes')
        ->and($anomalias[0]['cantidad'])->toBe(8.0)
        ->and($anomalias[0]['umbral'])->toBe(5);
});

it('escala la severidad según el exceso sobre el umbral', function (): void {
    $clasificador = new ClasificarAnomaliasOperacion;

    $baja = $clasificador->clasificar(['limpiezas_pendientes' => 7]);
    $media = $clasificador->clasificar(['limpiezas_pendientes' => 9]);
    $alta = $clasificador->clasificar(['limpiezas_pendientes' => 11]);
    $critica = $clasificador->clasificar(['limpiezas_pendientes' => 16]);

    expect($baja[0]['severidad'])->toBe(SeveridadAnomalia::Baja)
        ->and($media[0]['severidad'])->toBe(SeveridadAnomalia::Media)
        ->and($alta[0]['severidad'])->toBe(SeveridadAnomalia::Alta)
        ->and($critica[0]['severidad'])->toBe(SeveridadAnomalia::Critica);
});

it('respeta los umbrales personalizados', function (): void {
    $clasificador = new ClasificarAnomaliasOperacion(['limpiezas_pendientes' => 2]);

    $anomalias = $clasificador->clasificar(['limpiezas_pendientes' => 3]);

    expect($anomalias)->toHaveCount(1)
        ->and($anomalias[0]['umbral'])->toBe(2);
});

it('omite indicadores sin umbral configurado', function (): void {
    $clasificador = new ClasificarAnomaliasOperacion;

    $anomalias = $clasificador->clasificar(['indicador_desconocido' => 999]);

    expect($anomalias)->toBeEmpty();
});
