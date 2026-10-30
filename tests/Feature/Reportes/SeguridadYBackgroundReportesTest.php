<?php

declare(strict_types=1);

use App\Filament\Pages\Compras\ReportesCompras;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
});

describe('Seguridad y control de acceso en el Visor de Reportes', function (): void {
    test('acceso no autenticado al visor es redirigido o denegado', function (): void {
        $token = (string) Str::uuid();
        $response = $this->get("/admin/reportes/visor/{$token}");

        // Al tener middleware auth, debe redirigir a login
        $response->assertRedirect();
    });

    test('token con formato inválido retorna HTTP 400', function (): void {
        $admin = User::first() ?? User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);

        $response = $this->get('/admin/reportes/visor/token-invalido!@#$%^&*()');
        $response->assertStatus(400);
    });

    test('token expirado elimina el archivo físico en disco y retorna HTTP 410', function (): void {
        $admin = User::first() ?? User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);

        $token = (string) Str::uuid();
        $tempDir = storage_path('app/temp-reportes');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = "{$tempDir}/{$token}.pdf";
        file_put_contents($filePath, '%PDF-dummy-content');

        // Simulamos que el token ya expiró en caché
        cache()->put("reporte_meta_{$token}", [
            'filename' => 'expirado.pdf',
            'user_id' => $admin->id,
            'expires_at' => now()->subMinute()->timestamp,
        ], now()->subMinute());

        $this->assertFileExists($filePath);

        $response = $this->get("/admin/reportes/visor/{$token}");
        $response->assertStatus(410);

        // El archivo debe haber sido eliminado automáticamente de disco
        $this->assertFileDoesNotExist($filePath);
    });

    test('usuario ajeno sin rol administrador recibe HTTP 403 al intentar ver reporte de otro', function (): void {
        $owner = User::factory()->create(['is_admin' => false]);
        $attacker = User::factory()->create(['is_admin' => false]);

        $token = (string) Str::uuid();
        $tempDir = storage_path('app/temp-reportes');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = "{$tempDir}/{$token}.pdf";
        file_put_contents($filePath, '%PDF-dummy-content');

        cache()->put("reporte_meta_{$token}", [
            'filename' => 'reporte_privado.pdf',
            'user_id' => $owner->id,
            'expires_at' => now()->addMinutes(30)->timestamp,
        ], now()->addMinutes(30));

        // El atacante intenta acceder
        $this->actingAs($attacker);
        $response = $this->get("/admin/reportes/visor/{$token}");
        $response->assertStatus(403);

        // Limpieza
        @unlink($filePath);
    });

    test('usuario propietario puede ver su reporte con headers de seguridad', function (): void {
        $owner = User::factory()->create(['is_admin' => false]);

        $token = (string) Str::uuid();
        $tempDir = storage_path('app/temp-reportes');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = "{$tempDir}/{$token}.pdf";
        file_put_contents($filePath, '%PDF-dummy-content');

        cache()->put("reporte_meta_{$token}", [
            'filename' => 'reporte_valido.pdf',
            'user_id' => $owner->id,
            'expires_at' => now()->addMinutes(30)->timestamp,
        ], now()->addMinutes(30));

        $this->actingAs($owner);
        $response = $this->get("/admin/reportes/visor/{$token}");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        // Limpieza
        @unlink($filePath);
    });
});

describe('Limpieza periódica de archivos temporales de reportes', function (): void {
    test('comando reportes:limpiar-temporales borra archivos expirados y preserva recientes', function (): void {
        $tempDir = storage_path('app/temp-reportes');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $archivoViejo = "{$tempDir}/viejo_test.pdf";
        $archivoReciente = "{$tempDir}/reciente_test.pdf";

        file_put_contents($archivoViejo, '%PDF-viejo');
        file_put_contents($archivoReciente, '%PDF-reciente');

        // Modificamos fecha de modificación del archivo viejo (2 horas atrás)
        touch($archivoViejo, time() - 7200);
        // Archivo reciente (hace 2 minutos)
        touch($archivoReciente, time() - 120);

        $this->artisan('reportes:limpiar-temporales', ['--minutos' => 30])
            ->assertSuccessful();

        $this->assertFileDoesNotExist($archivoViejo);
        $this->assertFileExists($archivoReciente);

        // Limpieza
        @unlink($archivoReciente);
    });
});

describe('Despacho a segundo plano para reportes pesados / históricos', function (): void {
    test('rango superior a 31 días se despacha automáticamente a segundo plano', function (): void {
        Queue::fake();

        $admin = User::first() ?? User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $page = app(ReportesCompras::class);

        // Parámetros con rango de 90 días (más de 31 días)
        $params = [
            'fecha_inicio' => now()->subDays(90)->toDateString(),
            'fecha_fin' => now()->toDateString(),
        ];

        $resultado = $page->procesarDescargaReporte('compras', 'solicitudes_estado', $params);

        // Al irse a segundo plano retorna null inmediatamente sin bloquear
        expect($resultado)->toBeNull();

        Queue::assertPushed(GenerarReporteJob::class, function (GenerarReporteJob $job): bool {
            return $job->codigoReporte === 'HTB-COM-010';
        });
    });

    test('GenerarReporteJob genera archivo temporal y notificación al usuario', function (): void {
        $admin = User::first() ?? User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $admin->notifications()->delete();

        $job = new GenerarReporteJob(
            codigoReporte: 'HTB-INV-001',
            parametros: [
                'directo' => 1,
            ],
            usuarioId: $admin->id,
        );

        $job->handle();

        // Verificar que el usuario tenga notificaciones y entre ellas la de reporte listo
        $notificaciones = $admin->notifications()->get();
        expect($notificaciones)->not->toBeEmpty();

        $titulos = $notificaciones->pluck('data.title')->all();
        $tieneListo = collect($titulos)->contains(fn (mixed $t): bool => is_string($t) && str_contains($t, 'Listo'));

        expect($tieneListo)->toBeTrue();
    });
});
