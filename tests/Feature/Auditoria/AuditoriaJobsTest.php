<?php

declare(strict_types=1);

namespace Tests\Feature\Auditoria;

use App\Enums\Shared\EstadoEjecucionJob;
use App\Enums\Shared\TipoJob;
use App\Filament\Resources\Auditoria\AuditoriaJobs\Pages\ListAuditoriaJobs;
use App\Interactors\Auditoria\EjecutarJobManual;
use App\Repository\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Asegurar directorios temporales limpios para la prueba
    $dirs = [
        storage_path('app/temp-reportes'),
        storage_path('app/public/reports'),
    ];
    foreach ($dirs as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
});

describe('TipoJob LimpiezaReportesPdf Enum', function (): void {
    test('está configurado con los metadatos correctos', function (): void {
        $tipo = TipoJob::LimpiezaReportesPdf;

        expect($tipo->value)->toBe('limpieza_reportes_pdf')
            ->and($tipo->getLabel())->toBe('Limpiar Reportes PDF Temporales')
            ->and($tipo->getColor())->toBe('warning')
            ->and($tipo->claseJob())->toBe('reportes:limpiar-temporales')
            ->and($tipo->esComando())->toBeTrue()
            ->and($tipo->horarioConfigurado())->toBe('hourly');
    });
});

describe('EjecutarJobManual con LimpiezaReportesPdf', function (): void {
    test('limpia archivos expirados tanto en temp-reportes como en public/reports', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tempDir = storage_path('app/temp-reportes');
        $reportsDir = storage_path('app/public/reports');

        $archivoTempViejo = "{$tempDir}/reporte_temp_expirado.pdf";
        $archivoReportsViejo = "{$reportsDir}/reporte_public_expirado.pdf";
        $archivoReciente = "{$reportsDir}/reporte_reciente.pdf";

        file_put_contents($archivoTempViejo, 'dummy temp');
        touch($archivoTempViejo, time() - 3600); // 1 hora de antigüedad

        file_put_contents($archivoReportsViejo, 'dummy public reports');
        touch($archivoReportsViejo, time() - 3600); // 1 hora de antigüedad

        file_put_contents($archivoReciente, 'dummy reciente');
        touch($archivoReciente, time()); // recién creado

        $interactor = app(EjecutarJobManual::class);
        $registro = $interactor->ejecutar(TipoJob::LimpiezaReportesPdf);

        expect($registro->estado)->toBe(EstadoEjecucionJob::Completado)
            ->and($registro->tipo_job)->toBe(TipoJob::LimpiezaReportesPdf)
            ->and($registro->nombre_job)->toBe('Limpiar Reportes PDF Temporales')
            ->and($registro->tipo_ejecucion)->toBe('manual')
            ->and($registro->mensaje)->toContain('Limpieza completada')
            ->and(file_exists($archivoTempViejo))->toBeFalse()
            ->and(file_exists($archivoReportsViejo))->toBeFalse()
            ->and(file_exists($archivoReciente))->toBeTrue();

        // Limpiar archivo reciente remanente
        @unlink($archivoReciente);

        $this->assertDatabaseHas('auditoria_jobs', [
            'id' => $registro->id,
            'tipo_job' => 'limpieza_reportes_pdf',
            'estado' => 'completado',
        ]);
    });

    test('permite forzar la eliminación de todos los reportes con --all', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reportsDir = storage_path('app/public/reports');
        $archivoReciente = "{$reportsDir}/reporte_a_borrar_forzado.pdf";
        file_put_contents($archivoReciente, 'dummy');

        $interactor = app(EjecutarJobManual::class);
        $registro = $interactor->ejecutar(TipoJob::LimpiezaReportesPdf, ['--all' => true]);

        expect($registro->estado)->toBe(EstadoEjecucionJob::Completado)
            ->and(file_exists($archivoReciente))->toBeFalse()
            ->and($registro->mensaje)->toContain('todos los archivos');
    });
});

describe('Pantalla ListAuditoriaJobs Filament', function (): void {
    test('permite seleccionar y ejecutar LimpiezaReportesPdf desde la acción ejecutarJob', function (): void {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListAuditoriaJobs::class)
            ->callAction('ejecutarJob', data: [
                'tipo_job' => TipoJob::LimpiezaReportesPdf->value,
                'limpiar_todos' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('auditoria_jobs', [
            'tipo_job' => 'limpieza_reportes_pdf',
            'estado' => 'completado',
        ]);
    });
});
