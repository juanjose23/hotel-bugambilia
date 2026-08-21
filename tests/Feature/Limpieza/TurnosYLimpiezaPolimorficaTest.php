<?php

declare(strict_types=1);

namespace Tests\Feature\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);
use App\BusinessLogic\Limpieza\ValidarCambioColaboradorEjecucion;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\User;
use Carbon\Carbon;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    // Ejecutar seeders necesarios
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $this->ubicacionPadre = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    // Crear ubicaciones de prueba
    $this->zonaPrincipal = Ubicacion::create([
        'tipo' => 'zona',
        'nombre' => 'Zona A - Lobby',
        'orden' => 90,
        'estado' => 1,
    ]);

    $this->zonaSecundaria = Ubicacion::create([
        'tipo' => 'zona',
        'nombre' => 'Zona B - Pasillo Principal',
        'orden' => 91,
        'estado' => 1,
    ]);

    $this->carritoBodega = Ubicacion::create([
        'tipo' => 'almacen',
        'nombre' => 'Carrito Limpieza #1',
        'orden' => 92,
        'estado' => 1,
    ]);

    // Colaboradores
    $this->lider = Colaborador::factory()->create();
    $this->apoyo = Colaborador::factory()->create();

    // Entidades Limpiables (Habitación, Espacio, Ubicación)
    $this->habitacion = Habitacion::create([
        'codigo' => 'HAB-1001',
        'numero' => 1001,
        'slug' => 'habitacion-1001',
        'nombre' => 'Habitación 1001 Deluxe',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacionPadre->id,
        'estado' => EstadoEspacio::SUCIA,
    ]);

    $this->espacio = Espacio::create([
        'codigo' => 'ESP-GYM',
        'nombre' => 'Gimnasio Principal',
        'tipo' => TipoEspacio::GYM,
        'capacidad_personas' => 20,
        'estado' => EstadoEspacio::Disponible,
    ]);
});

describe('Módulo de Turnos de Limpieza (limp_horario_turnos)', function () {
    it('crea un turno con líder, apoyo y múltiples carritos', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Matutino A',
            'lider_id' => $this->lider->id,
            'apoyo_id' => $this->apoyo->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => true,
        ]);

        $turno->carritos()->attach($this->carritoBodega->id);
        $turno->load('carritos');

        expect($turno->nombre)->toBe('Turno Matutino A')
            ->and($turno->lider->id)->toBe($this->lider->id)
            ->and($turno->apoyo->id)->toBe($this->apoyo->id)
            ->and($turno->carritos)->toHaveCount(1)
            ->and($turno->carritos->first()->nombre)->toBe('Carrito Limpieza #1');
    });
});

describe('Planificación de Horarios Polimórficos Cabecera-Detalle (limp_horarios)', function () {
    it('permite planificar limpieza para habitación, espacio y ubicación general', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Vespertino',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '15:00:00',
            'hora_fin' => '23:00:00',
        ]);

        // 1. Horario para Habitación
        $horarioHab = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '16:00:00',
            'frecuencia' => 'diaria',
            'checklist' => ['Tender cama', 'Barrer'],
        ]);
        $horarioHab->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        // 2. Horario para Espacio
        $horarioEsp = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '17:00:00',
            'frecuencia' => 'semanal',
            'dia_semana' => 'lunes',
        ]);
        $horarioEsp->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        // 3. Horario para Ubicación Física
        $horarioUbi = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '18:00:00',
            'frecuencia' => 'diaria',
        ]);
        $horarioUbi->detalles()->create([
            'limpiable_type' => Ubicacion::class,
            'limpiable_id' => $this->zonaPrincipal->id,
        ]);

        expect($horarioHab->detalles->first()->limpiable)->toBeInstanceOf(Habitacion::class)
            ->and($horarioEsp->detalles->first()->limpiable)->toBeInstanceOf(Espacio::class)
            ->and($horarioUbi->detalles->first()->limpiable)->toBeInstanceOf(Ubicacion::class)
            ->and($this->habitacion->horariosLimpieza)->toHaveCount(1)
            ->and($this->espacio->horariosLimpieza)->toHaveCount(1)
            ->and($this->zonaPrincipal->horariosLimpieza)->toHaveCount(1);
    });
});

describe('Comando de Materialización Diaria (limpieza:materializar-ejecuciones)', function () {
    it('materializa horarios correctos para el día de la semana', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Matutino',
            'lider_id' => $this->lider->id,
            'apoyo_id' => $this->apoyo->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => true,
        ]);

        // Diaria
        $h1 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
            'checklist' => ['Tarea 1'],
        ]);
        $h1->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        // Semanal los lunes
        $h2 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '10:00:00',
            'frecuencia' => 'semanal',
            'dia_semana' => 'lunes',
        ]);
        $h2->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        // Semanal los miércoles
        $h3 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '10:00:00',
            'frecuencia' => 'semanal',
            'dia_semana' => 'miercoles',
        ]);
        $h3->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        // Forzar fecha a un Lunes
        $lunes = Carbon::parse('next monday')->toDateString();
        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => $lunes]);

        // Deberían materializarse: la diaria y la semanal del lunes (2 ejecuciones)
        $ejecuciones = LimpiezaEjecucion::whereDate('fecha', $lunes)->get();
        expect($ejecuciones)->toHaveCount(2);

        // Validar colaborador preasignado (debería ser null por requerimiento, sin asignar)
        expect($ejecuciones->first()->colaborador_id)->toBeNull();

        // Validar que el checklist se cargó con el valor inicial false
        expect($ejecuciones->first()->detalles_checklist)->toBeArray()
            ->and($ejecuciones->first()->detalles_checklist['Tarea 1'])->toBeFalse();

        // Ejecutar de nuevo para el mismo lunes no debe duplicar registros
        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => $lunes]);
        expect(LimpiezaEjecucion::whereDate('fecha', $lunes)->count())->toBe(2);
    });

    it('materializa con colaborador nulo (sin asignar) si no hay colaborador de apoyo', function () {
        $turnoSinApoyo = Turno::create([
            'nombre' => 'Turno Solo Lider',
            'lider_id' => $this->lider->id,
            'apoyo_id' => null,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $h = LimpiezaHorario::create([
            'turno_id' => $turnoSinApoyo->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
        ]);
        $h->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => Carbon::parse('next monday')->toDateString()]);

        $ejecucion = LimpiezaEjecucion::whereDate('fecha', Carbon::parse('next monday')->toDateString())->first();
        expect($ejecucion)->not->toBeNull()
            ->and($ejecucion->colaborador_id)->toBeNull();
    });
});

describe('Transiciones y Registro de Ejecución (limp_ejecuciones)', function () {
    it('flujo de estados de una ejecución de limpieza', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Matutino A',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $ejecucion = LimpiezaEjecucion::create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
            'turno_id' => $turno->id,
            'colaborador_id' => $this->lider->id,
            'fecha' => Carbon::parse('next monday')->toDateString(),
            'estado' => EstadoLimpieza::Pendiente,
        ]);

        // Iniciar
        $ejecucion->update([
            'estado' => EstadoLimpieza::EnProgreso,
            'hora_inicio' => '08:05:00',
        ]);

        expect($ejecucion->fresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
            ->and($ejecucion->fresh()->hora_inicio)->toBe('08:05:00');

        // Completar con checklist
        $ejecucion->update([
            'estado' => EstadoLimpieza::Completada,
            'hora_fin' => '08:45:00',
            'detalles_checklist' => [
                'limpieza_cama' => true,
                'aspirado' => true,
                'reposicion_toallas' => true,
            ],
            'observaciones' => 'Sin novedades',
        ]);

        $fresh = $ejecucion->fresh();
        expect($fresh->estado)->toBe(EstadoLimpieza::Completada)
            ->and($fresh->hora_fin)->toBe('08:45:00')
            ->and($fresh->detalles_checklist)->toBeArray()
            ->and($fresh->detalles_checklist['aspirado'])->toBeTrue()
            ->and($fresh->observaciones)->toBe('Sin novedades');
    });

    it('valida reasignacion de colaborador con carrito asignado', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Reasignaciones',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $colaborador1 = $this->lider;
        $colaborador2 = Colaborador::factory()->create();

        $carrito = Ubicacion::create([
            'nombre' => 'Carrito X',
            'tipo' => 'carrito',
            'estado' => 1,
        ]);

        $ejecucion = LimpiezaEjecucion::create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
            'turno_id' => $turno->id,
            'colaborador_id' => $colaborador1->id,
            'carrito_id' => $carrito->id,
            'fecha' => Carbon::parse('next monday')->toDateString(),
            'estado' => EstadoLimpieza::EnProgreso,
        ]);

        $validador = app(ValidarCambioColaboradorEjecucion::class);

        $this->actingAs(User::factory()->create());
        $ejecucion->colaborador_id = $colaborador2->id;
        expect(fn () => $validador->validar($ejecucion))
            ->toThrow(\Exception::class, 'No tiene permisos para cambiar el colaborador de esta limpieza.');

        $userColab1 = User::factory()->create(['persona_id' => $colaborador1->persona_id]);
        $this->actingAs($userColab1);

        expect(fn () => $validador->validar($ejecucion))
            ->toThrow(\Exception::class, 'Debe liberar el carrito (quitar el carrito seleccionado) antes de poder asignar a otro colaborador.');

        $ejecucion->carrito_id = null;
        $validador->validar($ejecucion);

        $ejecucion->save();

        expect($ejecucion->fresh()->colaborador_id)->toBe($colaborador2->id)
            ->and($ejecucion->fresh()->carrito_id)->toBeNull();
    });
});

describe('Nuevas características del Módulo de Limpieza (Equipos, Horarios Nullable, Recordatorios)', function () {
    it('permite crear un horario sin asignar turno', function () {
        $horarioSinTurno = LimpiezaHorario::create([
            'turno_id' => null,
            'hora_estimada' => '11:00:00',
            'frecuencia' => 'diaria',
        ]);
        $horarioSinTurno->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        expect($horarioSinTurno->turno_id)->toBeNull()
            ->and($horarioSinTurno->detalles->first()->limpiable)->toBeInstanceOf(Habitacion::class);
    });

    it('ignora horarios sin turno asignado durante la materialización', function () {
        // Horario con turno
        $turno = Turno::create([
            'nombre' => 'Turno Activo',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $h1 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
        ]);
        $h1->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        // Horario sin turno
        $h2 = LimpiezaHorario::create([
            'turno_id' => null,
            'hora_estimada' => '09:00:00',
            'frecuencia' => 'diaria',
        ]);
        $h2->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => Carbon::parse('next monday')->toDateString()]);

        // Sólo debe existir 1 ejecución de limpieza (la de la habitación con turno)
        $ejecuciones = LimpiezaEjecucion::whereDate('fecha', Carbon::parse('next monday')->toDateString())->get();
        expect($ejecuciones)->toHaveCount(1)
            ->and($ejecuciones->first()->limpiable_type)->toBe(Habitacion::class);
    });

    it('envía recordatorios de limpieza pendientes cuando pasa la hora estimada', function () {
        Carbon::setTestNow(Carbon::parse('2026-08-04 09:00:00'));

        $turno = Turno::create([
            'nombre' => 'Turno Alertas',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $userLider = User::factory()->create(['persona_id' => $this->lider->persona_id]);

        $horario = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
        ]);
        $horario->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        $ejecucion = LimpiezaEjecucion::create([
            'horario_id' => $horario->id,
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
            'turno_id' => $turno->id,
            'colaborador_id' => $this->lider->id,
            'fecha' => '2026-08-04',
            'estado' => EstadoLimpieza::Pendiente,
        ]);

        Artisan::call('limpieza:enviar-recordatorios');

        expect($ejecucion->fresh()->recordatorio_enviado_at)->not->toBeNull();

        expect(DB::table('notifications')
            ->where('notifiable_id', $userLider->id)
            ->where('notifiable_type', User::class)
            ->count())->toBeGreaterThan(0);

        Carbon::setTestNow(); // reset
    });

    test('permite crear y consultar turnos dedicados a lavanderia', function (): void {
        $turnoLavanderia = Turno::create([
            'nombre' => 'Turno Lavandería Nocturno',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '22:00:00',
            'hora_fin' => '06:00:00',
            'es_lavanderia' => true,
        ]);

        expect($turnoLavanderia->es_lavanderia)->toBeTrue();

        $encontrado = Turno::query()->where('es_lavanderia', true)->first();
        expect($encontrado)->not->toBeNull();
        expect($encontrado?->nombre)->toBe('Turno Lavandería Nocturno');
    });
});
