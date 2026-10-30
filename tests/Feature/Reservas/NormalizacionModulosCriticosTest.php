<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Clientes\GestionarAcompanantesReserva;
use App\Interactors\Clientes\ObtenerDetalleReservaPortal;
use App\Interactors\Habitaciones\SincronizarVistasHabitacion;
use App\Interactors\Reservas\Gestion\RegistrarHuespedes;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\User;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorio;
use App\Repository\Persistencia\Reservas\AcompanantesReservaRepositorio;
use App\Repository\Persistencia\Reservas\ReservaDetalleRepositorio;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

/** @return array{reserva: Reserva, habitaciones: array<int, Habitacion>, detalles: array<int, ReservaDetalle>} */
function reservaNormalizacionCriticos(bool $doble = false): array
{
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-NORM-'.str()->random(8), 'nombre_cliente' => 'Persona de prueba',
        'tipo_reserva' => TipoReserva::HABITACION, 'estado' => EstadoReserva::CONFIRMADA,
        'fecha_check_in' => '2026-11-01', 'fecha_check_out' => '2026-11-03', 'adultos' => 6, 'ninos' => 2,
    ]);
    $habitaciones = [];
    $detalles = [];
    for ($i = 0; $i < ($doble ? 2 : 1); $i++) {
        $recurso = RecursoReservable::query()->create(['tipo' => 1, 'nombre' => 'Alojamiento '.$i, 'control_disponibilidad' => 1, 'estado' => 1]);
        $habitacion = Habitacion::factory()->create(['reservable_id' => $recurso->id]);
        $habitacion->detalle()->create(['capacidad_adultos' => 3, 'capacidad_ninos' => 2]);
        $detalle = $reserva->detalles()->create([
            'reservable_id' => $recurso->id, 'parent_id' => $i > 0 ? $detalles[0]->id : null,
            'fecha_inicio' => '2026-11-01', 'fecha_fin' => '2026-11-03', 'estado' => 2,
        ]);
        $habitaciones[] = $habitacion;
        $detalles[] = $detalle;
    }

    return compact('reserva', 'habitaciones', 'detalles');
}

test('las vistas se guardan en relaciones y rechazan catalogos de otra clase', function (): void {
    $f = reservaNormalizacionCriticos();
    $habitacion = $f['habitaciones'][0];
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_VISTA', 'nombre' => 'Vistas', 'estado' => 1]);
    $vista = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'codigo' => 'JARDIN', 'nombre' => 'Vista al jardín', 'estado' => 1]);
    app(SincronizarVistasHabitacion::class)->ejecutar($habitacion, [$vista->id]);
    expect($habitacion->vistas()->pluck('catalogos.id')->all())->toBe([$vista->id]);
    expect(fn () => app(SincronizarVistasHabitacion::class)->ejecutar($habitacion, [$habitacion->categoria_id]))->toThrow(ValidationException::class);
    expect($habitacion->vistas()->pluck('catalogos.id')->all())->toBe([$vista->id]);
});

test('clonar una habitacion conserva sus vistas normalizadas', function (): void {
    $f = reservaNormalizacionCriticos();
    $habitacion = $f['habitaciones'][0];
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_VISTA', 'nombre' => 'Vistas', 'estado' => 1]);
    $vista = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'codigo' => 'PISCINA', 'nombre' => 'Piscina', 'estado' => 1]);
    app(SincronizarVistasHabitacion::class)->ejecutar($habitacion, [$vista->id]);
    $clon = DB::transaction(fn () => app(HabitacionRepositorio::class)->clonar($habitacion, 9901, 'Clon de prueba'));
    expect($clon->vistas()->pluck('catalogos.id')->all())->toBe([$vista->id]);
});

test('una reserva multiple exige destino y revierte todo si falta', function (): void {
    $f = reservaNormalizacionCriticos(true);
    expect(fn () => DB::transaction(fn () => app(AcompanantesReservaRepositorio::class)->reemplazar($f['reserva'], [['nombre' => 'Persona', 'tipo' => 'adulto']])))
        ->toThrow(DomainException::class, 'Seleccione');
    expect($f['reserva']->huespedes()->count())->toBe(0);
});

test('recepcion registra al huesped en la habitacion elegida', function (): void {
    $f = reservaNormalizacionCriticos(true);
    $h = app(RegistrarHuespedes::class)->agregar($f['reserva'], ['nombre' => 'Persona', 'identificacion' => 'DOC-RECEPCION', 'tipo_huesped' => 1, 'reserva_detalle_id' => $f['detalles'][1]->id]);
    expect($h->reserva_detalle_id)->toBe($f['detalles'][1]->id);
    expect(fn () => app(RegistrarHuespedes::class)->agregar($f['reserva'], ['nombre' => 'Sin destino', 'tipo_huesped' => 1]))->toThrow(DomainException::class);
});

test('editar acompanantes conserva el identificador y los datos propios de la persona', function (): void {
    $f = reservaNormalizacionCriticos();
    $h = $f['detalles'][0]->huespedes()->create(['nombre' => 'Persona', 'tipo_huesped' => 1, 'es_titular' => false, 'identificacion' => 'DOC-01', 'telefono' => '55555555', 'fecha_nacimiento' => '1990-02-03']);
    DB::transaction(fn () => app(AcompanantesReservaRepositorio::class)->reemplazar($f['reserva'], [['huesped_id' => $h->id, 'nombre' => 'Persona revisada', 'identificacion' => 'DOC-01', 'tipo' => 'adulto']]));
    expect($h->refresh()->nombre)->toBe('Persona revisada')->and($h->telefono)->toBe('55555555')->and($h->fecha_nacimiento->format('Y-m-d'))->toBe('1990-02-03');
    expect($f['reserva']->huespedes()->count())->toBe(1);
});

test('no se pueden enviar personas ni destinos de otra reserva', function (): void {
    $f = reservaNormalizacionCriticos();
    $otra = reservaNormalizacionCriticos();
    $ajeno = $otra['detalles'][0]->huespedes()->create(['nombre' => 'Ajeno', 'tipo_huesped' => 1]);
    expect(fn () => DB::transaction(fn () => app(AcompanantesReservaRepositorio::class)->reemplazar($f['reserva'], [['nombre' => 'Persona', 'tipo' => 'adulto', 'reserva_detalle_id' => $otra['detalles'][0]->id]])))->toThrow(DomainException::class);
    expect(fn () => DB::transaction(fn () => app(AcompanantesReservaRepositorio::class)->reemplazar($f['reserva'], [['nombre' => 'Persona', 'tipo' => 'adulto', 'huesped_id' => $ajeno->id]])))->toThrow(DomainException::class);
    expect(fn () => app(RegistrarHuespedes::class)->eliminar($f['reserva'], $ajeno))->toThrow(DomainException::class);
    expect($ajeno->fresh())->not->toBeNull();
});

test('la sincronizacion de acompanantes conserva al titular', function (): void {
    $f = reservaNormalizacionCriticos();
    $titular = $f['detalles'][0]->huespedes()->create(['nombre' => 'Titular', 'tipo_huesped' => 1, 'es_titular' => true]);
    DB::transaction(fn () => app(AcompanantesReservaRepositorio::class)->reemplazar($f['reserva'], []));
    expect($titular->fresh())->not->toBeNull()->and($f['reserva']->huespedes()->count())->toBe(1);
});

test('editar adicionales conserva el detalle y los huespedes existentes', function (): void {
    $f = reservaNormalizacionCriticos(true);
    $h = $f['detalles'][1]->huespedes()->create(['nombre' => 'Adicional', 'tipo_huesped' => 1]);
    DB::transaction(fn () => app(ReservaDetalleRepositorio::class)->reemplazarAdicionales($f['reserva'], $f['detalles'][0], [], [], [['habitacion_id' => $f['habitaciones'][1]->id, 'cantidad' => 1, 'precio' => 100.0]]));
    expect($f['detalles'][1]->fresh()->deleted_at)->toBeNull()->and($h->refresh()->reserva_detalle_id)->toBe($f['detalles'][1]->id);
});

test('retirar un alojamiento ocupado requiere reasignar antes a sus huespedes', function (): void {
    $f = reservaNormalizacionCriticos(true);
    $f['detalles'][1]->huespedes()->create(['nombre' => 'Adicional', 'tipo_huesped' => 1]);
    expect(fn () => DB::transaction(fn () => app(ReservaDetalleRepositorio::class)->reemplazarAdicionales($f['reserva'], $f['detalles'][0], [], [], [])))->toThrow(DomainException::class, 'Reasigne');
    expect($f['detalles'][1]->fresh()->deleted_at)->toBeNull();
});

test('reversion y reaplicacion conservan las vistas y no duplican personas', function (): void {
    $f = reservaNormalizacionCriticos();
    DB::transaction(fn () => app(AcompanantesReservaRepositorio::class)->reemplazar($f['reserva'], [['nombre' => 'Historico', 'tipo' => 'bebe']]));
    $m = require database_path('migrations/Reservas/2026_10_30_000003_normalize_reserva_acompanantes.php');
    $m->down();
    $m->up();
    expect($f['reserva']->huespedes()->count())->toBe(1)->and($f['reserva']->fresh()->acompanantes[0]['tipo'])->toBe('bebe');
    $m = require database_path('migrations/Habitaciones/2026_10_30_000002_normalize_habitacion_vistas.php');
    $m->down();
    $m->up();
    expect($f['habitaciones'][0]->vistas()->count())->toBe(0);
});

test('la base impide vincular una cuenta a una estancia de otra reserva', function (): void {
    $f = reservaNormalizacionCriticos();
    $otra = reservaNormalizacionCriticos();
    $estancia = DB::table('estancias')->insertGetId(['reserva_id' => $otra['reserva']->id, 'check_in_at' => now()]);
    expect(fn () => DB::transaction(fn () => DB::table('cuentas')->insert(['numero_cuenta' => 'CTA-AJENA', 'abierta_at' => now(), 'estancia_id' => $estancia, 'reserva_id' => $f['reserva']->id])))->toThrow(QueryException::class);
});

test('actualizar un huesped conserva su propio documento y titular sin contarlo dos veces', function (): void {
    $f = reservaNormalizacionCriticos();
    $h = $f['detalles'][0]->huespedes()->create(['nombre' => 'Titular', 'tipo_huesped' => 1, 'es_titular' => true, 'identificacion' => 'DOC-TITULAR']);
    $actualizado = app(RegistrarHuespedes::class)->actualizar($f['reserva'], $h, ['nombre' => 'Titular revisado']);
    expect($actualizado->id)->toBe($h->id)->and($actualizado->identificacion)->toBe('DOC-TITULAR')->and($actualizado->es_titular)->toBeTrue();
});

test('al mover un huesped se valida la capacidad del destino elegido', function (): void {
    $f = reservaNormalizacionCriticos(true);
    $f['habitaciones'][1]->detalle()->update(['capacidad_adultos' => 1]);
    $h = $f['detalles'][0]->huespedes()->create(['nombre' => 'Movil', 'tipo_huesped' => 1, 'identificacion' => 'DOC-MOVIL']);
    $f['detalles'][1]->huespedes()->create(['nombre' => 'Ocupante', 'tipo_huesped' => 1, 'identificacion' => 'DOC-OCUPANTE']);
    expect(fn () => app(RegistrarHuespedes::class)->actualizar($f['reserva'], $h, ['reserva_detalle_id' => $f['detalles'][1]->id]))->toThrow(DomainException::class);
    expect($h->refresh()->reserva_detalle_id)->toBe($f['detalles'][0]->id);
});

test('un permiso de consulta no permite modificar los acompanantes de otra reserva', function (): void {
    $f = reservaNormalizacionCriticos();
    $user = User::factory()->create(['is_admin' => false]);
    Permission::findOrCreate('View:Reserva', 'web');
    $user->givePermissionTo('View:Reserva');
    expect(fn () => app(GestionarAcompanantesReserva::class)->ejecutar($f['reserva']->id, [['nombre' => 'No autorizado', 'tipo' => 'adulto']], $user))->toThrow(DomainException::class, 'permiso');
    expect($f['reserva']->huespedes()->count())->toBe(0);
});

test('el propietario actualiza acompanantes y el portal lee la misma identidad que recepcion', function (): void {
    $f = reservaNormalizacionCriticos();
    $user = User::factory()->create(['is_admin' => false]);
    $f['reserva']->update(['email_cliente' => $user->email]);
    app(GestionarAcompanantesReserva::class)->ejecutar($f['reserva']->id, [['nombre' => 'Persona compartida', 'tipo' => 'adulto']], $user);
    $portal = app(ObtenerDetalleReservaPortal::class)->ejecutar($f['reserva']->id, $user);
    expect($portal['acompanantes'][0]['huesped_id'])->toBe($f['reserva']->huespedes()->firstOrFail()->id);
});
