<?php

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\Solicitud;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Compras\Solicitudes\Mutations\AprobarSolicitud;
use App\UseCases\Compras\Solicitudes\Mutations\CancelarSolicitud;
use App\UseCases\Compras\Solicitudes\Mutations\GenerarCodigoSolicitud;
use App\UseCases\Compras\Solicitudes\Mutations\RechazarSolicitud;
use App\UseCases\Compras\Solicitudes\Queries\ObtenerSolicitudConItems;
use App\UseCases\Compras\Solicitudes\Queries\ObtenerSolicitudesParaComparar;
use App\UseCases\Compras\Solicitudes\Queries\ObtenerSolicitudParaComparativa;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->catalogoTipo = CatalogoTipo::factory()->create();

    $this->moneda = Moneda::create([
        'codigo' => 'NIO',
        'nombre' => 'Córdoba',
        'simbolo' => 'C$',
    ]);

    $this->departamento = Catalogo::create([
        'nombre' => 'Cocina',
        'codigo' => 'DEP_COCINA',
        'catalogo_tipo_id' => $this->catalogoTipo->id,
        'estado' => 1,
        'orden' => 1,
    ]);

    $this->producto = Producto::factory()->create();
});

describe('GenerarCodigoSolicitud', function () {
    it('genera codigo con siglas basadas en codigo del departamento con guiones bajos', function () {
        $departamentoConGuiones = Catalogo::create([
            'nombre' => 'Cocina y Banquetes',
            'codigo' => 'DEP_COCINA_BANQ',
            'catalogo_tipo_id' => $this->catalogoTipo->id,
            'estado' => 1,
            'orden' => 2,
        ]);

        $codigo = app(GenerarCodigoSolicitud::class)->ejecutar($departamentoConGuiones->id);

        expect($codigo)->toMatch('/^S-CB-\d{3}$/');
    });

    it('genera S-XXXX-001 cuando es la primera solicitud del departamento', function () {
        $departamento = Catalogo::create([
            'nombre' => 'Mantenimiento',
            'codigo' => 'DEP_MAN',
            'catalogo_tipo_id' => $this->catalogoTipo->id,
            'estado' => 1,
            'orden' => 3,
        ]);

        $codigo = app(GenerarCodigoSolicitud::class)->ejecutar($departamento->id);

        expect($codigo)->toBe('S-M-001');
    });

    it('genera codigos correlativos para el mismo departamento', function () {
        $codigo1 = app(GenerarCodigoSolicitud::class)->ejecutar($this->departamento->id);

        Solicitud::create([
            'codigo' => $codigo1,
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $codigo2 = app(GenerarCodigoSolicitud::class)->ejecutar($this->departamento->id);

        expect(1)->toBe(1); // Ensure we got past the code generation
        expect($codigo2)->toBe('S-C-002');
    });

    it('lanza exception si el departamento no existe', function () {
        expect(fn () => app(GenerarCodigoSolicitud::class)->ejecutar(99999))
            ->toThrow(ModelNotFoundException::class);
    });

    it('genera siglas de 4 caracteres para codigos sin prefijo', function () {
        $depSinPrefijo = Catalogo::create([
            'nombre' => 'Test',
            'codigo' => 'SOLO',
            'catalogo_tipo_id' => $this->catalogoTipo->id,
            'estado' => 1,
            'orden' => 4,
        ]);

        $codigo = app(GenerarCodigoSolicitud::class)->ejecutar($depSinPrefijo->id);

        expect($codigo)->toMatch('/^S-SOLO-\d{3}$/');
    });
});

describe('AprobarSolicitud', function () {
    it('aprueba solicitud y actualiza cantidades aprobadas', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-001',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test aprobacion',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $item = $solicitud->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad_solicitada' => 10,
            'cantidad_aprobada' => 0,
        ]);

        app(AprobarSolicitud::class)->execute($solicitud, [
            ['id' => $item->id, 'cantidad_aprobada' => 8],
        ]);

        expect($solicitud->fresh()->estado)->toBe(EstadoSolicitud::Aprobada);
        expect($item->fresh()->cantidad_aprobada)->toBe('8.00');
    });

    it('aprueba solicitud sin items aprobados especificos', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-002',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test aprobacion sin items',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $item = $solicitud->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad_solicitada' => 10,
            'cantidad_aprobada' => 5,
        ]);

        app(AprobarSolicitud::class)->execute($solicitud);

        expect($solicitud->fresh()->estado)->toBe(EstadoSolicitud::Aprobada);
        expect($item->fresh()->cantidad_aprobada)->toBe('5.00');
    });
});

describe('RechazarSolicitud', function () {
    it('cambia estado a Rechazada', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-003',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test rechazo',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        app(RechazarSolicitud::class)->execute($solicitud);

        expect($solicitud->fresh()->estado)->toBe(EstadoSolicitud::Rechazada);
    });

    it('permite rechazar una solicitud ya aprobada', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-004',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test rechazo post-aprobacion',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        app(RechazarSolicitud::class)->execute($solicitud);

        expect($solicitud->fresh()->estado)->toBe(EstadoSolicitud::Rechazada);
    });
});

describe('CancelarSolicitud', function () {
    it('cancela solicitud con items de cancelacion y nota', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-005',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test cancelacion',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $item = $solicitud->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad_solicitada' => 10,
            'cantidad_aprobada' => 5,
        ]);

        app(CancelarSolicitud::class)->execute($solicitud, [
            ['cantidad_aprobada' => 0],
        ], 'Cancelado por falta de presupuesto');

        expect($solicitud->fresh()->estado)->toBe(EstadoSolicitud::Cancelada);
        expect($item->fresh()->cantidad_aprobada)->toBe('0.00');
        expect($solicitud->fresh()->notas)->toContain('Cancelado por falta de presupuesto');
    });

    it('cancela solicitud sin items de cancelacion', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-006',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test cancelacion sin items',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        app(CancelarSolicitud::class)->execute($solicitud);

        expect($solicitud->fresh()->estado)->toBe(EstadoSolicitud::Cancelada);
        expect($solicitud->fresh()->notas)->toContain('CANCELADO');
    });

    it('concatena notas si la solicitud ya tenia notas previas', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-007',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test notas concatenadas',
            'estado' => EstadoSolicitud::Pendiente,
            'notas' => 'Nota previa importante.',
        ]);

        app(CancelarSolicitud::class)->execute($solicitud, [], 'Cancelado por compras');

        expect($solicitud->fresh()->notas)->toContain('Nota previa importante.');
        expect($solicitud->fresh()->notas)->toContain('Cancelado por compras');
    });
});

describe('ObtenerSolicitudConItems', function () {
    it('retorna solicitud con items cargados', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-008',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Test query',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $solicitud->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad_solicitada' => 10,
        ]);

        $resultado = app(ObtenerSolicitudConItems::class)->execute($solicitud->id);

        expect($resultado)->not->toBeNull();
        expect($resultado->relationLoaded('items'))->toBeTrue();
        expect($resultado->items)->toHaveCount(1);
    });

    it('retorna null cuando la solicitud no existe', function () {
        $resultado = app(ObtenerSolicitudConItems::class)->execute(99999);

        expect($resultado)->toBeNull();
    });
});

describe('ObtenerSolicitudesParaComparar', function () {
    it('retorna solicitudes aprobadas sin cotizaciones elegidas ni ordenes', function () {
        $solicitudValida = Solicitud::create([
            'codigo' => 'S-TEST-009',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Valida para comparar',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        $resultado = app(ObtenerSolicitudesParaComparar::class)->execute();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($solicitudValida->id);
    });

    it('excluye solicitudes en estado no Aprobada', function () {
        Solicitud::create([
            'codigo' => 'S-TEST-010',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Pendiente',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        Solicitud::create([
            'codigo' => 'S-TEST-011',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Rechazada',
            'estado' => EstadoSolicitud::Rechazada,
        ]);

        $resultado = app(ObtenerSolicitudesParaComparar::class)->execute();

        expect($resultado)->toHaveCount(0);
    });

    it('excluye solicitudes que ya tienen cotizacion elegida', function () {
        $proveedor = Proveedor::factory()->create();

        $solicitudConElegida = Solicitud::create([
            'codigo' => 'S-TEST-012',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Con elegida',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        $cotizacion = Cotizacion::create([
            'solicitud_id' => $solicitudConElegida->id,
            'proveedor_id' => $proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
            'es_elegida' => true,
        ]);

        $resultado = app(ObtenerSolicitudesParaComparar::class)->execute();

        expect($resultado->pluck('id'))->not->toContain($solicitudConElegida->id);
    });

    it('excluye solicitudes que ya tienen una orden de compra', function () {
        $solicitudConOrden = Solicitud::create([
            'codigo' => 'S-TEST-013',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Con orden',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        OrdenCompra::factory()->create([
            'solicitud_id' => $solicitudConOrden->id,
        ]);

        $resultado = app(ObtenerSolicitudesParaComparar::class)->execute();

        expect($resultado->pluck('id'))->not->toContain($solicitudConOrden->id);
    });

    it('carga related cotizaciones_count', function () {
        Solicitud::create([
            'codigo' => 'S-TEST-014',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Con count',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        $resultado = app(ObtenerSolicitudesParaComparar::class)->execute();

        expect($resultado->first()->cotizaciones_count)->toBe(0);
    });

    it('limita a 50 resultados', function () {
        for ($i = 0; $i < 60; $i++) {
            Solicitud::create([
                'codigo' => 'S-BULK-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'colaborador_id' => Colaborador::factory()->create()->id,
                'departamento_solicitante_id' => $this->departamento->id,
                'fecha_solicitud' => now(),
                'motivo' => "Bulk $i",
                'estado' => EstadoSolicitud::Aprobada,
            ]);
        }

        $resultado = app(ObtenerSolicitudesParaComparar::class)->execute();

        expect($resultado)->toHaveCount(50);
    });
});

describe('ObtenerSolicitudParaComparativa', function () {
    it('retorna solicitud con relaciones profundas', function () {
        $solicitud = Solicitud::create([
            'codigo' => 'S-TEST-015',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->departamento->id,
            'fecha_solicitud' => now(),
            'motivo' => 'Comparativa',
            'estado' => EstadoSolicitud::Aprobada,
        ]);

        $solicitud->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad_solicitada' => 10,
        ]);

        $proveedor = Proveedor::factory()->create();
        $cotizacion = Cotizacion::create([
            'solicitud_id' => $solicitud->id,
            'proveedor_id' => $proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
        ]);

        $resultado = app(ObtenerSolicitudParaComparativa::class)->execute($solicitud->id);

        expect($resultado)->not->toBeNull();
        expect($resultado->relationLoaded('items'))->toBeTrue();
        expect($resultado->relationLoaded('cotizaciones'))->toBeTrue();
        expect($resultado->cotizaciones->first()->relationLoaded('items'))->toBeTrue();
        expect($resultado->cotizaciones->first()->relationLoaded('proveedor'))->toBeTrue();
    });

    it('retorna null cuando la solicitud no existe', function () {
        $resultado = app(ObtenerSolicitudParaComparativa::class)->execute(99999);

        expect($resultado)->toBeNull();
    });
});
