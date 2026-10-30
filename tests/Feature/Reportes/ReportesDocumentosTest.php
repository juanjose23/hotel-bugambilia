<?php

declare(strict_types=1);

use App\Interactors\Compras\Reportes\GenerarReporteCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;

test('reporte de orden de compra genera pdf correctamente', function (): void {
    $orden = OrdenCompra::first();
    if (! $orden) {
        $this->markTestSkipped('No hay órdenes de compra registradas');
    }

    $interactor = app(GenerarReporteCompra::class);
    $pdf = $interactor->execute('orden_compra', ['orden' => $orden]);

    expect($pdf->output())->toBeString()->not->toBeEmpty();
});

test('reporte de recepcion de compra genera pdf correctamente', function (): void {
    $recepcion = RecepcionCompra::first();
    if (! $recepcion) {
        $this->markTestSkipped('No hay recepciones registradas');
    }

    $interactor = app(GenerarReporteCompra::class);
    $pdf = $interactor->execute('recepcion', ['recepcion' => $recepcion]);

    expect($pdf->output())->toBeString()->not->toBeEmpty();
});

test('reporte de trazabilidad completa genera pdf correctamente con postgres', function (): void {
    $solicitud = Solicitud::first();
    if (! $solicitud) {
        $this->markTestSkipped('No hay solicitudes registradas');
    }

    $interactor = app(GenerarReporteCompra::class);
    $pdf = $interactor->execute('trazabilidad_completa', ['solicitud' => $solicitud]);

    expect($pdf->output())->toBeString()->not->toBeEmpty();
});
