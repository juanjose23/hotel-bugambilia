<?php

declare(strict_types=1);

namespace Tests\Feature\Reportes;

use App\Models\User;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use App\UseCases\Reportes\Queries\ObtenerDatosBaseReporteUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create(['name' => 'Admin Test User']);
    $this->actingAs($this->user);
});

it('registra auditoria de reporte correctamente', function () {
    $useCase = app(RegistrarAuditoriaReporteUseCase::class);
    $useCase->execute(
        tipoReporte: 'HTB-TEST-001',
        parametros: ['filtro1' => 'valor1'],
        rutaArchivo: 'exports/test.pdf'
    );

    // Verify record in database
    $registro = DB::table('auditoria_reportes')->where('tipo_reporte', 'HTB-TEST-001')->first();

    expect($registro)->not->toBeNull()
        ->and($registro->usuario_id)->toBe($this->user->id)
        ->and(json_decode($registro->parametros, true))->toBe(['filtro1' => 'valor1'])
        ->and($registro->ruta_archivo)->toBe('exports/test.pdf')
        ->and($registro->conteo_descargas)->toBe(1);
});

it('obtiene datos base de reporte y registra auditoria automaticamente', function () {
    $useCase = app(ObtenerDatosBaseReporteUseCase::class);

    // Dummy record object
    $dummyRecord = new \stdClass;
    $dummyRecord->id = 99;
    $dummyRecord->codigo = 'REC-999';

    $data = $useCase->execute(
        codigoReporte: 'HTB-TEST-002',
        record: $dummyRecord,
        filtros: ['categoria' => 'Lujo']
    );

    // Verify returned base data
    expect($data)->toHaveKey('logo_base64')
        ->and($data)->toHaveKey('hotelInfo')
        ->and($data['usuario'])->toBe('Admin Test User')
        ->and($data['record']->id)->toBe(99);

    // Verify auditoria was registered automatically
    $registro = DB::table('auditoria_reportes')->where('tipo_reporte', 'HTB-TEST-002')->first();

    expect($registro)->not->toBeNull()
        ->and($registro->usuario_id)->toBe($this->user->id);

    $params = json_decode($registro->parametros, true);
    expect($params['categoria'])->toBe('Lujo')
        ->and($params['id'])->toBe(99)
        ->and($params['codigo'])->toBe('REC-999');
});
