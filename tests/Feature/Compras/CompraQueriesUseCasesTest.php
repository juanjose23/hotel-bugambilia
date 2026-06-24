<?php

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\Solicitud;
use App\Models\User;
use App\UseCases\Compras\Queries\ObtenerResumenComprasDepartamentosUseCase;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->catalogoTipo = CatalogoTipo::factory()->create();

    $this->departamento = Catalogo::create([
        'nombre' => 'Cocina',
        'codigo' => 'DEP_COCINA',
        'catalogo_tipo_id' => $this->catalogoTipo->id,
        'estado' => 1,
        'orden' => 1,
    ]);

    $this->solicitud = Solicitud::create([
        'codigo' => 'S-TEST-RES-001',
        'colaborador_id' => Colaborador::factory()->create()->id,
        'departamento_solicitante_id' => $this->departamento->id,
        'fecha_solicitud' => now()->subDays(5),
        'motivo' => 'Resumen test',
        'estado' => EstadoSolicitud::Aprobada,
    ]);
});

describe('ObtenerResumenComprasDepartamentosUseCase', function () {
    it('retorna resumen agrupado por departamento con conteos y totales', function () {
        $proveedor = Proveedor::factory()->create();

        OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
        ]);

        OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'subtotal' => 500,
            'impuestos' => 75,
            'total' => 575,
        ]);

        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([
            'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
            'fecha_fin' => now()->addMonth()->format('Y-m-d'),
        ]);

        expect($resultado)->toHaveKeys(['data', 'fechaInicio', 'fechaFin']);
        expect($resultado['data'])->toHaveCount(1);
        expect($resultado['data']->first()->departamento)->toBe('Cocina');
        expect((int) $resultado['data']->first()->conteo_ordenes)->toBe(2);
        expect((float) $resultado['data']->first()->total_oc)->toBe(1725.00);
    });

    it('retorna data vacia cuando no hay ordenes en el periodo', function () {
        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([
            'fecha_inicio' => now()->subYears(10)->format('Y-m-d'),
            'fecha_fin' => now()->subYears(10)->addDay()->format('Y-m-d'),
        ]);

        expect($resultado['data'])->toHaveCount(0);
    });

    it('intercambia fechas cuando fechaInicio > fechaFin', function () {
        $proveedor = Proveedor::factory()->create();

        OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'total' => 500,
        ]);

        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([
            'fecha_inicio' => now()->addMonth()->format('Y-m-d'),
            'fecha_fin' => now()->subMonth()->format('Y-m-d'),
        ]);

        expect($resultado['fechaInicio']->lte($resultado['fechaFin']))->toBeTrue();
    });

    it('usa inicio de mes como fecha por defecto cuando no se proporcionan filtros', function () {
        $proveedor = Proveedor::factory()->create();

        OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'total' => 300,
        ]);

        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([]);

        expect($resultado['fechaInicio']->isStartOfMonth())->toBeTrue();
        expect($resultado['fechaFin']->isToday())->toBeTrue();
    });

    it('ignora ordenes soft-deleted', function () {
        $proveedor = Proveedor::factory()->create();

        $orden = OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'total' => 1000,
        ]);

        $orden->delete();

        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([
            'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
            'fecha_fin' => now()->addMonth()->format('Y-m-d'),
        ]);

        expect($resultado['data'])->toHaveCount(0);
    });

    it('usa fechas por defecto cuando el parseo de fechas falla', function () {
        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([
            'fecha_inicio' => 'not-a-date',
            'fecha_fin' => 'also-not-a-date',
        ]);

        expect($resultado['fechaInicio']->isStartOfMonth())->toBeTrue();
    });

    it('maneja multiples departamentos en el resumen', function () {
        $departamento2 = Catalogo::create([
            'nombre' => 'Mantenimiento',
            'codigo' => 'DEP_MANT',
            'catalogo_tipo_id' => $this->catalogoTipo->id,
            'estado' => 1,
            'orden' => 2,
        ]);

        $solicitud2 = Solicitud::create([
            'codigo' => 'S-TEST-RES-002',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $departamento2->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test multi',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        $proveedor = Proveedor::factory()->create();

        OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'total' => 1150,
        ]);

        OrdenCompra::factory()->create([
            'solicitud_id' => $solicitud2->id,
            'proveedor_id' => $proveedor->id,
            'fecha_orden' => now(),
            'total' => 2300,
        ]);

        $resultado = app(ObtenerResumenComprasDepartamentosUseCase::class)->execute([]);

        expect($resultado['data'])->toHaveCount(2);
        expect($resultado['data']->pluck('departamento')->sort()->values()->toArray())->toBe(['Cocina', 'Mantenimiento']);
    });
});
