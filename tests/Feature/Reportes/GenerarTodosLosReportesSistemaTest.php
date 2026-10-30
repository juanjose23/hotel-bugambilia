<?php

declare(strict_types=1);

use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use App\Interactors\Restaurante\Reportes\GenerarReporteRestaurante;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\User;
use App\Support\Barcode\Support\ReporteConfig;
use Barryvdh\DomPDF\PDF;

beforeEach(function (): void {
    $admin = User::first() ?? User::factory()->create();
    $this->actingAs($admin);
});

describe('Generación de todos los reportes de ReporteConfig', function (): void {
    foreach (ReporteConfig::getReportes() as $modulo => $reportes) {
        foreach ($reportes as $slug => $info) {
            $codigo = $info['codigo'];
            $titulo = $info['titulo'];

            test("Módulo [{$modulo}] Reporte {$codigo} - {$titulo} ({$slug}) genera contenido válido", function () use ($slug, $codigo): void {
                $dispatcher = app(ReporteDispatcher::class);

                $params = [
                    'fecha_inicio' => now()->subMonths(6)->toDateString(),
                    'fecha_fin' => now()->toDateString(),
                    'meses' => 6,
                ];

                if ($slug === 'hoja_habitacion') {
                    $habitacion = Habitacion::first();
                    if ($habitacion) {
                        $params['tipo'] = 'habitacion';
                        $params['id'] = $habitacion->id;
                    }
                } elseif ($slug === 'ficha_espacio') {
                    $espacio = Espacio::first();
                    if ($espacio) {
                        $params['tipo'] = 'espacio';
                        $params['id'] = $espacio->id;
                    }
                } elseif ($slug === 'historial') {
                    $activo = Activo::first();
                    if ($activo) {
                        $params['activo_id'] = $activo->id;
                    }
                }

                $resultadoPorCodigo = $dispatcher->generar($codigo, $params);
                $contenidoCodigo = $resultadoPorCodigo instanceof PDF ? $resultadoPorCodigo->output() : $resultadoPorCodigo->getContent();

                expect($contenidoCodigo)->toBeString();
                $this->assertNotEmpty($contenidoCodigo);

                $resultadoPorSlug = $dispatcher->generar($slug, $params);
                $contenidoSlug = $resultadoPorSlug instanceof PDF ? $resultadoPorSlug->output() : $resultadoPorSlug->getContent();

                expect($contenidoSlug)->toBeString();
                $this->assertNotEmpty($contenidoSlug);
            });
        }
    }
});

describe('Generación de reportes de documentos individuales', function (): void {
    test('Compras: reporte de solicitud', function (): void {
        $solicitud = Solicitud::first();
        if (! $solicitud) {
            $this->markTestSkipped('No hay solicitudes');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('solicitud', ['solicitud' => $solicitud]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Compras: reporte de orden de compra', function (): void {
        $orden = OrdenCompra::first();
        if (! $orden) {
            $this->markTestSkipped('No hay órdenes de compra');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('orden_compra', ['orden' => $orden]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Compras: reporte de recepcion', function (): void {
        $recepcion = RecepcionCompra::first();
        if (! $recepcion) {
            $this->markTestSkipped('No hay recepciones');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('recepcion', ['recepcion' => $recepcion]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Compras: reporte de devolucion', function (): void {
        $devolucion = DevolucionCompra::first();
        if (! $devolucion) {
            $this->markTestSkipped('No hay devoluciones');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('devolucion', ['devolucion' => $devolucion]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Compras: reporte de cotizacion', function (): void {
        $cotizacion = Cotizacion::first();
        if (! $cotizacion) {
            $this->markTestSkipped('No hay cotizaciones');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('cotizacion', ['cotizacion' => $cotizacion]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Compras: reporte comparativa', function (): void {
        $solicitud = Solicitud::first();
        if (! $solicitud) {
            $this->markTestSkipped('No hay solicitudes');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('comparativa', ['solicitud' => $solicitud]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Compras: reporte trazabilidad completa', function (): void {
        $solicitud = Solicitud::first();
        if (! $solicitud) {
            $this->markTestSkipped('No hay solicitudes');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('trazabilidad_completa', ['solicitud' => $solicitud]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Inventario: trazabilidad de lote', function (): void {
        $lote = Lote::first();
        if (! $lote) {
            $this->markTestSkipped('No hay lotes');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('trazabilidad_lote', ['lote' => $lote]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Activos: ficha de activo', function (): void {
        $activo = Activo::first();
        if (! $activo) {
            $this->markTestSkipped('No hay activos');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('fichaActivoPdf', ['activo' => $activo]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Activos: ficha de mantenimiento', function (): void {
        $mantenimiento = ActivoMantenimiento::first();
        if (! $mantenimiento) {
            $this->markTestSkipped('No hay mantenimientos');
        }

        $dispatcher = app(ReporteDispatcher::class);
        $res = $dispatcher->generar('fichaMantenimientoPdf', ['mantenimiento' => $mantenimiento]);
        $content = $res instanceof PDF ? $res->output() : $res->getContent();
        expect($content)->toBeString();
        $this->assertNotEmpty($content);
    });

    test('Restaurante: datos de reporte de restaurante', function (): void {
        $interactor = app(GenerarReporteRestaurante::class);
        $datos = $interactor->ejecutar(now()->subMonth()->toDateString(), now()->toDateString());

        expect($datos)->toBeArray()
            ->and($datos)->toHaveKeys(['resumen', 'topPlatos', 'porCategoria', 'totalPedidos']);
    });
});
