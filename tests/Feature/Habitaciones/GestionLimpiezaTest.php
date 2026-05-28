<?php

declare(strict_types=1);

namespace Tests\Feature\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\User;
use App\UseCases\Limpieza\Mutations\RegistrarSolicitudLimpieza;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Crea una habitación de prueba.
 */
function crearHabitacionPrueba(
    int $numero = 301,
    ?int $categoriaId = null,
    ?int $ubicacionId = null,
    EstadoHabitacion $estado = EstadoHabitacion::Activa,
): Habitacion {
    return Habitacion::create([
        'codigo' => 'HAB-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
        'numero' => $numero,
        'slug' => "habitacion-{$numero}",
        'nombre' => "Habitación {$numero}",
        'descripcion' => 'Habitación estándar de prueba',
        'categoria_id' => $categoriaId,
        'ubicacion_id' => $ubicacionId,
        'estado' => $estado,
    ]);
}

/**
 * Crea un espacio de prueba.
 */
function crearEspacioPrueba(
    int $ubicacionId
): Espacio {
    return Espacio::create([
        'codigo' => 'ESP-MESA-001',
        'nombre' => 'Mesa Terraza 1',
        'descripcion' => 'Mesa de prueba',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'ubicacion_id' => $ubicacionId,
        'estado' => EstadoEspacio::Disponible,
    ]);
}

/**
 * @return array{categoria: Catalogo, ubicacion: Ubicacion, useCase: RegistrarSolicitudLimpieza, user: User}
 */
function contextoGestionLimpieza(): array
{
    $categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();
    $useCase = app(RegistrarSolicitudLimpieza::class);
    $user = User::factory()->create();

    return compact('categoria', 'ubicacion', 'useCase', 'user');
}

// ─── Setup ───────────────────────────────────────────────────────────────────

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);
});

// ─── Tests ───────────────────────────────────────────────────────────────────

it('puede registrar una solicitud de limpieza para una habitacion y cambia el estado a Sucia', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion, 'useCase' => $useCase] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Activa);

    $solicitud = $useCase->execute(
        limpiable: $habitacion,
        prioridad: 'alta',
        notas: 'Huésped hizo check-out, limpiar a fondo',
    );

    // Validar que la solicitud se creó correctamente
    expect($solicitud)->not->toBeNull()
        ->and($solicitud->limpiable_type)->toBe(Habitacion::class)
        ->and($solicitud->limpiable_id)->toBe($habitacion->id)
        ->and($solicitud->prioridad)->toBe('alta')
        ->and($solicitud->estado)->toBe('pendiente')
        ->and($solicitud->notas)->toBe('Huésped hizo check-out, limpiar a fondo')
        ->and($solicitud->personal_id)->toBeNull();

    // Validar que la habitación cambió su estado a Sucia (6)
    $habitacion->refresh();
    expect($habitacion->estado)->toBe(EstadoHabitacion::Sucia);
});

it('puede registrar una solicitud de limpieza para un espacio y cambia el estado a Limpieza', function () {
    ['ubicacion' => $ubicacion, 'useCase' => $useCase] = contextoGestionLimpieza();

    $espacio = crearEspacioPrueba($ubicacion->id);

    $solicitud = $useCase->execute(
        limpiable: $espacio,
        prioridad: 'normal',
        notas: 'Mesa desocupada, desinfectar',
    );

    // Validar que la solicitud se creó correctamente
    expect($solicitud)->not->toBeNull()
        ->and($solicitud->limpiable_type)->toBe(Espacio::class)
        ->and($solicitud->limpiable_id)->toBe($espacio->id)
        ->and($solicitud->prioridad)->toBe('normal')
        ->and($solicitud->estado)->toBe('pendiente');

    // Validar que el espacio cambió su estado a Limpieza (3)
    $espacio->refresh();
    expect($espacio->estado)->toBe(EstadoEspacio::Limpieza);
});

it('despacha notificaciones en base de datos al registrar una solicitud', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion, 'useCase' => $useCase, 'user' => $user] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Activa);

    // Asegurar que el usuario tiene el permiso de limpieza o es super_admin
    $user->assignRole(
        Role::firstOrCreate(['name' => 'super_admin'])
    );

    // Registrar solicitud
    $useCase->execute($habitacion);

    // Validar que se registró la notificación en base de datos para el usuario
    expect($user->notifications()->count())->toBe(1);

    $notification = $user->notifications()->first();
    expect($notification->data['title'])->toBe('Nueva Solicitud de Limpieza')
        ->and($notification->data['body'])->toContain('Habitación 301');
});

it('notifica al personal de limpieza de forma especifica cuando se crea asignada', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Activa);
    $personal = User::factory()->create();

    // Crear un supervisor con el permiso para que el notifier no caiga en el fallback general
    $supervisor = User::factory()->create();
    $permission = Permission::findOrCreate('Update:SolicitudLimpieza');
    $supervisor->givePermissionTo($permission);

    // Crear la solicitud asignando directamente al personal
    $habitacion->solicitudesLimpieza()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $habitacion->id,
        'prioridad' => 'normal',
        'estado' => 'pendiente',
        'personal_id' => $personal->id,
    ]);

    // El personal debe recibir la notificación específica de asignación
    expect($personal->notifications()->count())->toBe(1);
    $notification = $personal->notifications()->first();
    expect($notification->data['title'])->toBe('Nueva Tarea de Limpieza Asignada')
        ->and($notification->data['body'])->toContain('Habitación 301');

    // El supervisor debe recibir la general
    expect($supervisor->notifications()->count())->toBe(1);
});

it('notifica al personal de limpieza de forma especifica cuando se actualiza la asignacion', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Activa);
    $personal = User::factory()->create();

    // Crear un supervisor con el permiso para que el notifier no caiga en el fallback general
    $supervisor = User::factory()->create();
    $permission = Permission::findOrCreate('Update:SolicitudLimpieza');
    $supervisor->givePermissionTo($permission);

    // Crear la solicitud sin personal
    $solicitud = $habitacion->solicitudesLimpieza()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $habitacion->id,
        'prioridad' => 'normal',
        'estado' => 'pendiente',
    ]);

    // No debe tener notificaciones el personal todavía
    expect($personal->notifications()->count())->toBe(0);
    // El supervisor sí debe recibir la notificación de creación
    expect($supervisor->notifications()->count())->toBe(1);

    // Asignar el personal mediante una actualización
    $solicitud->update([
        'personal_id' => $personal->id,
    ]);

    // Ahora debe recibir la notificación específica de asignación
    expect($personal->notifications()->count())->toBe(1);
    $notification = $personal->notifications()->first();
    expect($notification->data['title'])->toBe('Nueva Tarea de Limpieza Asignada')
        ->and($notification->data['body'])->toContain('Habitación 301');
});

it('registra el historial de cambio de estado en la habitacion automáticamente', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion, 'useCase' => $useCase] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Activa);

    // Ejecutar el caso de uso
    $useCase->execute($habitacion, null, 'normal', 'Limpieza estándar');

    // Verificar que existe una transición registrada en el historial
    $habitacion->load('historial');

    expect($habitacion->historial)->toHaveCount(2); // 1 de creación (Activa) + 1 de actualización (Sucia)

    $ultimoHistorial = $habitacion->historial->sortByDesc('id')->first();

    expect($ultimoHistorial->estado_anterior)->toBe('Activa')
        ->and($ultimoHistorial->estado_nuevo)->toBe('Sucia');
});

it('inicia la limpieza de una habitacion correctamente', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion, 'user' => $user] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Sucia);

    $solicitud = $habitacion->solicitudesLimpieza()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $habitacion->id,
        'prioridad' => 'normal',
        'estado' => 'pendiente',
    ]);

    // Autenticar al usuario
    $this->actingAs($user);

    // Simular el inicio de limpieza
    $solicitud->update([
        'estado' => 'en_progreso',
        'personal_id' => $user->id,
    ]);

    $solicitud->limpiable->update([
        'estado' => EstadoHabitacion::EN_LIMPIEZA,
    ]);

    // Validaciones
    $solicitud->refresh();
    expect($solicitud->estado)->toBe('en_progreso')
        ->and($solicitud->personal_id)->toBe($user->id);

    $habitacion->refresh();
    expect($habitacion->estado)->toBe(EstadoHabitacion::Limpieza); // En Limpieza (3)

    // Validar historial de estado
    $habitacion->load('historial');
    $ultimoHistorial = $habitacion->historial->sortByDesc('id')->first();
    expect($ultimoHistorial->estado_anterior)->toBe('Sucia')
        ->and($ultimoHistorial->estado_nuevo)->toBe('Limpieza');
});

it('completa la limpieza de una habitacion correctamente', function () {
    ['categoria' => $categoria, 'ubicacion' => $ubicacion, 'user' => $user] = contextoGestionLimpieza();

    $habitacion = crearHabitacionPrueba(301, $categoria->id, $ubicacion->id, EstadoHabitacion::Limpieza);

    $solicitud = $habitacion->solicitudesLimpieza()->create([
        'limpiable_type' => Habitacion::class,
        'limpiable_id' => $habitacion->id,
        'personal_id' => $user->id,
        'prioridad' => 'normal',
        'estado' => 'en_progreso',
    ]);

    // Autenticar al usuario
    $this->actingAs($user);

    // Simular el término de limpieza
    $solicitud->update([
        'estado' => 'completada',
    ]);

    $solicitud->limpiable->update([
        'estado' => EstadoHabitacion::DISPONIBLE, // Disponible/Activa (1)
    ]);

    // Validaciones
    $solicitud->refresh();
    expect($solicitud->estado)->toBe('completada');

    $habitacion->refresh();
    expect($habitacion->estado)->toBe(EstadoHabitacion::Activa);

    // Validar historial de estado
    $habitacion->load('historial');
    $ultimoHistorial = $habitacion->historial->sortByDesc('id')->first();
    expect($ultimoHistorial->estado_anterior)->toBe('Limpieza')
        ->and($ultimoHistorial->estado_nuevo)->toBe('Activa');
});
