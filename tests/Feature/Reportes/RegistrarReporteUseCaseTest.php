<?php

declare(strict_types=1);

namespace Tests\Feature\Reportes;

use App\Models\Audits\AuditoriaReporte;
use App\Models\User;
use App\UseCases\Reportes\Mutations\RegistrarReporteUseCase;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

// ─── registrar ───────────────────────────────────────────────────────────────

describe('registrar', function () {

    it('crea un registro de auditoría de reporte', function () {
        $reporte = app(RegistrarReporteUseCase::class)->registrar(
            tipo: 'INVENTARIO_MENSUAL',
            parametros: ['mes' => 6, 'anio' => 2026],
            rutaArchivo: 'exports/inventario_junio.pdf',
            usuarioId: $this->user->id,
        );

        expect($reporte)->toBeInstanceOf(AuditoriaReporte::class)
            ->and($reporte->tipo_reporte)->toBe('INVENTARIO_MENSUAL')
            ->and($reporte->parametros)->toBe(['mes' => 6, 'anio' => 2026])
            ->and($reporte->ruta_archivo)->toBe('exports/inventario_junio.pdf')
            ->and($reporte->usuario_id)->toBe($this->user->id);
    });

    it('crea registro sin parámetros ni ruta de archivo', function () {
        $reporte = app(RegistrarReporteUseCase::class)->registrar(
            tipo: 'HABITACIONES_GENERAL',
        );

        expect($reporte->tipo_reporte)->toBe('HABITACIONES_GENERAL')
            ->and($reporte->parametros)->toBeNull()
            ->and($reporte->ruta_archivo)->toBeNull()
            ->and($reporte->usuario_id)->toBeNull();
    });

    it('persiste el registro en la base de datos', function () {
        app(RegistrarReporteUseCase::class)->registrar(
            tipo: 'OCUPACION_ANUAL',
            parametros: ['anio' => 2026],
            usuarioId: $this->user->id,
        );

        $this->assertDatabaseHas((new AuditoriaReporte)->getTable(), [
            'tipo_reporte' => 'OCUPACION_ANUAL',
            'usuario_id' => $this->user->id,
        ]);
    });

    it('guarda parámetros como array', function () {
        $reporte = app(RegistrarReporteUseCase::class)->registrar(
            tipo: 'COMPLEJO',
            parametros: ['filtros' => ['categoria' => 'suite', 'fecha' => '2026-06-01']],
        );

        expect($reporte->parametros)->toBeArray()
            ->and($reporte->parametros['filtros']['categoria'])->toBe('suite');
    });
});

// ─── incrementarDescarga ─────────────────────────────────────────────────────

describe('incrementarDescarga', function () {

    it('incrementa conteo_descargas y actualiza ultima_descarga_en', function () {
        $reporte = AuditoriaReporte::create([
            'usuario_id' => $this->user->id,
            'tipo_reporte' => 'DESCARGABLE',
            'conteo_descargas' => 0,
        ]);

        $actualizado = app(RegistrarReporteUseCase::class)->incrementarDescarga($reporte->id);

        expect($actualizado)->not->toBeNull()
            ->and($actualizado->conteo_descargas)->toBe(1)
            ->and($actualizado->ultima_descarga_en)->not->toBeNull();
    });

    it('retorna null cuando el reporte no existe', function () {
        $resultado = app(RegistrarReporteUseCase::class)->incrementarDescarga(99999);

        expect($resultado)->toBeNull();
    });

    it('acumula descargas múltiples', function () {
        $reporte = AuditoriaReporte::create([
            'usuario_id' => $this->user->id,
            'tipo_reporte' => 'MULTI_DESCARGABLE',
            'conteo_descargas' => 0,
        ]);

        $uc = app(RegistrarReporteUseCase::class);
        $uc->incrementarDescarga($reporte->id);
        $uc->incrementarDescarga($reporte->id);
        $uc->incrementarDescarga($reporte->id);

        expect($reporte->fresh()->conteo_descargas)->toBe(3);
    });
});
