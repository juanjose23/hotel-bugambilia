<?php

declare(strict_types=1);

use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use App\Support\ReporteConfig;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Garantiza que todo reporte configurado en ReporteConfig (la interfaz unificada
 * de Filament) tenga un mapeo en ReporteDispatcher. Si un código o slug falta,
 * la generación en segundo plano (Job) lanzaría una excepción.
 */
test('todos los reportes configurados tienen mapeo en el dispatcher', function (): void {
    $mapas = mapasDelDispatcher();

    $faltantes = [];

    foreach (ReporteConfig::getReportes() as $modulo => $reportes) {
        foreach (array_keys($reportes) as $key) {
            $codigo = ReporteConfig::getCodigo($modulo, $key);

            $codigoPresente = $codigo !== null && array_key_exists($codigo, $mapas);
            $clavePresente = array_key_exists($key, $mapas);

            if (! $codigoPresente && ! $clavePresente) {
                $faltantes[] = "{$modulo}/{$key} ({$codigo})";
            }
        }
    }

    expect($faltantes)->toBe([]);
});

/**
 * @return array<string, string>
 */
function mapasDelDispatcher(): array
{
    $reflection = new ReflectionClass(ReporteDispatcher::class);

    $mapas = [];

    foreach ($reflection->getReflectionConstants() as $constante) {
        if (! str_starts_with($constante->getName(), 'MAP') && ! str_ends_with($constante->getName(), '_MAP')) {
            continue;
        }

        $valor = $constante->getValue();

        if (is_array($valor)) {
            $mapas = array_merge($mapas, $valor);
        }
    }

    return $mapas;
}
