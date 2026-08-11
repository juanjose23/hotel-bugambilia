<?php

declare(strict_types=1);

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Interactors\Limpieza\Ejecucion\AsignarSolicitudLimpieza;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Permission;
use Tests\Feature\Limpieza\Helpers\LimpiezaTestHelpers;

uses(LimpiezaTestHelpers::class);

beforeEach(function (): void {
    $this->colaborador = Colaborador::factory()->create();
    $this->user = User::factory()->create(['persona_id' => $this->colaborador->persona_id]);
    $this->user->givePermissionTo(
        Permission::findOrCreate('page_TableroLimpieza', 'web')
    );

    $this->ubicacion = Ubicacion::query()->create([
        'nombre' => 'Piso 1',
        'tipo' => 'piso',
        'estado' => 1,
    ]);
    $this->categoria = Catalogo::factory()->create();
    $this->habitacion = $this->crearHabitacionLimpieza(
        categoriaId: $this->categoria->id,
        ubicacionId: $this->ubicacion->id,
    );
    $this->solicitud = $this->crearSolicitudLimpieza($this->habitacion);

    $this->turno = Turno::factory()->create(['estado' => true]);
});

it('crea la ejecución de limpieza cuando la solicitud no tiene una', function (): void {
    $resultado = app(AsignarSolicitudLimpieza::class)->execute($this->user, (int) $this->solicitud->id);

    expect($resultado)
        ->toBeInstanceOf(LimpiezaEjecucion::class)
        ->and($resultado->solicitud_id)->toBe($this->solicitud->id)
        ->and($resultado->limpiable_id)->toBe($this->habitacion->id)
        ->and($resultado->colaborador_id)->toBe($this->colaborador->id)
        ->and($resultado->estado)->toBe(EstadoLimpieza::Pendiente);

    expect($this->solicitud->refresh()->personal_id)->toBe($this->user->id);
});

it('resuelve el turno activo cuyo carrito coincide con la ubicación del limpiable', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito Piso 1',
        'tipo' => 'carrito',
        'estado' => 1,
        'padre_id' => $this->ubicacion->id,
    ]);
    $turno = Turno::factory()->create(['estado' => true]);
    $turno->carritos()->attach($carrito->id);

    $resultado = app(AsignarSolicitudLimpieza::class)->execute($this->user, (int) $this->solicitud->id);

    expect($resultado->turno_id)->toBe($turno->id);
});

it('reutiliza la ejecución existente y le asigna el colaborador del usuario', function (): void {
    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create([
        'solicitud_id' => $this->solicitud->id,
        'limpiable_type' => $this->solicitud->limpiable_type,
        'limpiable_id' => $this->solicitud->limpiable_id,
    ]);

    $resultado = app(AsignarSolicitudLimpieza::class)->execute($this->user, (int) $this->solicitud->id);

    expect($resultado->id)->toBe($ejecucion->id)
        ->and($resultado->colaborador_id)->toBe($this->colaborador->id);
});

it('lanza una excepción cuando el usuario no tiene permisos', function (): void {
    $sinPermisos = User::factory()->create();

    expect(fn () => app(AsignarSolicitudLimpieza::class)->execute($sinPermisos, (int) $this->solicitud->id))
        ->toThrow(OperacionLimpiezaNoPermitida::class);
});

it('lanza una excepción cuando la solicitud no existe', function (): void {
    expect(fn () => app(AsignarSolicitudLimpieza::class)->execute($this->user, 999999))
        ->toThrow(ModelNotFoundException::class);
});
